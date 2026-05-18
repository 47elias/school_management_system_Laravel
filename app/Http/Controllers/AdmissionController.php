<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admission;
use App\Mail\AdmissionStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

        return view('admission.index', compact('admissions', 'stats'));
    }

    /**
     * PUBLIC: Display the admission application form.
     */
    public function index()
    {
        return view('admission.apply');
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
            'grade'                 => 'required|string|max:255',
            'address'               => 'nullable|string',
            'guardian_name'         => 'required|string|max:255',
            'phone'                 => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'previous_school'       => 'nullable|string|max:255',
            'subjects_passed'       => 'required|string',
            'academic_history'      => 'nullable|string',
            'results_file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'recommendation_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'identity_number.unique' => 'This Identity Number is already registered.',
        ]);

        $resultsPath = null;
        $recommendationPath = null;

        try {
            // 2. Handle File Uploads
            if ($request->hasFile('results_file')) {
                $resultsPath = $request->file('results_file')->store('admissions/results', 'public');
            }

            if ($request->hasFile('recommendation_letter')) {
                $recommendationPath = $request->file('recommendation_letter')->store('admissions/recommendations', 'public');
            }

            // 3. Database Transaction
            $admission = DB::transaction(function () use ($request, $resultsPath, $recommendationPath) {
                return Admission::create([
                    'identity_number'       => $request->identity_number,
                    'student_name'          => $request->name,
                    'date_of_birth'         => $request->dob,
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
                ->route('students.apply')
                ->with('success', "Application submitted! Tracking ID: {$admission->tracking_id}");

        } catch (\Exception $e) {
            // Cleanup files if the database entry fails
            if ($resultsPath) Storage::disk('public')->delete($resultsPath);
            if ($recommendationPath) Storage::disk('public')->delete($recommendationPath);

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

        return view('admission.apply', compact('application'));
    }
}
