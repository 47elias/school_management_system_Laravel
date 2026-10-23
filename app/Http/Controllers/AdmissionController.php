<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admission;
use App\Mail\AdmissionStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Models\Student;
use App\Models\User;
use App\Models\GradeLevel;
use App\Services\StudentEnrollmentService;
use Illuminate\Support\Facades\Storage;

class AdmissionController extends Controller
{
    /**
     * ADMIN: Display management registry with statistics.
     */
    public function manage()
    {
        // Fetch all admissions with pagination
        $admissions = Admission::orderBy('created_at', 'desc')->paginate(15);

        // Stats for Admin Dashboard
        $stats = [
            'total'    => Admission::count(),
            'pending'  => Admission::where('status', 'pending')->count(),
            'approved' => Admission::where('status', 'approved')->count(),
        ];

        $enrolledStudents = Student::with('schoolClass')->whereIn('national_id', $admissions->getCollection()->pluck('identity_number'))
            ->get()->keyBy('national_id');

        return view('admission.index', compact('admissions', 'stats', 'enrolledStudents'));
    }

    public function paymentProofs()
    {
        $applications = Admission::whereNotNull('activation_payment_proof')
            ->orderByRaw("CASE WHEN activation_payment_status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->paginate(20);

        $studentsByNationalId = Student::with('schoolClass')
            ->whereIn('national_id', $applications->getCollection()->pluck('identity_number'))
            ->get()->keyBy('national_id');
        $applicationIds = Admission::whereNotNull('activation_payment_proof')->pluck('identity_number');
        $legacyStudents = Student::with('schoolClass')
            ->whereNotNull('activation_payment_proof')
            ->whereNotIn('national_id', $applicationIds)
            ->orderByRaw("CASE WHEN activation_payment_status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->get();

        return view('admission.payment_proofs', compact('applications', 'studentsByNationalId', 'legacyStudents'));
    }

    public function reviewPaymentProof(Request $request, $id)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $admission = Admission::whereNotNull('activation_payment_proof')->findOrFail($id);

        if ($validated['decision'] === 'approved') {
            if (!$admission->portal_password) {
                return back()->with('error', 'This application has no pending portal activation. Ask the applicant to submit activation details again.');
            }

            try {
                $student = app(StudentEnrollmentService::class)->enrollApprovedAdmission($admission, $admission->gender);
            } catch (\RuntimeException $exception) {
                return back()->with('error', $exception->getMessage());
            }

            $student->forceFill([
                'password' => $admission->portal_password,
                'activation_payment_proof' => $admission->activation_payment_proof,
                'activation_payment_status' => 'approved',
                'activation_payment_remarks' => $validated['remarks'] ?? null,
            ])->save();
            User::where('national_id', $student->national_id)
                ->where('role', 'student')
                ->update(['password' => $admission->portal_password, 'updated_at' => now()]);

            $admission->forceFill([
                'activation_payment_status' => 'approved',
                'activation_payment_remarks' => $validated['remarks'] ?? null,
                'portal_password' => null,
            ])->save();

            $message = "Payment proof approved for {$admission->student_name}. Student record created in {$student->schoolClass?->class_name}; portal sign-in is now enabled.";
        } else {
            $admission->update([
                'activation_payment_status' => 'rejected',
                'activation_payment_remarks' => $validated['remarks'] ?? null,
            ]);
            $message = "Payment proof rejected for {$admission->student_name}. The applicant can resubmit proof from the activation page.";
        }

        return back()->with('success', $message);
    }

    public function showPaymentProof($id)
    {
        $admission = Admission::whereNotNull('activation_payment_proof')->findOrFail($id);
        abort_unless(Storage::disk('local')->exists($admission->activation_payment_proof), 404);

        return Storage::disk('local')->response($admission->activation_payment_proof);
    }

    public function reviewLegacyStudentPaymentProof(Request $request, $id)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $student = Student::whereNotNull('activation_payment_proof')->findOrFail($id);
        $student->forceFill([
            'activation_payment_status' => $validated['decision'],
            'activation_payment_remarks' => $validated['remarks'] ?? null,
        ])->save();

        return back()->with('success', "Payment proof {$validated['decision']} for {$student->name} {$student->surname}.");
    }

    public function showLegacyStudentPaymentProof($id)
    {
        $student = Student::whereNotNull('activation_payment_proof')->findOrFail($id);
        abort_unless(Storage::disk('local')->exists($student->activation_payment_proof), 404);

        return Storage::disk('local')->response($student->activation_payment_proof);
    }

    /**
     * PUBLIC: Display the admission application form.
     */
    public function index()
    {
        $gradeLevels = GradeLevel::orderBy('level_order')->get();

        return view('admission.apply', compact('gradeLevels'));
    }

    /**
     * PUBLIC: Store a new application.
     * Matches all columns in sit (10).sql: address, guardian_email, previous_school, etc.
     */
    public function store(Request $request)
    {
        // 1. Validation (matches your form inputs and SQL schema)
        $request->validate([
            'identity_number'       => 'required|string|max:255|unique:admissions,identity_number',
            'name'                  => 'required|string|max:255',
            'dob'                   => 'required|date|before:today',
            'gender'                => 'required|in:Male,Female',
            'grade'                 => 'required|string|max:255',
            'address'               => 'nullable|string',
            'guardian_name'         => 'required|string|max:255',
            'phone'                 => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'previous_school'       => 'nullable|string|max:255',
            'subjects_passed'       => 'required|string',
            'academic_history'      => 'nullable|string',
            'results_file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'recommendation_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4999',
        ], [
            'identity_number.unique' => 'This Identity Number is already registered.',
        ]);

        $resultsPath = null;
        $recommendationPath = null;

        try {
            // 2. Handle File Uploads Directly to public/admission-documents
            if ($request->hasFile('results_file')) {
                $file = $request->file('results_file');
                $filename = time() . '_results_' . str_replace(' ', '_', $file->getClientOriginalName());
                $file->move(public_path('admission-documents'), $filename);
                $resultsPath = 'admission-documents/' . $filename;
            }

            if ($request->hasFile('recommendation_letter')) {
                $file = $request->file('recommendation_letter');
                $filename = time() . '_rec_' . str_replace(' ', '_', $file->getClientOriginalName());
                $file->move(public_path('admission-documents'), $filename);
                $recommendationPath = 'admission-documents/' . $filename;
            }

            // 3. Database Transaction
            $admission = DB::transaction(function () use ($request, $resultsPath, $recommendationPath) {
                return Admission::create([
                    'identity_number'       => $request->identity_number,
                    'student_name'          => $request->name,
                    'date_of_birth'         => $request->dob,
                    'gender'                => $request->gender,
                    'applied_grade'         => $request->grade,
                    'address'               => $request->address,
                    'guardian_name'         => $request->guardian_name,
                    'guardian_phone'        => $request->phone,
                    'guardian_email'        => $request->email,
                    'previous_school'       => $request->previous_school,
                    'subjects_passed'       => $request->subjects_passed,
                    'academic_history'      => $request->academic_history,
                    'results_file'          => $resultsPath,
                    'recommendation_letter' => $recommendationPath,
                    // tracking_id and status (default 'pending') handled by Model boot or DB default
                ]);
            });

            return redirect()
                ->route('students.apply.index')
                ->with('success', "Application submitted! Tracking ID: {$admission->tracking_id}");

        } catch (\Exception $e) {
            // Cleanup files from the public folder if the database entry fails
            if ($resultsPath && File::exists(public_path($resultsPath))) {
                File::delete(public_path($resultsPath));
            }
            if ($recommendationPath && File::exists(public_path($recommendationPath))) {
                File::delete(public_path($recommendationPath));
            }

            Log::error("Admission Save Error: " . $e->getMessage());
            return back()->withInput()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    /**
     * ADMIN: Update admission status and add remarks.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|in:pending,approved,declined,reviewing,interview',
            'admin_remarks' => 'nullable|string'
        ]);

        try {
            $admission = Admission::findOrFail($id);
            $admission->update([
                'status'        => $request->status,
                'admin_remarks' => $request->admin_remarks
            ]);

            // Email Notification to Guardian
            if ($admission->guardian_email) {
                try {
                    Mail::to($admission->guardian_email)->send(new AdmissionStatusUpdated($admission));
                } catch (\Exception $e) {
                    Log::warning("Mail delivery failed for Admission ID {$id}: " . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', "Status updated for {$admission->student_name}");

        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /** Enroll an approved applicant directly from the admissions registry. */
    public function enroll(Request $request, $id)
    {
        $validated = $request->validate([
            'gender' => 'required|in:Male,Female',
        ]);

        try {
            $admission = Admission::findOrFail($id);
            if ($admission->activation_payment_status !== 'approved' || !$admission->activation_payment_proof) {
                throw new \RuntimeException('The student can only be added to the register after the proof of payment is approved.');
            }

            $student = app(StudentEnrollmentService::class)->enrollApprovedAdmission($admission, $validated['gender']);

            $className = $student->schoolClass?->class_name ?? 'an available stream';
            return back()->with('success', "{$student->name} {$student->surname} is enrolled in {$student->grade} and placed in {$className} (ID: {$student->student_number}). The student can activate their portal account from the student login page.");
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admission enrollment failed', ['admission_id' => $id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Enrollment failed. Check that the student is not already registered and try again.');
        }
    }

    /**
     * PUBLIC: Track application by Identity Number or Tracking ID.
     */
    public function track(Request $request)
    {
        $request->validate(['identity_number' => 'required|string']);

        $search = $request->identity_number;
        $application = Admission::where('identity_number', $search)
                                ->orWhere('tracking_id', $search)
                                ->first();

        if (!$application) {
            return back()->withInput()->with('error', "No application found for '{$search}'.");
        }

        $gradeLevels = GradeLevel::orderBy('level_order')->get();

        return view('admission.apply', compact('application', 'gradeLevels'));
    }
}
