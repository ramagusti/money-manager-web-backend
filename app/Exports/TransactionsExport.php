<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    protected $groupId;

    public function __construct($groupId)
    {
        $this->groupId = $groupId;
    }

    public function collection()
    {
        return Transaction::where('group_id', $this->groupId)
            ->select('group_id', 'category_id', 'description', 'amount', 'type', 'actor', 'transaction_time')
            ->with('category:id,name', 'group:id,name')
            ->get()
            ->map(function ($transaction) {
                return [
                    'group_id' => $transaction->group_id,
                    'group' => $transaction->group->name,
                    'category_id' => $transaction->category_id,
                    'category' => $transaction->category->name,
                    'description' => $transaction->description,
                    'amount' => $transaction->amount,
                    'type' => $transaction->type,
                    'actor' => $transaction->actor,
                    'transaction_time' => $transaction->transaction_time,
                ];
            });
    }

    public function headings(): array
    {
        return [
            "Group ID",
            "Group Name",
            "Category ID",
            "Category",
            "Description",
            "Amount",
            "Type",
            "Actor",
            "Transaction Time",
        ];
    }
}
