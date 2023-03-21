<?php

namespace App\Services;

use App\Enums\AssistantMode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GPT
{
    /**
     * Mode of the assistant
     *
     * @var AssistantMode
     */
    private AssistantMode $assistantMode;
    public function __construct(AssistantMode $assistantMode)
    {
        $this->assistantMode = $assistantMode;
    }

    /**
     * Get first line told by system to the assistant about how it should compose answers
     *
     * @return string
     */
    private function getAssistantModeSpecificSystemOrder(): string
    {
        return match ($this->assistantMode) {
            AssistantMode::INFORMATIVE => 'Jesteś pomocnym asystentem. Twoje odpowiedzi są krótkie i zwięzłe.',
            AssistantMode::CONVERSATIONAL => 'Jesteś pomocnym asystentem. Twoje odpowiedzi są dłuższe i bardziej rozbudowane.'
        };
    }

    public function ask(mixed $question): string
    {
        Log::info($this->getAssistantModeSpecificSystemOrder());
        if($question instanceof UploadedFile) {
            $jsonData = [
                'type' => 'audio',
                'assistant-mode-prompt' => $this->getAssistantModeSpecificSystemOrder(),
            ];

            $fileName = Str::random() . '.' . $question->getClientOriginalExtension();

            $jsonData['question'] = 'storage/app/' . $question->storeAs('audio', $fileName);
        } elseif (is_string($question)) {
            $jsonData = [
                'type' => 'text',
                'assistant-mode-prompt' => $this->getAssistantModeSpecificSystemOrder(),
                'question' => $question,
            ];
        } else {
            return false;
        }

        $jsonFilePath = $this->saveQuestionFile($jsonData);
        Log::info($jsonFilePath);
        $this->callMediator($jsonFilePath);

        $answerFilePath = $this->getAnswerFilePath($jsonFilePath);

        $fp = fopen($answerFilePath, 'r');
        $fileText = fread($fp, filesize($answerFilePath));
        fclose($fp);

        return json_decode($fileText, true)['choices'][0]['message']['content'];
    }

    private function saveQuestionFile(array $jsonData): string
    {
        $jsonData = json_encode($jsonData, JSON_PRETTY_PRINT);

        $jsonFilePath = storage_path('app/jsons') . '/' . Str::random() . '_question.json';

        $fp = fopen($jsonFilePath, 'w');
        fwrite($fp, $jsonData);
        fclose($fp);

        return $jsonFilePath;
    }

    private function callMediator(string $jsonFilePath): void
    {
        exec('cd .. && . venv/bin/activate && python3 main.py ' . $jsonFilePath, $output, $returnCode);
    }

    private function getAnswerFilePath(string $jsonFilePath): string
    {
        $pathWithoutExtension = explode('.', $jsonFilePath)[0];

        return $pathWithoutExtension . '_answer.json';
    }
}
