<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare request data for validation
     */
    public function prepareForValidation(): void
    {
        $this->merge(
            [
                'id' => $this->id
            ]
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:products,id',
            'name' => 'required',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:1',
            'enabled' => 'required|boolean'
        ];
    }
}
