<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'family_tree_id',
        'family_name',
        'given_name',
        'family_name_kana',
        'given_name_kana',
        'gender',
        'birth_date',
        'death_date',
        'is_alive',
        'legal_status',
        'relationship_to_deceased',
        'postal_code',
        'current_address',
        'registered_domicile',
        'registered_address',
        'display_order',
        'generation_level',
        'position_x',
        'position_y',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
        'is_alive' => 'boolean',
        'position_x' => 'decimal:2',
        'position_y' => 'decimal:2',
    ];

    // 家系図との関係
    public function familyTree(): BelongsTo
    {
        return $this->belongsTo(FamilyTree::class);
    }

    // この人物が関係者1となる関係性
    public function relationsAsSubject(): HasMany
    {
        return $this->hasMany(Relationship::class, 'person1_id');
    }

    // この人物が関係者2となる関係性
    public function relationsAsObject(): HasMany
    {
        return $this->hasMany(Relationship::class, 'person2_id');
    }

    // フルネーム取得
    public function getFullNameAttribute(): string
    {
        return $this->family_name . ' ' . $this->given_name;
    }

    // フルネーム（かな）取得
    public function getFullNameKanaAttribute(): string
    {
        return $this->family_name_kana . ' ' . $this->given_name_kana;
    }

    // 年齢計算
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        $endDate = $this->is_alive ? now() : ($this->death_date ?? now());
        return $this->birth_date->diffInYears($endDate);
    }
}
