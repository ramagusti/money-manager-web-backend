<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
}
