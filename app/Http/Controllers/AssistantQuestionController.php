<?php

namespace App\Http\Controllers;

use App\Enums\AssistantMode;
use App\Http\Requests\QuestionToAssistantRequest;
use App\Services\GPT;

class AssistantQuestionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(QuestionToAssistantRequest $request)
    {
        $data = $request->validated();

        $assistantMode = AssistantMode::from($data['assistant_mode']);

        $gpt = new GPT($assistantMode);

        if(!empty($data['should_remember_context']) and !empty($data['api_token'])) {
            $gpt->rememberContext($data['api_token']);
        }

        $answer = $gpt->ask($data['question']);

        return response()->json([
            'answer' => $answer,
        ]);
    }
}
