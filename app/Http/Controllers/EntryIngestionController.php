<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EntryIngestionController extends Controller
{
    /**
     * Accepts OCR output payloads and persists them as transactions.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'user_id' => ['required', 'exists:users,id'],
            'output.entries' => ['required', 'array', 'min:1'],
            'output.entries.*.type' => ['required', Rule::in(['income', 'expense'])],
            'output.entries.*.category' => ['required'],
            'output.entries.*.category.id' => ['nullable', 'integer'],
            'output.entries.*.category.name' => ['nullable', 'string', 'max:255'],
            'output.entries.*.description' => ['nullable', 'string'],
            'output.entries.*.value.amount' => ['required', 'numeric'],
            'output.entries.*.value.currency' => ['required', 'string', 'max:3'],
            'output.entries.*.actor' => ['nullable'],
            'output.entries.*.actor.id' => ['nullable', 'integer'],
            'output.entries.*.actor.name' => ['nullable', 'string', 'max:255'],
            'output.entries.*.datetime' => ['required', 'date'],
            'output.entries.*.source' => ['nullable', 'string', 'max:50'],
            'output.entries.*.confidence' => ['nullable', 'numeric', 'between:0,1'],
            'output.needs_clarification' => ['sometimes', 'boolean'],
            'output.clarification_question' => ['nullable', 'string'],
        ]);

        $groupId = $validated['group_id'];
        // $user = $request->user();

        // if (!$user->groups()->where('group_id', $groupId)->exists()) {
        //     return response()->json(['message' => 'You are not a member of this group.'], 403);
        // }
        $userId = $validated['user_id'];

        $entries = $validated['output']['entries'];

        $transactions = [];

        DB::beginTransaction();

        try {
            foreach ($entries as $index => $entry) {
                $categoryId = $this->resolveCategoryId($groupId, $entry);

                if (!$categoryId) {
                    throw ValidationException::withMessages([
                        "output.entries.$index.category" => ['Category information is missing or invalid.'],
                    ]);
                }

                $transactions[] = Transaction::create([
                    // 'user_id' => $user->id,
                    'user_id' => $userId,
                    'group_id' => $groupId,
                    'category_id' => $categoryId,
                    'amount' => round($entry['value']['amount'], 2),
                    'type' => $entry['type'],
                    'description' => $entry['description'] ?? null,
                    'actor' => $entry['actor']['name'] ?? $entry['actor']['id'] ?? null,
                    'transaction_time' => Carbon::parse($entry['datetime']),
                    'proof' => null,
                ])->fresh(['category']);
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        return response()->json([
            'transactions' => $transactions,
            'metadata' => [
                'needs_clarification' => $validated['output']['needs_clarification'] ?? false,
                'clarification_question' => $validated['output']['clarification_question'] ?? null,
            ],
        ], 201);
    }

    /**
     * Resolve a category id, creating the category when only a name is provided.
     */
    protected function resolveCategoryId(int $groupId, array $entry): ?int
    {
        $categoryData = $entry['category'] ?? null;

        if (!$categoryData) {
            return null;
        }

        if (!empty($categoryData['id'])) {
            $category = TransactionCategory::where('group_id', $groupId)
                ->where('id', $categoryData['id'])
                ->first();

            if ($category) {
                $this->ensureCategoryTypeMatchesEntry($category, $entry['type']);

                return $category->id;
            }
        }

        if (!empty($categoryData['name'])) {
            $category = TransactionCategory::where('group_id', $groupId)
                ->where('name', $categoryData['name'])
                ->first();

            if ($category) {
                $this->ensureCategoryTypeMatchesEntry($category, $entry['type']);

                return $category->id;
            }

            $category = TransactionCategory::create([
                'group_id' => $groupId,
                'type' => $entry['type'],
                'name' => $categoryData['name'],
            ]);

            return $category->id;
        }

        return null;
    }

    protected function ensureCategoryTypeMatchesEntry(TransactionCategory $category, string $entryType): void
    {
        if ($category->type !== $entryType) {
            throw ValidationException::withMessages([
                'category_id' => [
                    "Category {$category->name} expects {$category->type} entries but {$entryType} was provided.",
                ],
            ]);
        }
    }
}
