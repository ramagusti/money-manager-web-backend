<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            // Optionally store the owner directly if you wish (foreign key to users)
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamps();
            
            // Foreign key constraint (if you want to enforce it now)
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
