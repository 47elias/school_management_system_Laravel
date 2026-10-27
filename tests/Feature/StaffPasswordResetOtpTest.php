<?php

namespace Tests\Feature;

use App\Mail\StaffPasswordResetOtp;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffPasswordResetOtpTest extends TestCase
{
    public function createApplication()
    {
        $app = require Application::inferBasePath().'/bootstrap/app.php';
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));
        // The application loads subjects during provider boot, before migrations.
        $app->booting(function () {
            config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
            Schema::create('subjects', fn (Blueprint $table) => $table->id());
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/0001_01_01_000000_create_users_table.php'))->up();
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin');
            $table->string('ec_number')->nullable();
            $table->string('national_id')->nullable();
        });
        $this->travelTo(now()->startOfSecond());
        Mail::fake();
    }

    private function staff(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'School Admin', 'email' => 'admin@school.test',
            'password' => 'OldPassword123!', 'role' => 'admin',
            'ec_number' => 'STAFF001', 'national_id' => 'ID001',
        ], $attributes));
    }

    private function sendCode(User $user): string
    {
        $this->post(route('password.otp.send'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))->assertSessionHasNoErrors();

        return Mail::sent(StaffPasswordResetOtp::class)->last()->code;
    }

    private function resetData(string $code): array
    {
        return ['otp' => $code, 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];
    }

    public function test_recovery_form_asks_for_staff_email(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Staff email address')
            ->assertDontSee('name="ec_number"', false)->assertDontSee('name="password"', false);
    }

    public function test_code_is_emailed_and_only_its_hash_is_stored(): void
    {
        $user = $this->staff();
        $code = $this->sendCode($user);
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
        $token = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');
        $this->assertNotSame($code, $token);
        $this->assertTrue(Hash::check($code, $token));
        Mail::assertSent(StaffPasswordResetOtp::class, fn ($mail) => $mail->hasTo($user->email));
        (new StaffPasswordResetOtp($code))->assertSeeInHtml($code)->assertSeeInHtml('10 minutes');
        $this->get(route('password.request'))->assertOk()->assertSee('Email verification code');
    }

    public function test_valid_code_resets_password_once_and_rotates_remember_token(): void
    {
        Event::fake([PasswordReset::class]);
        $user = $this->staff();
        $code = $this->sendCode($user);
        $this->post(route('password.update.final'), $this->resetData($code))
            ->assertRedirect(route('login'))->assertSessionHas('success')
            ->assertSessionMissing('staff_password_reset_email');
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertNotEmpty($user->fresh()->remember_token);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        Event::assertDispatched(PasswordReset::class);

        $this->withSession(['staff_password_reset_email' => $user->email])
            ->post(route('password.update.final'), $this->resetData($code))->assertSessionHasErrors('otp');
    }

    public function test_wrong_code_does_not_change_password_or_flash_secrets(): void
    {
        $user = $this->staff();
        $code = $this->sendCode($user);
        $wrongCode = $code === '111111' ? '222222' : '111111';
        $this->post(route('password.update.final'), $this->resetData($wrongCode))
            ->assertSessionHasErrors('otp')->assertSessionMissing('_old_input.otp')
            ->assertSessionMissing('_old_input.password');
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_code_expires_after_ten_minutes(): void
    {
        $user = $this->staff();
        $code = $this->sendCode($user);
        $this->travel(10)->minutes();
        $this->post(route('password.update.final'), $this->resetData($code))->assertSessionHasErrors('otp');
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_five_wrong_attempts_block_even_a_valid_code(): void
    {
        $user = $this->staff();
        $code = $this->sendCode($user);
        $wrongCode = $code === '111111' ? '222222' : '111111';
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('password.update.final'), $this->resetData($wrongCode))->assertSessionHasErrors('otp');
        }
        $this->post(route('password.update.final'), $this->resetData($code))
            ->assertSessionHasErrors(['otp' => 'Too many attempts. Please try again in 10 minutes.']);
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_resend_is_limited_and_replaces_the_stored_code(): void
    {
        $user = $this->staff();
        $this->sendCode($user);
        $oldHash = DB::table('password_reset_tokens')->value('token');
        $this->post(route('password.otp.send'), ['email' => $user->email])->assertSessionHasErrors('email');
        Mail::assertSentCount(1);
        $this->travel(61)->seconds();
        $this->post(route('password.otp.send'), ['email' => $user->email])->assertRedirect(route('password.request'));
        Mail::assertSentCount(2);
        $this->assertDatabaseCount('password_reset_tokens', 1);
        $this->assertNotSame($oldHash, DB::table('password_reset_tokens')->value('token'));
        $newCode = Mail::sent(StaffPasswordResetOtp::class)->last()->code;
        $this->assertTrue(Hash::check($newCode, DB::table('password_reset_tokens')->value('token')));
    }

    public function test_students_and_unknown_emails_do_not_receive_staff_reset_codes(): void
    {
        $student = $this->staff(['email' => 'student@school.test', 'role' => 'student']);
        foreach ([$student->email, 'unknown@school.test'] as $email) {
            $this->post(route('password.otp.send'), ['email' => $email])
                ->assertRedirect(route('password.request'))->assertSessionHas('status');
        }
        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_old_identity_details_cannot_bypass_the_code(): void
    {
        $user = $this->staff();
        $this->post(route('password.update.final'), [
            'ec_number' => $user->ec_number, 'id_number' => $user->national_id,
            'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasErrors('otp');
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_reset_requires_the_session_that_requested_the_code(): void
    {
        $user = $this->staff();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email, 'token' => Hash::make('123456'), 'created_at' => now(),
        ]);
        $this->post(route('password.update.final'), $this->resetData('123456'))
            ->assertRedirect(route('password.request'))->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_password_confirmation_is_required_without_consuming_code(): void
    {
        $user = $this->staff();
        $code = $this->sendCode($user);
        $this->post(route('password.update.final'), array_merge($this->resetData($code), [
            'password_confirmation' => 'DifferentPassword123!',
        ]))->assertSessionHasErrors('password');
        $this->assertDatabaseCount('password_reset_tokens', 1);
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_delivery_failure_removes_the_undelivered_code(): void
    {
        $user = $this->staff();
        Mail::shouldReceive('to')->once()->with($user->email)->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Test mail failure'));
        $this->post(route('password.otp.send'), ['email' => $user->email])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_successful_reset_revokes_only_that_users_database_sessions(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->staff();
        $other = $this->staff(['email' => 'other@school.test']);
        foreach (['staff-session' => $user->id, 'other-session' => $other->id] as $id => $userId) {
            DB::table('sessions')->insert([
                'id' => $id, 'user_id' => $userId,
                'payload' => base64_encode(serialize([])), 'last_activity' => now()->timestamp,
            ]);
        }
        $code = $this->sendCode($user);
        $this->post(route('password.update.final'), $this->resetData($code))->assertRedirect(route('login'));
        $this->assertDatabaseMissing('sessions', ['id' => 'staff-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'other-session']);
    }

    public function test_email_case_does_not_prevent_code_verification(): void
    {
        $user = $this->staff();
        $this->post(route('password.otp.send'), ['email' => strtoupper($user->email)])
            ->assertSessionHas('staff_password_reset_email', $user->email);
        $code = Mail::sent(StaffPasswordResetOtp::class)->last()->code;
        $this->post(route('password.update.final'), $this->resetData($code))->assertRedirect(route('login'));
    }
}
