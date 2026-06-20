<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Payment;
use App\Models\FeeStructure;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;


class StudentController extends Controller
{
    /**
     * Display Enrollment Statistics
     * GET: /students/stats
     */
    public function enrollmentStats()
    {
        // 1. Class/Grade Statistics
        $classStats = Student::select('grade', DB::raw('count(*) as total'))
            ->groupBy('grade')
            ->orderBy('grade', 'asc')
            ->get();

        // 2. Gender Statistics
        $genderStats = Student::select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->get();

        // 3. Term Growth (Top 5 most recent terms with student counts)
        $termStats = DB::table('students')
            ->join('terms', 'students.term_id', '=', 'terms.id')
            ->select('terms.term_name', 'terms.academic_year', DB::raw('count(students.id) as total'))
            ->groupBy('terms.id', 'terms.term_name', 'terms.academic_year')
            ->orderBy('terms.id', 'desc')
            ->limit(5)
            ->get();

        return view('students.stats', compact('classStats', 'genderStats', 'termStats'));
    }

    /**
     * Show Student Promotion Form
     * GET: /students/promote
     */
    public function showPromotionForm()
    {
        $classes = SchoolClass::orderBy('class_name', 'asc')->get();
        $terms = Term::orderBy('start_date', 'desc')->get();

        return view('students.promote', compact('classes', 'terms'));
    }

    /**
     * Process Bulk Student Promotion
     * POST: /students/promote
     */
    public function promote(Request $request)
    {
        $request->validate([
            'current_grade' => 'required|string',
            'next_grade'    => 'required|string',
            'next_term_id'  => 'required|exists:terms,id'
        ]);

        if ($request->current_grade === $request->next_grade) {
            return back()->with('error', "Source and destination grades cannot be the same.");
        }

        try {
            DB::transaction(function () use ($request) {
                $nextClass = SchoolClass::where('class_name', $request->next_grade)->first();

                Student::where('grade', $request->current_grade)
                    ->where('status', 'active')
                    ->update([
                        'grade'      => $request->next_grade,
                        'class_id'   => $nextClass ? $nextClass->id : null,
                        'term_id'    => $request->next_term_id,
                        'updated_at' => now()
                    ]);
            });

            return redirect()->route('students.index')->with('success', "Promotion processed successfully.");
        } catch (Exception $e) {
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }

    /**
     * Display Academic Performance
     */
    public function performance(Request $request, $id = null)
    {
        if ($id) {
            $student = Student::findOrFail($id);
        } else {
            $student = auth()->user()->student ?? auth('student')->user();
        }

        if (!$student) {
            return back()->with('error', 'Student record not found.');
        }

        $allTerms = Term::orderBy('start_date', 'desc')->get();
        $requestedTermId = $request->get('term_id');

        if ($requestedTermId) {
            $displayTerm = Term::find($requestedTermId);
        } else {
            $displayTerm = Term::where('is_current', true)->first() ?? $allTerms->first();
        }

        if (!$displayTerm) {
            return back()->with('error', 'No academic terms found.');
        }

        $termResults = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($query) use ($displayTerm) {
                $query->where('term_id', $displayTerm->id);
            })
            ->with(['exam.subject'])
            ->get();

        $average = $termResults->count() > 0 ? $termResults->avg('score') : 0;

        return view('student.performance', compact('student', 'allTerms', 'displayTerm', 'termResults', 'average'));
    }

    public function index()
    {
        $students = Student::orderBy('surname', 'asc')->get();
        return view('students.manage', compact('students'));
    }

    public function create()
    {
        $classes = SchoolClass::all();
        $currentTerm = Term::where('is_current', true)->first() ?? Term::latest()->first();
        $terms = Term::orderBy('id', 'desc')->get();

        return view('students.create', compact('classes', 'currentTerm', 'terms'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name'              => 'required|string|max:255',
            'surname'           => 'required|string|max:255',
            'date_of_birth'     => 'required|date',
            'gender'            => 'required|in:Male,Female',
            'national_id'       => 'required|string|unique:students,national_id',
            'grade'             => 'required|string',
            'phone'             => 'required|string',
            'emergency_contact' => 'nullable|string',
            'address'           => 'nullable|string',
            'term_id'           => 'required|exists:terms,id',
        ]);

        try {
            return DB::transaction(function () use ($validatedData) {
                $schoolClass = SchoolClass::where('class_name', $validatedData['grade'])->first();

                $studentData = $validatedData;
                $studentData['class_id'] = $schoolClass ? $schoolClass->id : null;
                $studentData['status'] = 'active';
                $studentData['enrollment_date'] = now();

                // Map emergency_contact to parent_contact for DB consistency
                $studentData['parent_contact'] = $validatedData['emergency_contact'];

                // Calculate age from date_of_birth for the 'age' integer column
                $studentData['age'] = Carbon::parse($validatedData['date_of_birth'])->age;

                $student = Student::create($studentData);

                User::create([
                    'name'        => $validatedData['name'] . ' ' . $validatedData['surname'],
                    'email'       => $student->email,
                    'national_id' => $validatedData['national_id'],
                    'ec_number'   => $validatedData['national_id'],
                    'password'    => Hash::make($validatedData['national_id']),
                    'role'        => 'student',
                    'base_salary' => 0,
                ]);

                return redirect()->route('students.index')->with('success', "Registration successful: {$student->name} {$student->surname}");
            });
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classes = SchoolClass::all();
        $terms   = Term::orderBy('id', 'desc')->get();
        return view('students.edit', compact('student', 'classes', 'terms'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $oldNationalId = $student->national_id;

        $validatedData = $request->validate([
            'name'              => 'required|string|max:255',
            'surname'           => 'required|string|max:255',
            'date_of_birth'     => 'required|date',
            'gender'            => 'required|in:Male,Female',
            'national_id'       => 'required|string|unique:students,national_id,' . $student->id,
            'grade'             => 'required|string',
            'phone'             => 'required|string',
            'emergency_contact' => 'nullable|string',
            'status'            => 'required|in:active,inactive',
            'address'           => 'nullable|string',
            'term_id'           => 'required|exists:terms,id',
        ]);

        try {
            DB::transaction(function () use ($student, $validatedData, $oldNationalId) {
                $schoolClass = SchoolClass::where('class_name', $validatedData['grade'])->first();
                $updateData = $validatedData;
                $updateData['class_id'] = $schoolClass ? $schoolClass->id : $student->class_id;

                // Maintain DB column consistency
                $updateData['parent_contact'] = $validatedData['emergency_contact'];
                $updateData['age'] = Carbon::parse($validatedData['date_of_birth'])->age;

                $student->update($updateData);

                User::where('national_id', $oldNationalId)
                    ->where('role', 'student')
                    ->update([
                        'name'        => $validatedData['name'] . ' ' . $validatedData['surname'],
                        'national_id' => $validatedData['national_id'],
                        'ec_number'   => $validatedData['national_id']
                    ]);
            });

            return redirect()->route('students.index')->with('success', 'Student profile updated successfully!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function financials(Request $request, $id)
    {
        $student = Student::with(['payments', 'term', 'class', 'feeStructures'])
            ->findOrFail($id);

        $termId = $request->get('term_id');
        $term = $termId ? Term::find($termId) : (Term::where('is_current', true)->first() ?? Term::latest()->first());

        if (!$term) {
            return back()->with('error', 'No active academic term found.');
        }

        $expected = $this->getStudentExpectedTotal($student, $term->id);
        $paid = $student->payments->where('term_id', $term->id)->sum('amount_paid');
        $arrears = $this->calculateSumOfReds($student, $term, $expected, $paid);

        $student->expected_total = $expected;
        $student->monthly_installment = 50.00;
        $student->monthly_arrears = $arrears;
        $student->calculated_balance = $expected - $paid;

        $allTerms = Term::orderBy('academic_year', 'desc')->orderBy('id', 'desc')->get();

        return view('receptionist.students.show', compact('student', 'term', 'allTerms'));
    }

    private function getStudentExpectedTotal($student, $termId)
    {
        if (!$termId) return 0;
        $individualFees = FeeStructure::where('term_id', $termId)
            ->where('student_id', $student->id)
            ->sum('amount');

        if ($individualFees > 0) return (float) $individualFees;

        return (float) FeeStructure::where('term_id', $termId)
            ->where('grade', $student->grade)
            ->whereNull('student_id')
            ->sum('amount');
    }

    public function calculateSumOfReds($student, $term, $totalFee, $totalPaid)
    {
        if (!$term) return 0;
        $monthlyTarget = 50.00;
        $billingDates = $term->getBillingMonthDates();
        $now = Carbon::now()->startOfMonth();
        $pool = (float)$totalPaid;
        $totalOverdueArrears = 0;

        foreach ($billingDates as $mDate) {
            $billingMonth = Carbon::parse($mDate)->startOfMonth();
            $paidInThisSlot = min($monthlyTarget, $pool);
            $pool -= $paidInThisSlot;
            $shortfall = $monthlyTarget - $paidInThisSlot;

            if ($now->gte($billingMonth) && $shortfall > 0) {
                $totalOverdueArrears += $shortfall;
            }
        }
        return $totalOverdueArrears;
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        try {
            DB::transaction(function() use ($student) {
                Payment::where('student_id', $student->id)->delete();
                FeeStructure::where('student_id', $student->id)->delete();
                User::where('national_id', $student->national_id)->where('role', 'student')->delete();
                $student->delete();
            });

            return redirect()->route('students.index')->with('success', 'Records deleted permanently.');
        } catch (Exception $e) {
            return redirect()->route('students.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function processMassPromotion(Request $request)
    {
        $request->validate([
            'target_term_id' => 'required|exists:terms,id',
            'promote' => 'required|array',
        ]);

        $promotions = $request->input('promote');
        $targetTermId = $request->input('target_term_id');
        $count = 0;

        DB::transaction(function () use ($promotions, $targetTermId, &$count) {
            foreach ($promotions as $item) {
                if (isset($item['active']) && !empty($item['to_grade'])) {
                    $updated = Student::where('grade', $item['from_grade'])
                        ->where('status', 'active')
                        ->update([
                            'grade' => $item['to_grade'],
                            'term_id' => $targetTermId,
                            'status' => ($item['to_grade'] === 'Graduated') ? 'alumni' : 'active'
                        ]);

                    $count += $updated;
                }
            }
        });

        return redirect()->route('students.promote')
            ->with('success', "Success! $count students have been transitioned to the new term.");
    }

    /**
     * Show student profile data for Modal Popup (AJAX)
     */
   /**
     * Show student profile data for Modal Popup (AJAX)
     * Includes Gender-specific avatars and improved UI
     */
    public function showProfile($id)
    {
        try {
            $student = Student::findOrFail($id);
            $fullName = $student->name . " " . $student->surname;

            // Logic for Gender-specific Avatar
            // If you have local AdminLTE assets, use: asset('dist/img/avatar5.png')
            // Using dynamic UI-Avatars with gender colors: Blue for Male, Pink for Female
            $avatarColor = ($student->gender == 'Male') ? '3c8dbc' : 'e83e8c';
            $avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . "&background={$avatarColor}&color=fff&size=128";

            // Format Date of Birth
            $dob = $student->date_of_birth
                ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y')
                : 'Not Set';

            return "
            <div class='box-body box-profile'>
                <div class='text-center' style='margin-bottom: 15px;'>
                    <img class='profile-user-img img-responsive img-circle'
                         src='{$avatarUrl}'
                         alt='User profile picture'
                         style='width: 100px; height: 100px; border: 3px solid #{$avatarColor}; padding: 3px;'>

                    <h3 class='profile-username' style='margin-top: 10px; font-weight: bold; color: #333;'>
                        {$student->surname}, {$student->name}
                    </h3>
                    <p class='text-muted'>
                        <span class='label label-default' style='font-family: monospace;'>{$student->student_number}</span>
                    </p>
                </div>

                <ul class='list-group list-group-unbordered'>
                    <li class='list-group-item'>
                        <i class='fa fa-id-card text-blue'></i> <b>National ID</b>
                        <span class='pull-right text-bold'>".($student->national_id ?? 'N/A')."</span>
                    </li>
                    <li class='list-group-item'>
                        <i class='fa fa-graduation-cap text-blue'></i> <b>Level</b>
                        <span class='pull-right label label-primary'>{$student->grade}</span>
                    </li>
                    <li class='list-group-item'>
                        <i class='fa fa-venus-mars text-blue'></i> <b>Gender</b>
                        <span class='pull-right'>{$student->gender}</span>
                    </li>
                    <li class='list-group-item'>
                        <i class='fa fa-calendar text-blue'></i> <b>Date of Birth</b>
                        <span class='pull-right'>{$dob}</span>
                    </li>
                    <li class='list-group-item'>
                        <i class='fa fa-phone text-blue'></i> <b>Student Phone</b>
                        <span class='pull-right'>{$student->phone}</span>
                    </li>
                    <li class='list-group-item'>
                        <i class='fa fa-users text-blue'></i> <b>Guardian Contact</b>
                        <span class='pull-right text-bold'>".($student->parent_contact ?? $student->emergency_contact ?? 'N/A')."</span>
                    </li>
                </ul>

                <div style='margin-top: 15px; padding: 10px; background: #f4f4f4; border-radius: 5px; border-left: 4px solid #{$avatarColor};'>
                    <strong style='color: #555;'><i class='fa fa-map-marker margin-r-5'></i> Residential Address</strong>
                    <p class='text-muted' style='margin: 5px 0 0 20px;'>
                        ".($student->address ?? 'No residential address provided.')."
                    </p>
                </div>

                <div class='text-center' style='margin-top: 15px;'>
                    <span class='label " . ($student->status == 'active' ? 'label-success' : 'label-danger') . "' style='padding: 5px 15px; text-transform: uppercase;'>
                        <i class='fa " . ($student->status == 'active' ? 'fa-check-circle' : 'fa-times-circle') . "'></i>
                        {$student->status}
                    </span>
                </div>
            </div>";
        } catch (Exception $e) {
            return "<div class='alert alert-danger'>
                        <i class='icon fa fa-ban'></i> <b>Error:</b> Could not retrieve student profile data.
                    </div>";
        }
    }
    /**
     * View the biometric enrollment interface for a student.
     */
    public function enrollFaceView($id)
    {
        $student = Student::findOrFail($id);
        return view('students.enroll_face', compact('student'));
    }

    /**
     * Process and store the captured biometric face data.
     */
    public function storeFace(Request $request, $id)
    {
        // 1. Validate the input
        $request->validate(['face_image' => 'required|string']);

        $student = Student::findOrFail($id);
        $imageData = $request->face_image;

        // 2. Clean the Base64 string
        $imageData = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,'], '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);

        // 3. Define path: 'biometrics/face_ID_TIMESTAMP.jpg'
        $fileName = 'face_' . $student->id . '_' . time() . '.jpg';
        $path = 'biometrics/' . $fileName;

        // 4. Save file to storage/app/public/biometrics/
        // Using the 'public' disk means it will be stored in storage/app/public
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($imageData));

        // 5. CRITICAL STEP: Update the database
        // We update the student model with the new path
        $student->update(['face_path' => $path]);

        return response()->json(['success' => true, 'path' => $path]);
    }
    public function getFace($id)
    {
        $student = Student::findOrFail($id);

        // Check if face_path exists and file is on disk
        if ($student->face_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->face_path)) {
            return response()->file(storage_path('app/public/' . $student->face_path));
        }

        // If null or file missing, return a default image
        return response()->file(public_path('img/default-avatar.png'));
    }
}
