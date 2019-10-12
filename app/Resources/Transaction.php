<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => (string) $this->date,
            'description' => $this->description,
            'opposing_account' => [
                'name' => $this->opposing_account_name,
                'iban' => $this->opposing_account_iban
            ],
            'amount' => floatval($this->amount),
            'category_id' => $this->category_id,
            'category' => $this->category ? (new Category($this->category))->toArray($request) : null,
            'account_id' => $this->account_id,
            'account' => $this->account ? (new Account($this->account))->toArray($request) : null,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at
        ];
    }
}
