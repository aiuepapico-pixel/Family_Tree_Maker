<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FamilyTreeRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'deceased_person_id' => [
                'nullable',
                'exists:people,id',
                Rule::requiredIf(fn() => $this->status === 'completed')
            ],
            'inheritance_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
                Rule::requiredIf(fn() => $this->status === 'completed')
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'in_progress', 'completed', 'archived'])
            ],
        ];
    }

    /**
     * バリデーションメッセージの日本語化
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'ユーザーIDは必須です',
            'user_id.exists' => '指定されたユーザーが存在しません',
            'title.required' => 'タイトルは必須です',
            'title.max' => 'タイトルは100文字以内で入力してください',
            'description.max' => '説明は1000文字以内で入力してください',
            'deceased_person_id.required' => '被相続人の選択は必須です',
            'deceased_person_id.exists' => '指定された被相続人が存在しません',
            'inheritance_date.required' => '相続開始日は必須です',
            'inheritance_date.date' => '相続開始日は有効な日付を入力してください',
            'inheritance_date.before_or_equal' => '相続開始日は今日以前の日付を指定してください',
            'status.required' => 'ステータスは必須です',
            'status.in' => '指定されたステータスは無効です',
        ];
    }

    /**
     * バリデーション属性名の日本語化
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'ユーザーID',
            'title' => 'タイトル',
            'description' => '説明',
            'deceased_person_id' => '被相続人',
            'inheritance_date' => '相続開始日',
            'status' => 'ステータス',
        ];
    }
}
