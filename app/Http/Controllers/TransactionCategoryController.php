<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\Auth;

class TransactionCategoryController extends Controller
{
    /**
     * Get all categories for the current group.
     */
    public function index(Request $request)
    {
        $groupId = $request->query('group_id');

        if (!$groupId) {
            return response()->json(['error' => 'No group selected'], 403);
        }

        $categories = TransactionCategory::where('group_id', $groupId)->get();

        return response()->json($categories);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:income,expense'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        $groupId = $request->user()->current_group_id;

        if (!$groupId) {
            return response()->json(['error' => 'No group selected'], 403);
        }

        $category = TransactionCategory::create([
            'group_id' => $groupId,
            'type' => $request->type,
            'name' => $request->name,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Delete a category.
     */
    public function destroy(Request $request, TransactionCategory $category)
    {
        if ($category->group_id !== $request->user()->current_group_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
