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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_tree_id')->constrained()->onDelete('cascade');

            // 基本個人情報
            $table->string('family_name', 100)->comment('姓');
            $table->string('given_name', 100)->comment('名');
            $table->string('family_name_kana', 200)->nullable()->comment('姓（ひらがな）');
            $table->string('given_name_kana', 200)->nullable()->comment('名（ひらがな）');
            $table->enum('gender', ['male', 'female', 'unknown'])
                ->default('unknown')
                ->comment('性別');

            // 生年月日・没年月日
            $table->date('birth_date')->nullable()->comment('生年月日');
            $table->date('death_date')->nullable()->comment('没年月日');
            $table->boolean('is_alive')->default(true)->comment('生存フラグ');

            // 相続関係情報
            $table->enum('legal_status', ['heir', 'deceased', 'renounced'])
                ->default('heir')
                ->comment('相続法上の地位');
            $table->string('relationship_to_deceased', 100)
                ->nullable()
                ->comment('被相続人との続柄');

            // 住所情報
            $table->string('postal_code', 10)->nullable()->comment('郵便番号');
            $table->text('current_address')->nullable()->comment('現住所');
            $table->text('registered_domicile')->nullable()->comment('本籍地');
            $table->text('registered_address')->nullable()->comment('登記記録上の住所');

            // 表示・配置情報
            $table->integer('display_order')->default(0)->comment('表示順序');
            $table->integer('generation_level')->default(0)->comment('世代レベル');
            $table->decimal('position_x', 8, 2)->nullable()->comment('X座標');
            $table->decimal('position_y', 8, 2)->nullable()->comment('Y座標');

            // その他
            $table->text('notes')->nullable()->comment('備考');
            $table->timestamps();

            // インデックス
            $table->index('family_tree_id');
            $table->index(['family_tree_id', 'is_alive']);
            $table->index(['family_tree_id', 'legal_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
