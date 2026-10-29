<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBankingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin')?->hasPermission('admin.restaurant.approvals.banking.update')
            && $this->user('admin')?->hasPermission('admin.restaurant.approvals.banking.show');
    }

    protected function prepareForValidation(): void
    {
        $values = [];
        foreach (array_keys($this->rules()) as $field) {
            if (is_string($this->input($field))) {
                $values[$field] = trim($this->input($field));
            }
        }
        $this->merge($values);
        if (!$this->filled('account_number') && !$this->filled('account_number_confirmation')) {
            $this->request->remove('account_number');
            $this->request->remove('account_number_confirmation');
        }
    }

    public function rules(): array
    {
        $request = new BankingDetailsRequest();
        $request->setMethod('PATCH');

        return $request->rules();
    }

    public function messages(): array
    {
        return (new BankingDetailsRequest())->messages();
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);
        }

        throw new HttpResponseException(
            redirect()->route('admin.restaurant.approvals.show', ['id' => $this->route('id'), 'step' => 'banking'])
                ->withErrors($validator)
                ->withInput($this->except('account_number', 'account_number_confirmation'))
        );
    }
}
