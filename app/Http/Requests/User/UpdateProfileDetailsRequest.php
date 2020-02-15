<?php

namespace Blegrator\Http\Requests\User;

use Blegrator\Http\Requests\Request;
use Blegrator\User;

class UpdateProfileDetailsRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'birthday' => 'nullable|date',
        ];
    }
}
