<?php

namespace App\Http\Resources;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ChartOfAccount $account */
        $account = $this->resource;

        return [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'account_type' => $account->account_type,
            'account_type_label' => $account->accountTypeLabel(),
            'category_id' => $account->category_id,
            'category' => $account->category === null ? null : [
                'id' => $account->category->id,
                'name' => $account->category->name,
            ],
        ];
    }
}
