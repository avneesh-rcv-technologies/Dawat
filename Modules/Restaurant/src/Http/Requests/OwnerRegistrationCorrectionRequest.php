<?php

namespace Modules\Restaurant\Http\Requests;

class OwnerRegistrationCorrectionRequest extends UpdateRegistrationRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('admin')?->status;
    }
}
