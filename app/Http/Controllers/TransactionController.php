<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    // List transactions for groups that the user belongs to.
    public function index()
    {
        $user = Auth::user();
        // Fetch transactions from all groups the user is a member of.
        $transactions = Transaction::whereIn('group_id', $user->groups->pluck('id'))->get();
        return response()->json($transactions);
    }

    // Store a new transaction.
    public function store(Request $request)
    {
        $data = $request->validate([
            'group_id'         => 'required|exists:groups,id',
            'amount'           => 'required|numeric',
            'type'             => 'required|in:income,expense',
            'description'      => 'nullable|string',
            'actor'            => 'nullable|string',
            'transaction_time' => 'required|date',
            'proof'            => 'nullable|string',
            'category_id'      => 'nullable|exists:transaction_categories,id',
        ]);

        // Set the authenticated user as the creator.
        $data['user_id'] = Auth::id();
        $transaction = Transaction::create($data);
        return response()->json($transaction, 201);
    }

    // Show a specific transaction.
    public function show(Transaction $transaction)
    {
        return response()->json($transaction);
    }

    // Update an existing transaction.
    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'amount'           => 'sometimes|required|numeric',
            'type'             => 'sometimes|required|in:income,expense',
            'description'      => 'nullable|string',
            'actor'            => 'nullable|string',
            'transaction_time' => 'sometimes|required|date',
            'proof'            => 'nullable|string',
            'category_id'      => 'nullable|exists:transaction_categories,id',
        ]);
        $transaction->update($data);
        return response()->json($transaction);
    }

    // Delete a transaction.
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(null, 204);
    }
}
