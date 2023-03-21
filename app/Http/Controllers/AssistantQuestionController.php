<?php

namespace App\Http\Controllers;

use App\Enums\AssistantMode;
use App\Services\GPT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssistantQuestionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $gpt = new GPT(AssistantMode::INFORMATIVE);

        $answer = $gpt->ask($request->question);

        return response()->json([
            'answer' => $answer,
        ]);
    }
}
