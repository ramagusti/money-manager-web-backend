<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EntryIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ingests_entries_payload_and_creates_transactions(): void
    {
        $user = User::factory()->create();
        $group = Group::create([
            'name' => 'Household',
            'owner_id' => $user->id,
        ]);
        $group->users()->attach($user->id, ['role' => 'owner']);

        $category = TransactionCategory::where('group_id', $group->id)
            ->where('name', 'Other')
            ->where('type', 'expense')
            ->first();

        Sanctum::actingAs($user);

        $samplePayload = [
            'group_id' => $group->id,
            'output' => [
                'entries' => [
                    [
                        'type' => 'expense',
                        'category' => [
                            'id' => $category->id,
                            'name' => $category->name,
                        ],
                        'description' => 'Garbled OCR item 291k',
                        'value' => [
                            'amount' => 291000,
                            'currency' => 'IDR',
                        ],
                        'actor' => [
                            'id' => 1,
                            'name' => 'Rama Gusti',
                        ],
                        'datetime' => '2025-11-08T09:00:00+07:00',
                        'source' => 'text',
                        'confidence' => 0.6,
                    ],
                ],
                'needs_clarification' => true,
                'clarification_question' => "Please confirm the transaction type and category for 'Ncnrktot9 291k'.",
            ],
        ];

        $response = $this->postJson('/api/entries/ingest', $samplePayload);

        $response->assertCreated()
            ->assertJsonPath('transactions.0.description', 'Garbled OCR item 291k')
            ->assertJsonPath('transactions.0.category.id', $category->id)
            ->assertJsonPath('metadata.needs_clarification', true)
            ->assertJsonPath('metadata.clarification_question', "Please confirm the transaction type and category for 'Ncnrktot9 291k'.");

        $this->assertDatabaseHas('transactions', [
            'group_id' => $group->id,
            'category_id' => $category->id,
            'description' => 'Garbled OCR item 291k',
            'amount' => 291000,
            'type' => 'expense',
        ]);
    }
}
