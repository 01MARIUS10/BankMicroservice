<?php

namespace App\Presentation\Http\Requests;

use App\Domain\TransactionStatus;
use Illuminate\Validation\Rule;

use App\Domain\TransactionType;
class CreateTransactionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid'],
            'libelle' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'string', Rule::enum(TransactionType::class)],
        ];
    }
}
