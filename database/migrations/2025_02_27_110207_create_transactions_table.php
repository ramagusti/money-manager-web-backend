<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // The user who recorded/created the transaction
            $table->unsignedBigInteger('user_id');
            // The group in which this transaction is recorded
            $table->unsignedBigInteger('group_id');
            // Optional: reference to a transaction category (can be null)
            $table->unsignedBigInteger('category_id')->nullable();
            // The value of the transaction
            $table->decimal('amount', 10, 2);
            // Explicit type of transaction: income or expense
            $table->enum('type', ['income', 'expense']);
            // Optional description for more context
            $table->string('description')->nullable();
            // Actor/transaction by: free text or a name from a group member
            $table->string('actor')->nullable();
            // The actual date/time when the transaction occurred
            $table->dateTime('transaction_time');
            // Proof (could be a URL or reference to an uploaded file)
            $table->string('proof')->nullable();
            // Laravel's created_at and updated_at
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('transaction_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
