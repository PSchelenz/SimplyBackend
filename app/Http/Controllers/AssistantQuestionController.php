<?php

namespace App\Http\Controllers;

use App\Enums\AssistantMode;
use App\Http\Requests\QuestionToAssistantRequest;
use App\Services\GPT;
use App\Services\WeatherBro;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $answer = $gpt->ask($data['question'], true);

        $answer = array_filter(explode(" ", $answer), function ($item) {
            return str_contains($item, 'http');
        });

        $answer = trim(array_values($answer)[0], '\{}"\'\\n');

        $forecast = (new WeatherBro())
            ->fetchForecast($answer);

        if ($error = $forecast->getError()) {
            return response()->json([
                'answer' => $error,
            ]);
        }

        $forecast = $forecast
            ->parseForecastData()
            ->toJson();

        $finalAnswer = $gpt->ask($data['question'] . ".\n" . $forecast);

        return response()->json([
            'answer' => $finalAnswer,
        ]);
    }
}
