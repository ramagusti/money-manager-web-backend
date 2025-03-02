<?php

namespace App\Exports;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionsTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $userGroups = Auth::user()->groups;

        return collect([
            [
                "Group Name" => $userGroups->first()->name ?? "Home",
                "Category" => $userGroups->first()->transactionCategories->where('type', 'expense')->first()?->name ?? "Food & Beverages",
                "Description" => "Spaghetti",
                "Amount" => 100,
                "Type" => "expense",
                "Actor" => Auth::user()->name ?? "Me",
                "Transaction Time" => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            "Group Name",
            "Category",
            "Description",
            "Amount",
            "Type",
            "Actor",
            "Transaction Time",
        ];
    }
}

