<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'items'              => 'required|array|min:1|max:50',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1|max:99',
            'note'               => 'nullable|string|max:500',
            'payment_method'     => 'nullable|string|in:cash,transfer',
            'table_number'       => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'            => 'Đơn hàng phải có ít nhất 1 món',
            'items.min'                 => 'Đơn hàng phải có ít nhất 1 món',
            'items.max'                 => 'Tối đa 50 món mỗi đơn',
            'items.*.product_id.required' => 'Sản phẩm là bắt buộc',
            'items.*.product_id.exists' => 'Sản phẩm không tồn tại',
            'items.*.quantity.required' => 'Số lượng là bắt buộc',
            'items.*.quantity.min'      => 'Số lượng phải ít nhất là 1',
            'items.*.quantity.max'      => 'Số lượng tối đa là 99',
            'note.max'                  => 'Ghi chú tối đa 500 ký tự',
            'payment_method.required'   => 'Vui lòng chọn phương thức thanh toán',
            'payment_method.in'         => 'Phương thức thanh toán không hợp lệ',
        ];
    }
}
