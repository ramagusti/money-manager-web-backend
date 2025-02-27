<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Using enum for simplicity; default is 'free'
            $table->enum('subscription', ['free', 'premium'])->default('free');
        });
    }
    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
