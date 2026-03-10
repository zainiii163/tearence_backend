<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateVehicleCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('vehicle_category')->id;
        
        return [
            'name' => 'sometimes|required|string|max:100|unique:vehicle_categories,name,' . $categoryId,
            'slug' => 'sometimes|required|string|max:120|unique:vehicle_categories,slug,' . $categoryId,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'image' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.max' => 'Category name cannot exceed 100 characters.',
            'name.unique' => 'Category name already exists.',
            'slug.required' => 'Category slug is required.',
            'slug.max' => 'Category slug cannot exceed 120 characters.',
            'slug.unique' => 'Category slug already exists.',
            'icon.max' => 'Icon name cannot exceed 50 characters.',
            'image.max' => 'Image path cannot exceed 255 characters.',
            'sort_order.min' => 'Sort order must be 0 or greater.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug ?: Str::slug($this->name),
        ]);
    }
}
