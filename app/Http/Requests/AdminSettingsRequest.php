<?php

namespace NUSWhispers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'word_blacklist' => 'string',
            'rejection_net_score' => 'numeric|min:0',
            'rejection_decay' => 'numeric|min:0',
        ];
    }
}
