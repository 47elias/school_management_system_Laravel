<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('admissions', function (Blueprint $table) {
        // Adding the missing personal details
        $table->string('student_name')->after('identity_number');
        $table->date('date_of_birth')->after('student_name');
        $table->string('applied_grade')->after('date_of_birth');
        
        // Adding guardian details
        $table->string('guardian_name')->after('applied_grade');
        $table->string('guardian_phone')->after('guardian_name');
        
        // Adding status (if it doesn't exist yet)
        $table->string('status')->default('pending')->after('guardian_phone');
        
        // Adding admin remarks for tracking
        $table->text('admin_remarks')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('admissions', function (Blueprint $table) {
        $table->dropColumn([
            'student_name', 'date_of_birth', 'applied_grade', 
            'guardian_name', 'guardian_phone', 'status', 'admin_remarks'
        ]);
    });
    }
};
