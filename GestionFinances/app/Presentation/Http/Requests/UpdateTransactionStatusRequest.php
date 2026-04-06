<?php

namespace App\Presentation\Http\Requests;

use App\Domain\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionStatusRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid'],
            'status' => ['required', 'string', Rule::enum(TransactionStatus::class)],
        ];
    }
}
