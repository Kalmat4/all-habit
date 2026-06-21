<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dependencies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['user_id', 'is_active']);
        });

        // xxxx_create_impulses_table.php
        Schema::create('impulses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('dependency_id')->constrained()->cascadeOnDelete();
            $t->boolean('resisted');
            $t->string('trigger')->nullable();
            $t->text('comment')->nullable();
            $t->timestamps();
            // главный индекс под все отчётные запросы
            $t->index(['user_id', 'dependency_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
