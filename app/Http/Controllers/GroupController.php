<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use App\Models\GroupInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\GroupInvitationMail;
use App\Mail\NewMemberNotification;
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

    public function getBalance(Request $request, Group $group)
    {
        $totalIncome = $group->transactions()->where('type', 'income')->whereNotIn('category_id', [20, 21])->sum('amount');
        $totalExpense = $group->transactions()->where('type', 'expense')->whereNotIn('category_id', [20, 21])->sum('amount');

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

    public function getMembers(Request $request, Group $group)
    {
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $query = $group->users()->select('users.id', 'users.name', 'users.email', 'group_user.role');

        if ($request->has('role') && !empty($request->role)) {
            $query->where('group_user.role', $request->role);
        }

        $members = $query->paginate(10);

        return response()->json($members);
    }

    public function addMember(Request $request, Group $group)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,member',
        ]);

        // Prevent adding duplicate users
        if ($group->users()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'User already in the group'], 400);
        }

        $group->users()->attach($request->user_id, ['role' => $request->role]);

        return response()->json(['message' => 'User added successfully']);
    }

    public function inviteMember(Request $request, Group $group)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $existingUser = User::where('email', $email)->first();
        $token = Str::random(40);
        $expiresAt = now()->addDays(7);

        $invitation = GroupInvitation::updateOrCreate(
            ['email' => $email, 'group_id' => $group->id],
            ['token' => $token, 'expires_at' => $expiresAt, 'status' => 'pending']
        );

        if ($existingUser) {
            // Send "Join Group" email
            $joinUrl = env('FRONTEND_URL') . "/join-group?token=$token";
            Mail::to($email)->send(new GroupInvitationMail($group, $joinUrl, true));
        } else {
            // Send "Sign Up & Join" email
            $signupUrl = env('FRONTEND_URL') . "/signup?invite_token=$token";
            Mail::to($email)->send(new GroupInvitationMail($group, $signupUrl, false));
        }

        return response()->json(['message' => 'Invitation sent successfully!']);
    }

    public function acceptInvitation(Request $request)
    {
        $request->validate(['token' => 'required']);

        $invitation = GroupInvitation::where('token', $request->token)->first();

        if (!$invitation || $invitation->isExpired()) {
            return response()->json(['error' => 'Invalid or expired invitation'], 400);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User must be logged in'], 403);
        }

        // Add user to the group
        $group = Group::find($invitation->group_id);
        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        // Attach user to the group
        $group->users()->attach($user->id, ['role' => 'member']);
        $invitation->update(['status' => 'accepted']);

        // Notify group owner
        $admin = $group->users()->wherePivotIn('role', ['owner', 'admin'])->get();
        foreach ($admin as $adminUser) {
            Mail::to($adminUser->email)->send(new NewMemberNotification($group, $user));
        }

        return response()->json(['message' => 'You have successfully joined the group!']);
    }

    public function removeMember(Group $group, User $user)
    {
        if ($user->isGroupOwner($group->id)) {
            return response()->json(['message' => 'Owner cannot be removed'], 403);
        }

        $group->users()->detach($user->id);
        return response()->json(['message' => 'User removed successfully']);
    }

    public function updateMemberRole(Request $request, Group $group, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        // Prevent changing the owner's role
        if ($user->isGroupOwner($group->id)) {
            return response()->json(['message' => 'Owner role cannot be changed'], 403);
        }

        $group->users()->updateExistingPivot($user->id, ['role' => $request->role]);

        return response()->json(['message' => 'Role updated successfully']);
    }
}
