<?php

namespace Database\Seeders;

use App\Models\FamilyTree;
use App\Models\Person;
use App\Models\Relationship;
use App\Models\User;
use Illuminate\Database\Seeder;

class FamilyTreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        // サンプル家系図の作成
        $familyTree = FamilyTree::create([
            'user_id' => $user->id,
            'title' => 'サンプル家系図',
            'description' => 'これはサンプルの家系図です。',
            'status' => 'active',
        ]);

        // 家族メンバーの作成
        $grandfather = Person::create([
            'family_tree_id' => $familyTree->id,
            'family_name' => '山田',
            'given_name' => '一郎',
            'family_name_kana' => 'やまだ',
            'given_name_kana' => 'いちろう',
            'gender' => 'male',
            'birth_date' => '1940-01-01',
            'death_date' => '2020-12-31',
            'is_alive' => false,
            'legal_status' => 'deceased',
            'generation_level' => 0,
        ]);

        $grandmother = Person::create([
            'family_tree_id' => $familyTree->id,
            'family_name' => '山田',
            'given_name' => 'はな',
            'family_name_kana' => 'やまだ',
            'given_name_kana' => 'はな',
            'gender' => 'female',
            'birth_date' => '1942-03-15',
            'is_alive' => true,
            'legal_status' => 'heir',
            'generation_level' => 0,
        ]);

        $father = Person::create([
            'family_tree_id' => $familyTree->id,
            'family_name' => '山田',
            'given_name' => '太郎',
            'family_name_kana' => 'やまだ',
            'given_name_kana' => 'たろう',
            'gender' => 'male',
            'birth_date' => '1970-06-20',
            'is_alive' => true,
            'legal_status' => 'heir',
            'generation_level' => 1,
        ]);

        // 関係性の作成
        Relationship::create([
            'family_tree_id' => $familyTree->id,
            'person1_id' => $grandfather->id,
            'person2_id' => $grandmother->id,
            'relationship_type' => 'spouse',
            'relationship_date' => '1965-04-01',
        ]);

        Relationship::create([
            'family_tree_id' => $familyTree->id,
            'person1_id' => $grandfather->id,
            'person2_id' => $father->id,
            'relationship_type' => 'parent_child',
            'parent_type' => 'father',
            'relationship_order' => 1,
        ]);

        Relationship::create([
            'family_tree_id' => $familyTree->id,
            'person1_id' => $grandmother->id,
            'person2_id' => $father->id,
            'relationship_type' => 'parent_child',
            'parent_type' => 'mother',
            'relationship_order' => 1,
        ]);

        // 被相続人の設定
        $familyTree->deceased_person_id = $grandfather->id;
        $familyTree->inheritance_date = '2020-12-31';
        $familyTree->save();
    }
}
