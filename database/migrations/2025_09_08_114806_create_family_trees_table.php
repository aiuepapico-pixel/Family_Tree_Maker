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
        Schema::create('family_trees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->comment('家系図タイトル');
            $table->text('description')->nullable()->comment('説明・メモ');
            $table->foreignId('deceased_person_id')->nullable()->comment('被相続人ID');
            $table->date('inheritance_date')->nullable()->comment('相続開始日');
            $table->enum('status', ['draft', 'active', 'completed'])
                ->default('draft')
                ->comment('状態');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_trees');
    }
};
