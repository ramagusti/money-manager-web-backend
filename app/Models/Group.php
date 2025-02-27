<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_private',
        'owner_id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    protected static function booted()
    {
        static::created(function ($group) {
            $defaultExpenseCategories = [
                'Food & Beverages', 'Groceries', 'Housing', 'Bills & Subscriptions', 
                'Laundry', 'Travel', 'Transportation', 'Health, Selfcare & Beauty',
                'Apparels & Wearables', 'Gift', 'Gadget & Electronics', 'Entertainment',
                'Investment', 'Education & Work', 'Other'
            ];

            $defaultIncomeCategories = [
                'Work', 'Gift', 'Investment', 'Other'
            ];

            // Insert expense categories
            foreach ($defaultExpenseCategories as $category) {
                $group->transactionCategories()->create([
                    'type' => 'expense',
                    'name' => $category,
                ]);
            }

            // Insert income categories
            foreach ($defaultIncomeCategories as $category) {
                $group->transactionCategories()->create([
                    'type' => 'income',
                    'name' => $category,
                ]);
            }
        });
    }

    public function transactionCategories()
    {
        return $this->hasMany(TransactionCategory::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot('role')
            ->withTimestamps();
    }
}
