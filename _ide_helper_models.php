<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $exam_name
 * @property int $term_id
 * @property int $subject_id
 * @property string $exam_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mark> $marks
 * @property-read int|null $marks_count
 * @property-read \App\Models\Subject|null $subject
 * @property-read \App\Models\Term $term
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereExamDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereExamName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Exam whereUpdatedAt($value)
 */
	class Exam extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fee_name
 * @property numeric $amount
 * @property string $grade
 * @property int $term_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Term $term
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure whereFeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeStructure whereUpdatedAt($value)
 */
	class FeeStructure extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $exam_id
 * @property int $student_id
 * @property string $subject
 * @property int $score
 * @property int $max_score
 * @property string|null $teacher_comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Exam $exam
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereExamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereMaxScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereTeacherComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mark whereUpdatedAt($value)
 */
	class Mark extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $term_id
 * @property numeric $amount_paid
 * @property string $payment_date
 * @property string $payment_method
 * @property string|null $reference_no
 * @property string|null $remarks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Term $term
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmountPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $class_name
 * @property string $class_code
 * @property string|null $room_number
 * @property int $capacity
 * @property int|null $teacher_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subject> $subjects
 * @property-read int|null $subjects_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereClassCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereClassName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereRoomNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolClass whereUpdatedAt($value)
 */
	class SchoolClass extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $term_id
 * @property string $student_number
 * @property string $name
 * @property string $surname
 * @property int $age
 * @property string|null $gender
 * @property string|null $national_id
 * @property string|null $grade
 * @property string $enrollment_status
 * @property string|null $address
 * @property string|null $parent_contact
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $enrollment_date
 * @property int|null $enrollment_term_id
 * @property string $status
 * @property string|null $photo_path
 * @property string|null $emergency_contact
 * @property string|null $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $remember_token
 * @property numeric $balance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mark> $marks
 * @property-read int|null $marks_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Term|null $term
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEmergencyContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEnrollmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEnrollmentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEnrollmentTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNationalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereParentContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student wherePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStudentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUpdatedAt($value)
 */
	class Student extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $subject_name
 * @property string $subject_code
 * @property string $type
 * @property int $pass_mark
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SchoolClass> $classes
 * @property-read int|null $classes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Subject> $subjects
 * @property-read int|null $subjects_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject wherePassMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereSubjectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereSubjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereUpdatedAt($value)
 */
	class Subject extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $term_name
 * @property string $academic_year
 * @property string $start_date
 * @property string $end_date
 * @property int $is_current
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereAcademicYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereIsCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereTermName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Term whereUpdatedAt($value)
 */
	class Term extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $employee_id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string|null $phone_number
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $ec_number
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEcNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

