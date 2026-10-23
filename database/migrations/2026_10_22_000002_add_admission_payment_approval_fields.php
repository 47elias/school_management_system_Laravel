<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [];
        if (!Schema::hasColumn('admissions', 'activation_payment_proof')) $columns[] = 'activation_payment_proof';
        if (!Schema::hasColumn('admissions', 'activation_payment_status')) $columns[] = 'activation_payment_status';
        if (!Schema::hasColumn('admissions', 'activation_payment_remarks')) $columns[] = 'activation_payment_remarks';
        if (!Schema::hasColumn('admissions', 'portal_password')) $columns[] = 'portal_password';

        if ($columns) {
            Schema::table('admissions', function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    if ($column === 'activation_payment_proof') $table->string($column)->nullable();
                    elseif ($column === 'activation_payment_status') $table->string($column)->nullable()->index();
                    elseif ($column === 'activation_payment_remarks') $table->text($column)->nullable();
                    elseif ($column === 'portal_password') $table->string($column)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            'activation_payment_proof',
            'activation_payment_status',
            'activation_payment_remarks',
            'portal_password',
        ], fn ($column) => Schema::hasColumn('admissions', $column)));

        if ($columns) {
            Schema::table('admissions', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
