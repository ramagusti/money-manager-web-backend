<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Exports\TransactionsExport;
use App\Exports\TransactionsTemplateExport;
use App\Imports\TransactionsImport;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    /**
     * Fetch all transactions for the currently selected group.
     */
    public function index(Request $request)
    {
        $groupId = $request->query('group_id');

        if (!$groupId) {
            return response()->json(['error' => 'No group selected'], 403);
        }

        $query = Transaction::where('group_id', $groupId)->with('category');

        // Filter by transaction type (income/expense)
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('transaction_time', '>=', $request->start_date)
                ->whereDate('transaction_time', '<=', $request->end_date);
        } elseif ($request->has('date') && !empty($request->date)) {
            $date = $request->date;
            $query->whereRaw("DATE_FORMAT(transaction_time, '%Y-%m') = ?", [$date]);
        }

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $allTransactions = $query->orderBy('transaction_time', 'desc')->get();
        $totalIncome = $allTransactions->where('type', 'income')
            ->whereNotIn('category_id', [20, 21])
            ->sum('amount');
        $totalExpense = $allTransactions->where('type', 'expense')
            ->whereNotIn('category_id', [20, 21])
            ->sum('amount');
        $totalSavings = $totalIncome - $totalExpense;

        $transactions = $query->orderBy('transaction_time', 'desc')->paginate(10);

        return response()->json([
            'transactions' => $transactions,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_savings' => $totalSavings,
        ]);
    }

    /**
     * Create a new transaction in the group.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category_id' => ['required', 'exists:transaction_categories,id'],
            'group_id' => ['required', 'exists:groups,id'],
            'amount' => ['required', 'numeric'],
            'description' => ['nullable', 'string', 'max:255'],
            'actor' => ['required', 'string', 'max:255'],
            'transaction_time' => ['required', 'date'],
            'proof' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'],
        ]);

        $groupId = $request->input('group_id');

        if (!$groupId) {
            return response()->json(['error' => 'No group selected'], 403);
        }

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'group_id' => $groupId,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'description' => $request->description,
            'actor' => $request->actor,
            'transaction_time' => $request->transaction_time,
            'proof' => $proofPath,
        ]);

        return response()->json($transaction, 201);
    }

    /**
     * Update an existing transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->group_id != $request->input('group_id')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'type' => [Rule::in(['income', 'expense'])],
            'category_id' => ['exists:transaction_categories,id'],
            'group_id' => ['required', 'exists:groups,id'],
            'amount' => ['numeric'],
            'description' => ['nullable', 'string', 'max:255'],
            'actor' => ['string', 'max:255'],
            'transaction_time' => ['date'],
            'proof' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'],
        ]);

        if ($request->hasFile('proof')) {
            // Delete old proof file if exists
            if ($transaction->proof) {
                Storage::disk('public')->delete($transaction->proof);
            }
            $transaction->proof = $request->file('proof')->store('proofs', 'public');
        }

        $transaction->update($request->except('proof'));

        return response()->json($transaction);
    }

    /**
     * Delete a transaction.
     */
    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->group_id != $request->input('group_id')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transaction->delete(); // Soft delete

        return response()->json(null, 204);
    }

    public function export(Request $request)
    {
        return Excel::download(new TransactionsExport($request->group_id), 'transactions.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TransactionsTemplateExport(), 'transactions_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx',
        ]);

        Excel::import(new TransactionsImport($request->group_id), $request->file('file'));

        return response()->json(["message" => "Transactions imported successfully"]);
    }

    public function dashboardData(Request $request)
    {
        $groupId = $request->query('group_id');

        if (!$groupId) {
            return response()->json(['error' => 'No group selected'], 403);
        }

        $query = Transaction::where('group_id', $groupId)->with('category');

        // Filter by transaction type (income/expense)
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('transaction_time', '>=', $request->start_date)
                ->whereDate('transaction_time', '<=', $request->end_date);
        } elseif ($request->has('date') && !empty($request->date)) {
            $date = $request->date;
            $query->whereRaw("DATE_FORMAT(transaction_time, '%Y-%m') = ?", [$date]);
        }

        // Filter by category
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $recentTransactions = $query->clone()->orderBy('transaction_time', 'desc')->take(10)->get();

        // Exclude savings transfers when calculating rollups
        $scopedQuery = $query->clone()->whereNotIn('category_id', [20, 21]);
        $filteredTransactions = $scopedQuery->clone()->orderBy('transaction_time', 'desc')->get();

        $totalIncome = $scopedQuery->clone()->where('type', 'income')->sum('amount');
        $totalExpense = $scopedQuery->clone()->where('type', 'expense')->sum('amount');
        $totalSavings = $totalIncome - $totalExpense;

        // Member overview must stay in sync with "Available balance":
        // use all-time, raw totals (no category exclusions).
        $memberOverview = Transaction::where('group_id', $groupId)
            ->selectRaw("COALESCE(actor, 'Unknown') as actor_name")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense")
            ->groupBy('actor_name')
            ->get()
            ->map(function ($row) {
                $income = (float) $row->total_income;
                $expense = (float) $row->total_expense;

                return [
                    'actor' => $row->actor_name,
                    'total_income' => $income,
                    'total_expense' => $expense,
                    'total_savings' => $income - $expense,
                ];
            })
            ->values();

        return response()->json([
            'all_transactions' => $recentTransactions,
            'filtered_transactions' => $filteredTransactions,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_savings' => $totalSavings,
            'member_overview' => $memberOverview,
        ]);
    }
}
