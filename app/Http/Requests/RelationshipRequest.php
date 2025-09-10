<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RelationshipRequest extends FormRequest
{
    /**
     * リクエストの認可を判定
     */
    public function authorize(): bool
    {
        return true; // 認可の詳細はポリシーで制御
    }

    /**
     * バリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'family_tree_id' => ['required', 'exists:family_trees,id'],
            'person1_id' => [
                'required',
                'exists:people,id',
                Rule::unique('relationships')
                    ->where(function ($query) {
                        return $query->where('person2_id', $this->person2_id)
                            ->where('relationship_type', $this->relationship_type)
                            ->where('family_tree_id', $this->family_tree_id);
                    })
                    ->ignore($this->route('relationship')),
            ],
            'person2_id' => [
                'required',
                'exists:people,id',
                'different:person1_id'
            ],
            'relationship_type' => [
                'required',
                Rule::in(['parent_child', 'spouse', 'sibling', 'adopted_child', 'grandchild', 'nephew_niece'])
            ],
            'parent_type' => [
                Rule::requiredIf(fn() => $this->relationship_type === 'parent_child'),
                Rule::in(['father', 'mother']),
            ],
            'indirect_relationship' => [
                Rule::requiredIf(fn() => in_array($this->relationship_type, ['grandchild', 'nephew_niece'])),
                Rule::in(['paternal', 'maternal']),
            ],
            'relationship_order' => ['nullable', 'integer', 'min:1'],
            'relationship_date' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * バリデーションメッセージの日本語化
     */
    public function messages(): array
    {
        return [
            'family_tree_id.required' => '家系図IDは必須です',
            'family_tree_id.exists' => '指定された家系図が存在しません',
            'person1_id.required' => '関係者1は必須です',
            'person1_id.exists' => '指定された関係者1が存在しません',
            'person1_id.unique' => 'この関係性は既に登録されています',
            'person2_id.required' => '関係者2は必須です',
            'person2_id.exists' => '指定された関係者2が存在しません',
            'person2_id.different' => '関係者1と関係者2は異なる人物を指定してください',
            'relationship_type.required' => '関係性の種類は必須です',
            'relationship_type.in' => '指定された関係性の種類は無効です',
            'parent_type.required' => '親の種類は必須です',
            'parent_type.in' => '指定された親の種類は無効です',
            'indirect_relationship.required' => '間接的な関係性の種類は必須です',
            'indirect_relationship.in' => '指定された間接的な関係性の種類は無効です',
            'relationship_order.integer' => '関係性の順序は整数で入力してください',
            'relationship_order.min' => '関係性の順序は1以上の値を指定してください',
            'relationship_date.date' => '関係性の日付は有効な日付を入力してください',
            'relationship_date.before_or_equal' => '関係性の日付は今日以前の日付を指定してください',
            'notes.max' => '備考は1000文字以内で入力してください',
        ];
    }

    /**
     * バリデーション属性名の日本語化
     */
    public function attributes(): array
    {
        return [
            'family_tree_id' => '家系図ID',
            'person1_id' => '関係者1',
            'person2_id' => '関係者2',
            'relationship_type' => '関係性の種類',
            'parent_type' => '親の種類',
            'indirect_relationship' => '間接的な関係性の種類',
            'relationship_order' => '関係性の順序',
            'relationship_date' => '関係性の日付',
            'notes' => '備考',
        ];
    }
}
