<?php

namespace App\Http\Requests\Admin;

use App\Services\HotelImageService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for hotel create / edit forms
 */
class HotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // image is required on create unless one is kept from the previous submit; optional on edit
        $imageRequired = $this->routeIs('adminHotelCreateProcess') && $this->keptImage() === null
            ? 'required'
            : 'nullable';

        return [
            'hotel_name' => ['required', 'string', 'max:255'],
            'prefecture_id' => ['required', 'integer', 'exists:prefectures,prefecture_id'],
            'image' => [$imageRequired, 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ];
    }

    /**
     * Identifies the form the kept image belongs to
     */
    public function formKey(): string
    {
        return $this->routeIs('adminHotelCreateProcess')
            ? 'create'
            : 'edit:' . (int) $this->input('hotel_id');
    }

    /**
     * Image kept from the previous submit, when the form asks to reuse it
     */
    public function keptImage(): ?string
    {
        if (!$this->boolean('keep_tmp_image')) {
            return null;
        }

        return app(HotelImageService::class)->kept($this->formKey());
    }

    /**
     * Keep a valid uploaded image so that the user does not have to select it again
     */
    protected function failedValidation(Validator $validator)
    {
        $images = app(HotelImageService::class);

        if ($this->hasFile('image') && !$validator->errors()->has('image')) {
            $images->keep($this->formKey(), $images->store($this->file('image'), HotelImageService::TMP_DIR));
        } elseif ($this->hasFile('image') || $this->keptImage() === null) {
            $images->forget();
        }

        parent::failedValidation($validator);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'hotel_name' => 'ホテル名',
            'prefecture_id' => '都道府県',
            'image' => 'ホテル画像',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeは必須項目です。',
            'string' => ':attributeは文字列で入力してください。',
            'hotel_name.max' => ':attributeは:max文字以内で入力してください。',
            'integer' => ':attributeを正しく選択してください。',
            'exists' => '選択された:attributeは存在しません。',
            'file' => ':attributeのアップロードに失敗しました。',
            'image' => ':attributeには画像ファイルを指定してください。',
            'mimes' => ':attributeには:values形式のファイルを指定してください。',
            'image.max' => ':attributeは:maxKB以下のファイルを指定してください。',
            'uploaded' => ':attributeのアップロードに失敗しました。',
        ];
    }
}
