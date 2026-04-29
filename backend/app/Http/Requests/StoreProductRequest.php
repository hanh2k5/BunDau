<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:150', Rule::unique('products', 'name')],
            'price'        => 'required|numeric|min:0',
            'description'  => 'nullable|string|max:1000',
            'category'     => 'required|string|in:bun-dau,bun-cha,combo,drink,other',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_available' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'name.max'      => 'Tên sản phẩm tối đa 150 ký tự',
            'name.unique'   => 'Tên sản phẩm đã tồn tại',
            'price.required' => 'Giá sản phẩm là bắt buộc',
            'price.min'      => 'Giá không được âm',
            'price.numeric'  => 'Giá phải là số',
        ];
    }
}
