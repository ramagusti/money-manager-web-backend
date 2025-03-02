<?php

namespace App\Imports;

use App\Models\Group;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TransactionsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $group = Group::where('name', $row['group_name'])->first();
        if (!$group) {
            Log::error("Group not found: " . $row['group_name']);
            return null;
        }

        $category = TransactionCategory::where('group_id', $group->id)->where('name', $row['category'])->first();
        if (!$category) {
            Log::error("Category not found: " . $row['category']);
            return null;
        }

        $transactionTime = $row['transaction_time'];
        // Validate the transaction time for a correct date
        if (!$this->isValidDate($transactionTime)) {
            Log::error("Invalid transaction time: " . $transactionTime);
            return null;
        }

        return new Transaction([
            'group_id' => $group->id,
            'category_id' => $category->id,
            'description' => $row['description'],
            'amount' => $row['amount'],
            'type' => $row['type'],
            'actor' => $row['actor'],
            'transaction_time' => $transactionTime,
            'user_id' => Auth::user()->id,
        ]);
    }

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $d && $d->format('Y-m-d H:i:s') === $date;
    }
}

