<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseCoinsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'euros' => 'required|integer|min:1|max:99',
            'payment_type' => ['required', Rule::in(['MBWAY', 'PAYPAL', 'IBAN', 'MB', 'VISA'])],
            'payment_reference' => ['required', 'string', function ($attribute, $value, $fail) {
                $this->validateReference($this->payment_type, $value, $fail);
            }],
        ];
    }

    private function validateReference($type, $reference, $fail)
    {
        $patterns = [
            'MBWAY' => '/^9\d{8}$/',
            'PAYPAL' => FILTER_VALIDATE_EMAIL,
            'IBAN' => '/^[A-Z]{2}\d{23}$/',
            'MB' => '/^\d{5}-\d{9}$/',
            'VISA' => '/^4\d{15}$/',
        ];

        if (isset($patterns[$type]) && !preg_match($patterns[$type], $reference)) {
            $fail('A referência de pagamento é inválida para o tipo selecionado.');
        }
    }

    public function messages()
    {
        return [
            'euros.required' => 'O valor é obrigatório.',
            'euros.min' => 'O valor mínimo é 1€.',
            'euros.max' => 'O valor máximo é 99€.',
            'payment_type.required' => 'O tipo de pagamento é obrigatório.',
            'payment_type.in' => 'Tipo de pagamento inválido.',
            'payment_reference.required' => 'A referência do pagamento é obrigatória.',
        ];
    }
}
