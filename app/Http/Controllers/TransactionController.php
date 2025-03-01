<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        $query = Transaction::where('group_id', $groupId);

        // Filter by transaction type (income/expense)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_time', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->orderBy('transaction_time', 'desc')->paginate(10);

        return response()->json($transactions);
    }

    /**
     * Create a new transaction in the group.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category_id' => ['required', 'exists:transaction_categories,id'],
            'amount' => ['required', 'numeric'],
            'description' => ['nullable', 'string', 'max:255'],
            'actor' => ['required', 'string', 'max:255'],
            'transaction_time' => ['required', 'date'],
            'proof' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'],
        ]);

        $groupId = $request->user()->current_group_id;

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
        if ($transaction->group_id !== $request->user()->current_group_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'type' => [Rule::in(['income', 'expense'])],
            'category_id' => ['exists:transaction_categories,id'],
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
        if ($transaction->group_id !== $request->user()->current_group_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($transaction->proof) {
            Storage::disk('public')->delete($transaction->proof);
        }

        $transaction->delete();

        return response()->json(null, 204);
    }
}
