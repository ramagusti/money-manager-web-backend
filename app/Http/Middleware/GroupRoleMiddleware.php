<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupRoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();
        $group = $request->route('group'); // Extract group from the request

        if (!$group || !$user->groups()->where('group_id', $group->id)->wherePivotIn('role', explode('|', $role))->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
