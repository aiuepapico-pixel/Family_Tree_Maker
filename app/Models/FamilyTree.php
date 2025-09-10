<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyTree extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'deceased_person_id',
        'inheritance_date',
        'status',
    ];

    protected $casts = [
        'inheritance_date' => 'date',
    ];

    // ユーザーとの関係
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 被相続人との関係
    public function deceasedPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'deceased_person_id');
    }

    // 家系図内の全ての人物
    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    // 家系図内の全ての関係性
    public function relationships(): HasMany
    {
        return $this->hasMany(Relationship::class);
    }
}
