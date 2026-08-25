<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CONTINUOUS ASSESSMENT (CA) MODULE
 * -----------------------------------------------------------------------
 * This table is intentionally independent from `exams`. Exams are
 * scheduled events with a fixed date tied to a subject via `subject_id`
 * (and resolve their class through a fragile hasOneThrough bridge).
 *
 * `class_activities` instead links straight to `subject_assignment_id`
 * (teacher + subject + class + academic_year in one FK) which is:
 *   - Faster: one indexed join instead of a bridge query per exam.
 *   - Safer: no ambiguity about which class an activity belongs to.
 *   - Scalable: a teacher can log as many of these as they like, any day,
 *     with no scheduling step required first (unlike exams).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_assignment_id')
                ->constrained('subject_assignments')
                ->cascadeOnDelete();

            $table->foreignId('term_id')
                ->constrained('terms')
                ->cascadeOnDelete();

            $table->string('title');
            $table->enum('type', [
                'classwork', 'homework', 'quiz', 'participation', 'practical', 'project', 'other'
            ])->default('classwork');

            // No fixed schedule requirement - defaults to "today" but can be backdated.
            $table->date('activity_date');

            $table->unsignedSmallInteger('max_score')->default(100);
            // Optional weighting so different activity types can count more/less
            // toward a term CA average later, without changing this schema again.
            $table->decimal('weight', 5, 2)->default(1.00);

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Fast lookups: "all of this teacher's class activities this term, by date"
            $table->index(['subject_assignment_id', 'term_id', 'activity_date'], 'class_activities_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_activities');
    }
};
