<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class StudentEnrollmentService
{
    /** Enroll an approved application in an available stream without changing its grade. */
    public function enrollApprovedAdmission(Admission $application, ?string $gender = null): Student
    {
        return DB::transaction(function () use ($application, $gender) {
            $admission = Admission::lockForUpdate()->findOrFail($application->id);

            if ($admission->status !== 'approved') {
                throw new RuntimeException('This application must be approved before the student can be enrolled.');
            }

            $existing = Student::where('national_id', $admission->identity_number)->first();
            if ($existing) {
                if ($existing->status !== 'active') {
                    throw new RuntimeException('The student record is inactive. Contact the school to complete enrollment.');
                }

                if ($existing->class_id && $existing->schoolClass) {
                    $existing->update([
                        'grade' => $existing->schoolClass->class_name,
                        'gender' => $existing->gender ?: ($gender ?? $admission->gender),
                    ]);
                    return $existing->fresh();
                }
            }

            $gradeName = strtolower(trim($admission->applied_grade));
            $gradeLevel = GradeLevel::whereRaw('LOWER(name) = ?', [$gradeName])->first();
            $classesQuery = SchoolClass::query();

            if ($gradeLevel) {
                $classesQuery->where(function ($query) use ($gradeLevel, $gradeName) {
                    $query->where('grade_level_id', $gradeLevel->id)
                        ->orWhereRaw('LOWER(class_name) LIKE ?', [$gradeName . '%']);
                });
            } else {
                $classesQuery->whereRaw('LOWER(class_name) LIKE ?', [$gradeName . '%']);
            }

            $gradeClasses = $classesQuery
                ->withCount(['students as active_students_count' => fn ($query) => $query->where('status', 'active')])
                ->lockForUpdate()
                ->get()
                ->filter(function ($class) use ($gradeName, $gradeLevel) {
                    if ($gradeLevel && (int) $class->grade_level_id === (int) $gradeLevel->id) {
                        return true;
                    }

                    return preg_match('/^' . preg_quote($gradeName, '/') . '(?:$|[\sa-z])/', strtolower($class->class_name)) === 1;
                })
                ->sortBy('active_students_count');

            if ($gradeClasses->isEmpty()) {
                throw new RuntimeException("No class streams are configured for {$admission->applied_grade}. Contact the admissions office for placement.");
            }

            $availableClass = $gradeClasses->first(
                fn ($class) => $class->capacity !== null && $class->active_students_count < $class->capacity
            );

            if (!$availableClass) {
                throw new RuntimeException("All class streams for {$admission->applied_grade} are currently full. Please contact the admissions office for placement.");
            }

            $term = Term::where('is_current', true)->first() ?? Term::latest()->first();
            if (!$term) {
                throw new RuntimeException('Configure an academic term before enrolling students.');
            }

            $parts = preg_split('/\s+/', trim($admission->student_name), 2);
            $name = $parts[0] ?? $admission->student_name;
            $surname = $parts[1] ?? $parts[0] ?? $admission->student_name;

            $studentData = [
                'name' => $name,
                'surname' => $surname,
                'date_of_birth' => $admission->date_of_birth,
                'gender' => $gender ?? $admission->gender,
                'national_id' => $admission->identity_number,
                'grade' => $availableClass->class_name,
                'class_id' => $availableClass->id,
                'address' => $admission->address,
                'parent_contact' => $admission->guardian_phone,
                'emergency_contact' => $admission->guardian_phone,
                'phone' => $admission->guardian_phone,
                'term_id' => $term->id,
                'enrollment_date' => now(),
                'status' => 'active',
                'password' => Hash::make(Str::random(48)),
            ];

            if ($existing) {
                unset($studentData['password']);
                $existing->update($studentData);
                $student = $existing;
            } else {
                $student = Student::create($studentData);
                // The student chooses their own portal password during account activation.
                $student->forceFill(['password' => null])->save();
            }

            if (!User::where('national_id', $admission->identity_number)->exists()) {
                $email = $student->email;
                if (User::where('email', $email)->exists()) {
                    $email = Str::slug($name . '.' . $surname, '.') . '-' . $admission->id . '-' . $student->id . '@student.local';
                }

                User::create([
                    'name' => trim($name . ' ' . $surname),
                    'email' => $email,
                    'national_id' => $admission->identity_number,
                    'ec_number' => $admission->identity_number,
                    'password' => Hash::make(Str::random(48)),
                    'role' => 'student',
                    'base_salary' => 0,
                ]);
            }

            return $student;
        });
    }
}
