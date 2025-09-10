<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonRequest extends FormRequest
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
            'family_name' => ['required', 'string', 'max:50'],
            'given_name' => ['required', 'string', 'max:50'],
            'family_name_kana' => ['required', 'string', 'max:100', 'regex:/^[\p{Hiragana}\p{Katakana}ー]+$/u'],
            'given_name_kana' => ['required', 'string', 'max:100', 'regex:/^[\p{Hiragana}\p{Katakana}ー]+$/u'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'death_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
                Rule::requiredIf(fn() => !$this->is_alive),
                'after_or_equal:birth_date'
            ],
            'is_alive' => ['required', 'boolean'],
            'legal_status' => ['required', Rule::in(['natural', 'adopted', 'spouse'])],
            'relationship_to_deceased' => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'regex:/^\d{3}-\d{4}$/'],
            'current_address' => ['nullable', 'string', 'max:200'],
            'registered_domicile' => ['nullable', 'string', 'max:200'],
            'registered_address' => ['nullable', 'string', 'max:200'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'generation_level' => ['nullable', 'integer', 'min:0'],
            'position_x' => ['nullable', 'numeric'],
            'position_y' => ['nullable', 'numeric'],
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
            'family_name.required' => '姓は必須です',
            'family_name.max' => '姓は50文字以内で入力してください',
            'given_name.required' => '名は必須です',
            'given_name.max' => '名は50文字以内で入力してください',
            'family_name_kana.required' => '姓（かな）は必須です',
            'family_name_kana.max' => '姓（かな）は100文字以内で入力してください',
            'family_name_kana.regex' => '姓（かな）はひらがなまたはカタカナで入力してください',
            'given_name_kana.required' => '名（かな）は必須です',
            'given_name_kana.max' => '名（かな）は100文字以内で入力してください',
            'given_name_kana.regex' => '名（かな）はひらがなまたはカタカナで入力してください',
            'gender.required' => '性別は必須です',
            'gender.in' => '指定された性別は無効です',
            'birth_date.date' => '生年月日は有効な日付を入力してください',
            'birth_date.before_or_equal' => '生年月日は今日以前の日付を指定してください',
            'death_date.required' => '死亡日は必須です',
            'death_date.date' => '死亡日は有効な日付を入力してください',
            'death_date.before_or_equal' => '死亡日は今日以前の日付を指定してください',
            'death_date.after_or_equal' => '死亡日は生年月日以降の日付を指定してください',
            'is_alive.required' => '生存状況は必須です',
            'is_alive.boolean' => '生存状況の値が無効です',
            'legal_status.required' => '法的関係は必須です',
            'legal_status.in' => '指定された法的関係は無効です',
            'relationship_to_deceased.max' => '被相続人との関係は50文字以内で入力してください',
            'postal_code.regex' => '郵便番号は123-4567の形式で入力してください',
            'current_address.max' => '現住所は200文字以内で入力してください',
            'registered_domicile.max' => '本籍地は200文字以内で入力してください',
            'registered_address.max' => '登録住所は200文字以内で入力してください',
            'display_order.integer' => '表示順序は整数で入力してください',
            'display_order.min' => '表示順序は0以上の値を指定してください',
            'generation_level.integer' => '世代レベルは整数で入力してください',
            'generation_level.min' => '世代レベルは0以上の値を指定してください',
            'position_x.numeric' => 'X座標は数値で入力してください',
            'position_y.numeric' => 'Y座標は数値で入力してください',
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
            'family_name' => '姓',
            'given_name' => '名',
            'family_name_kana' => '姓（かな）',
            'given_name_kana' => '名（かな）',
            'gender' => '性別',
            'birth_date' => '生年月日',
            'death_date' => '死亡日',
            'is_alive' => '生存状況',
            'legal_status' => '法的関係',
            'relationship_to_deceased' => '被相続人との関係',
            'postal_code' => '郵便番号',
            'current_address' => '現住所',
            'registered_domicile' => '本籍地',
            'registered_address' => '登録住所',
            'display_order' => '表示順序',
            'generation_level' => '世代レベル',
            'position_x' => 'X座標',
            'position_y' => 'Y座標',
            'notes' => '備考',
        ];
    }
}
