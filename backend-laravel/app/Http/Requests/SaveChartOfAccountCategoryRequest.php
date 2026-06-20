<?php

namespace App\Http\Requests;

use App\Models\ChartOfAccountCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveChartOfAccountCategoryRequest extends FormRequest
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
        $category = $this->route('chartOfAccountCategory');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_account_categories', 'name')
                    ->ignore($category instanceof ChartOfAccountCategory ? $category->id : null),
            ],
        ];
    }
}
