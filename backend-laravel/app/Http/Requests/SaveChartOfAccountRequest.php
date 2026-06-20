<?php

namespace App\Http\Requests;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveChartOfAccountRequest extends FormRequest
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
        $account = $this->route('chartOfAccount');

        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts', 'code')
                    ->ignore($account instanceof ChartOfAccount ? $account->id : null),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_account_categories', 'id'),
            ],
            'account_type' => [
                'required',
                'string',
                Rule::in(ChartOfAccount::ACCOUNT_TYPES),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}
