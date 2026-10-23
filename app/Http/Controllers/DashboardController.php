<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use App\Models\Mark;
use App\Models\InventoryStock;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Payslip;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\ClassActivity;
use App\Models\Admission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * UPDATED SERVICE: Main Dashboard
     */
    public function index()
    {
        $currentTerm = Term::where('is_current', true)->first();
        $studentCount = Student::inRegister()->where('status', 'active')->count();
        $examCount = Exam::when($currentTerm, fn ($query) => $query->where('term_id', $currentTerm->id))->count();
        $subjectCount = Subject::count();
        $userCount = User::count();
        $classCount = SchoolClass::count();
        $newStudentsCount = Student::inRegister()->where('enrollment_date', '>=', Carbon::now()->subDays(30)->startOfDay())->count();
        $pendingAdmissionsCount = Admission::whereIn('status', ['pending', 'reviewing', 'interview'])->count();

        // Financial Metrics for Dashboard
        $totalRevenue = Payment::where('amount_paid', '>', 0)->sum('amount_paid');
        $totalGeneralExpenses = Expense::sum('amount');
        $totalSalaries = Payslip::sum('net_salary');
        $totalExpenses = $totalGeneralExpenses + $totalSalaries;
        $netBalance = $totalRevenue - $totalExpenses;

        $upcomingExams = Exam::with(['subject', 'schoolClass'])
            ->whereDate('exam_date', '>=', Carbon::today())
            ->when($currentTerm, fn ($query) => $query->where('term_id', $currentTerm->id))
            ->orderBy('exam_date')
            ->take(6)
            ->get();

        // Enrollment Chart Data
        $gradesData = Student::inRegister()->select('grade', DB::raw('count(*) as total'))
            ->groupBy('grade')
            ->whereNotNull('grade')
            ->get();

        $activitiesCount = ClassActivity::when($currentTerm, fn ($query) => $query->where('term_id', $currentTerm->id))->count();
        $marksEnteredCount = Mark::whereHas('exam', fn ($query) => $query->when($currentTerm, fn ($termQuery) => $termQuery->where('term_id', $currentTerm->id)))->count();
        $lowStockCount = InventoryItem::whereColumn('quantity', '<=', 'alert_level')->count();

        // Weekly Stock Issuance Data (Last 7 Days)
        $days = collect(range(6, 0))->map(function($i) {
            return Carbon::now()->subDays($i)->format('D');
        });

        $stockOutData = collect(range(6, 0))->map(function($i) {
            return InventoryStock::where('type', 'out')
                ->whereDate('created_at', Carbon::now()->subDays($i))
                ->sum('quantity');
        });

        // Top 5 Most Issued Items
        $topIssuedItems = InventoryStock::with('item')
            ->select('inventory_item_id', DB::raw('SUM(quantity) as total_issued'))
            ->where('type', 'out')
            ->groupBy('inventory_item_id')
            ->orderBy('total_issued', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'studentCount', 'examCount', 'subjectCount', 'userCount', 'classCount',
            'newStudentsCount', 'pendingAdmissionsCount', 'activitiesCount', 'marksEnteredCount', 'lowStockCount', 'currentTerm',
            'totalRevenue', 'totalExpenses', 'netBalance',
            'upcomingExams', 'gradesData',
            'days', 'stockOutData', 'topIssuedItems'
        ));
    }

    /**
     * MODULE: Edit Profile
     */
    public function editProfile()
    {
        $admin = Auth::user(); 
        return view('settings.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
        ]);

        $admin->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * MODULE: Change Password
     */
    public function showChangePassword()
    {
        return view('settings.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $admin = Auth::user();

        if (!Hash::check($request->old_password,$admin->password)) {
            return back()->withErrors(['old_password' => 'The current password you entered is incorrect.']);
        }

        $admin->password = Hash::make($request->password);$admin->save();

        return back()->with('success', 'Admin password updated successfully!');
    }
}
