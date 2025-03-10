<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GroupController extends Controller
{
    // List all groups that the authenticated user belongs to.
    public function index()
    {
        $user = Auth::user();
        // Ensure you have defined the groups relationship in your User model.
        $groups = $user->groups;
        return response()->json($groups);
    }

    // Create a new group.
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create the group.
        $group = Group::create($data);
        // Attach the authenticated user as the owner.
        $group->users()->attach(Auth::user()->id, ['role' => 'owner']);
        return response()->json($group, 201);
    }

    // Show a single group.
    public function show(Group $group)
    {
        return response()->json($group);
    }

    // Update an existing group.
    public function update(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $group->update($data);
        return response()->json($group);
    }

    // Delete a group.
    public function destroy(Group $group)
    {
        // You might want to restrict deletion to only the owner.
        $group->delete();
        return response()->json(null, 204);
    }

    public function getMembers(Group $group)
    {
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $members = $group->users()->select('users.id', 'users.name', 'group_user.role')->get();
        return response()->json($members);
    }

    public function getBalance(Request $request, Group $group)
    {
        $totalIncome = $group->transactions()->where('type', 'income')->sum('amount');
        $totalExpense = $group->transactions()->where('type', 'expense')->sum('amount');

        return response()->json([
            'balance' => $totalIncome - $totalExpense,
        ]);
    }

    public function getGoal(Request $request, Group $group)
    {
        return response()->json($group->goal_amount ?? 0);
    }

    public function storeGoal(Request $request, Group $group)
    {
        $group->update([
            'goal_amount' => $request->goal_amount,
        ]);

        return response()->json($group);
    }

    public function getIncomeExpense(Request $request, Group $group)
    {
        $now = Carbon::now();
        $query = $group->transactions()
            ->whereRaw("DATE_FORMAT(transaction_time, '%Y-%m') = ?", [$now->format('Y-m')]);

        $totalIncome = $query->clone()->where('type', 'income')->sum('amount');
        $totalExpense = $query->clone()->where('type', 'expense')->sum('amount');

        return response()->json([
            'income' => $totalIncome,
            'expense' => $totalExpense,
        ]);
    }
}
