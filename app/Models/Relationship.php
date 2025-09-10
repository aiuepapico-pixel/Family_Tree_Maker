<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Relationship extends Model
{
    protected $fillable = [
        'family_tree_id',
        'person1_id',
        'person2_id',
        'relationship_type',
        'parent_type',
        'indirect_relationship',
        'relationship_order',
        'relationship_date',
        'notes',
    ];

    protected $casts = [
        'relationship_date' => 'date',
    ];

    // 家系図との関係
    public function familyTree(): BelongsTo
    {
        return $this->belongsTo(FamilyTree::class);
    }

    // 関係者1（主体）との関係
    public function person1(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person1_id');
    }

    // 関係者2（対象）との関係
    public function person2(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person2_id');
    }

    // 関係性の説明を取得
    public function getRelationshipDescriptionAttribute(): string
    {
        $description = match ($this->relationship_type) {
            'parent_child' => $this->parent_type === 'father' ? '父' : '母',
            'spouse' => '配偶者',
            'sibling' => '兄弟姉妹',
            'adopted_child' => '養子',
            'grandchild' => $this->indirect_relationship === 'paternal' ? '父方の孫' : '母方の孫',
            'nephew_niece' => $this->indirect_relationship === 'paternal' ? '父方の甥/姪' : '母方の甥/姪',
            default => '関係者'
        };

        if ($this->relationship_order) {
            $description .= "（第{$this->relationship_order}子）";
        }

        return $description;
    }
}
