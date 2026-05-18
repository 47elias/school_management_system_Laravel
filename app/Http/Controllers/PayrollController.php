<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payslip;
use App\Models\Payment; // For Income calculation
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index()
    {
        // 1. Fetch Staff/Teachers for the dropdown
        $teachers = User::whereIn('role', ['teacher', 'admin', 'staff'])->get();

        // 2. Get payslip history with user details
        $payslips = Payslip::with('user')->orderBy('payment_date', 'desc')->paginate(15);

        // --- FINANCIAL SUMMARY LOGIC ---

        // 3. Calculate Total Income (All fees paid by students)
        $totalIncome = Payment::sum('amount_paid');

        // 4. Calculate Total Expenses (All salaries paid to staff)
        $totalExpenses = Payslip::sum('net_salary');

        // 5. Calculate Current School Balance
        $schoolBalance = $totalIncome - $totalExpenses;

        return view('payroll.index', compact(
            'teachers',
            'payslips',
            'totalIncome',
            'totalExpenses',
            'schoolBalance'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'pay_period'   => 'required|string',
            'base_salary'  => 'required|numeric|min:0',
            'allowances'   => 'nullable|numeric|min:0',
            'deductions'   => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'remarks'      => 'nullable|string'
        ]);

        // Logic: Net Salary = (Base + Allowances) - Deductions
        $base = (float)$data['base_salary'];
        $allow = (float)($data['allowances'] ?? 0);
        $deduct = (float)($data['deductions'] ?? 0);
        $netSalary = ($base + $allow) - $deduct;

        // --- PRE-PAYMENT VALIDATION ---

        // Check if school has enough money to pay this salary
        $totalIncome = Payment::sum('amount_paid');
        $totalExpenses = Payslip::sum('net_salary');
        $currentBalance = $totalIncome - $totalExpenses;

        if ($netSalary > $currentBalance) {
            return back()->with('error', 'Insufficient Funds! The school balance ($' . number_format($currentBalance, 2) . ') is lower than the requested salary payment.')
                         ->withInput();
        }

        $data['net_salary'] = $netSalary;

        Payslip::create($data);

        return redirect()->route('payroll.index')->with('success', 'Payslip generated and funds deducted successfully!');
    }

    public function destroy($id)
    {
        // Finding and deleting also effectively "returns" the money to the school balance
        Payslip::findOrFail($id)->delete();
        return back()->with('success', 'Payslip record deleted. Fund balance adjusted.');
    }

    public function print($id)
    {
        $payslip = Payslip::with('user')->findOrFail($id);
        return view('payroll.print', compact('payslip'));
    }
}
