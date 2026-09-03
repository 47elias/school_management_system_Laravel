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
        // Core Statistics
        $studentCount = Student::count();$examCount = Exam::count();
        $subjectCount = Subject::count();$userCount = User::count();

        // Financial Metrics for Dashboard
        $totalRevenue = Payment::sum('amount_paid');$totalGeneralExpenses = Expense::sum('amount');
        $totalSalaries = Payslip::sum('net_salary');$totalExpenses = $totalGeneralExpenses +$totalSalaries;
        $netBalance = $totalRevenue -$totalExpenses;

        $recentExams = Exam::with('subject')->orderBy('exam_date', 'desc')->take(5)->get();

        // Enrollment Chart Data
        $gradesData = Student::select('grade', DB::raw('count(*) as total'))
            ->groupBy('grade')
            ->whereNotNull('grade')
            ->get();

        // Performance Chart Data
        $passCount = Mark::where('score', '>=', 50)->count();$failCount = Mark::where('score', '<', 50)->count();

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

        return view('dashboard', compact(
            'studentCount', 'examCount', 'subjectCount', 'userCount',
            'totalRevenue', 'totalExpenses', 'netBalance',
            'recentExams', 'gradesData', 'passCount', 'failCount',
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