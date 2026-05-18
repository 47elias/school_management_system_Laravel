<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderBy('expense_date', 'desc')->paginate(15);
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category'     => 'nullable|string',
            'reference_no' => 'nullable|string',
            'notes'        => 'nullable|string',
        ]);

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function destroy($id)
    {
        Expense::findOrFail($id)->delete();
        return back()->with('success', 'Expense deleted.');
    }
}
