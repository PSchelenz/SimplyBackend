<?php

namespace App\Http\Requests;

use App\Enums\AssistantMode;
use App\Rules\Questionable;
use Illuminate\Foundation\Http\FormRequest;

class QuestionToAssistantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'question' => [new Questionable],
            'assistant_mode' => 'required|in:' . implode(',', AssistantMode::values()),
            'should_remember_context' => 'boolean',
            'api_token' => 'nullable|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'should_remember_context' => $this->boolean('should_remember_context'),
        ]);
    }
}
