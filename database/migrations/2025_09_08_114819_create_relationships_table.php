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
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_tree_id')->constrained()->onDelete('cascade');
            $table->foreignId('person1_id')->constrained('people')->onDelete('cascade')
                ->comment('関係者1のID');
            $table->foreignId('person2_id')->constrained('people')->onDelete('cascade')
                ->comment('関係者2のID');

            $table->enum('relationship_type', [
                'parent_child',    // 親子関係
                'spouse',          // 配偶者関係
                'sibling',         // 兄弟姉妹関係
                'adopted_child',   // 養子関係
                'grandchild',      // 孫
                'nephew_niece'     // 甥姪
            ])->comment('関係の種類');

            // 親族関係の詳細
            $table->enum('parent_type', ['father', 'mother'])
                ->nullable()
                ->comment('親の種別');

            // 孫・甥姪の場合の追加情報
            $table->enum('indirect_relationship', [
                'paternal',     // 父方
                'maternal'      // 母方
            ])->nullable()->comment('父方/母方の区別');

            // 関係の順序（兄弟姉妹、孫、甥姪の順序）
            $table->integer('relationship_order')
                ->nullable()
                ->comment('関係の順序（第一子、長男など）');

            // 関係の開始日（結婚日、養子縁組日など）
            $table->date('relationship_date')
                ->nullable()
                ->comment('関係開始日');

            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();

            // インデックスと制約
            $table->index('family_tree_id');
            $table->index(['person1_id', 'person2_id']);
            $table->index('relationship_type');
            $table->index('indirect_relationship');
            $table->unique(
                ['person1_id', 'person2_id', 'relationship_type'],
                'unique_relationship'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};
