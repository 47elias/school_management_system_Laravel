<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Term;
use App\Services\StudentFinanceService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FutureTermEnrollmentTest extends TestCase
{
    public function createApplication()
    {
        $app = require Application::inferBasePath().'/bootstrap/app.php';
        $this->traitsUsedByTest = array_flip(class_uses_recursive(static::class));
        // The application's provider loads subjects during bootstrap, before migrations.
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
        $this->withoutMiddleware();
        $this->travelTo(now()->setDate(2026, 10, 23)->startOfDay());

        (require database_path('migrations/2026_01_04_145013_create_terms_table.php'))->up();
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            foreach (['student_number', 'name', 'surname', 'gender', 'national_id', 'grade',
                'phone', 'emergency_contact', 'parent_contact', 'address', 'email', 'password', 'status'] as $column) {
                $table->string($column)->nullable();
            }
            $table->date('date_of_birth');
            $table->date('enrollment_date');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'email', 'national_id', 'ec_number', 'password', 'role'] as $column) {
                $table->string($column);
            }
            $table->decimal('base_salary')->default(0);
            $table->timestamps();
        });
    }

    private function term(array $attributes = []): Term
    {
        return Term::create(array_merge([
            'term_name' => 'Term 1', 'academic_year' => 2027,
            'start_date' => '2027-01-12', 'end_date' => '2027-04-10',
            'is_current' => false, 'status' => 'open',
        ], $attributes));
    }

    private function registration(?int $termId): array
    {
        return [
            'name' => 'Jane', 'surname' => 'Doe', 'date_of_birth' => '2014-05-10',
            'gender' => 'Female', 'national_id' => 'TEST-001', 'phone' => '0771234567',
            'term_id' => $termId,
        ];
    }

    public function test_future_enrollment_preserves_current_term_and_does_not_bill_earlier_terms(): void
    {
        $current = $this->term([
            'term_name' => 'Term 3', 'academic_year' => 2026,
            'start_date' => '2026-09-01', 'end_date' => '2026-12-10', 'is_current' => true,
        ]);
        $future = $this->term();

        $this->post(route('students.store'), $this->registration($future->id))
            ->assertRedirect(route('students.index'))->assertSessionHasNoErrors()->assertSessionHas('success');

        $student = Student::firstOrFail();
        $this->assertSame($future->id, $student->term_id);
        $this->assertSame('2027-01-12', $student->enrollment_date->toDateString());
        $this->assertTrue($current->fresh()->is_current);
        $this->assertFalse($future->fresh()->is_current);
        $finance = app(StudentFinanceService::class);
        $this->assertFalse($finance->canBillForTerm($student, $current));
        $this->travelTo(now()->setDate(2027, 1, 12));
        $this->assertTrue($finance->canBillForTerm($student, $future));
    }

    public function test_future_enrollment_does_not_require_an_active_term(): void
    {
        $future = $this->term();
        $this->post(route('students.store'), $this->registration($future->id))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('students', ['term_id' => $future->id, 'enrollment_date' => '2027-01-12 00:00:00']);
        $this->assertFalse($future->fresh()->is_current);
    }

    public function test_current_term_enrollment_still_starts_today(): void
    {
        $current = $this->term([
            'start_date' => '2026-09-01', 'end_date' => '2026-12-10', 'is_current' => true,
        ]);
        $this->post(route('students.store'), $this->registration($current->id))
            ->assertSessionHas('success');
        $this->assertSame('2026-10-23', Student::firstOrFail()->enrollment_date->toDateString());
    }

    public function test_closed_past_missing_and_unknown_terms_are_rejected(): void
    {
        $closed = $this->term(['status' => 'closed']);
        $past = $this->term(['start_date' => '2026-01-01', 'end_date' => '2026-04-01']);
        foreach ([$closed->id, $past->id, null, 9999] as $termId) {
            $this->post(route('students.store'), $this->registration($termId))
                ->assertSessionHasErrors('term_id');
        }
        $this->assertDatabaseCount('students', 0);
    }
}
