import string
import json
import random
from openai import OpenAI
import os
import sys
from pathlib import Path
from dotenv.main import load_dotenv
import speech_recognition as sr
from pydub import AudioSegment
from datetime import date

load_dotenv()

week_days = {
    0: 'poniedziałek',
    1: 'wtorek',
    2: 'środa',
    3: 'czwartek',
    4: 'piątek',
    5: 'sobota',
    6: 'niedziela'
}

class GPTMediator:
    def __init__(self):
        self.messages = []
        self.questionFileOriginalName = ''
        self.questionFileOriginalExtension = ''
        self.questionFileData = {}
        self.response = None
        self.openai = OpenAI(
            api_key=os.getenv("OPENAI_API_KEY")
        )

    def ask(self):
        self.generate_question()
        self.send_question()

        self.save_answer_file()

        # if context is meant to be remembered
        if self.questionFileData['should-remember-context']:
            self.save_context()

    def load_question_file(self, filename):
        filenameParts = filename.split('.')

        self.questionFileOriginalName = filenameParts[0]
        self.questionFileOriginalExtension = filenameParts[1]

        jsonFile = open(filename)
        self.questionFileData = json.load(jsonFile)
        jsonFile.close()

    def save_answer_file(self):
        filename = self.questionFileOriginalName + '_answer.json'

        with open(filename, 'w') as outfile:
            json.dump(self.response.choices[0].message.content, outfile)

    def get_question(self):
        if self.questionFileData['type'] == 'text':
            return self.questionFileData['question']
        elif self.questionFileData['type'] == 'audio':
            return self.get_audio_text()
        else:
            return ''

    def get_pre_question(self):
        if self.questionFileData['generate-weather-link']:
            return ("Link do wygenerowania pogody to 'http://api.weatherapi.com/v1/forecast.json?key=7afaa9b0e73e41389cc192613241701&q={miasto}&aqi=no&days=5'. "
                    "W tym linku podmień {miasto} na nazwę miasta, dla którego użytkownik chce wygenerować pogodę. Odpowiedź podaj w formacie '{link: \"<wygenerowany link>\"}'. {miasto} powinno być w języku angielskim. "
                    "Jeśli użytkownik nie podał nazwy miasta, "
                    "to w tym miejscu wstaw nazwę 'Szczecin'. Wykonaj to zadanie na podstawie następującego tekstu: ")
        else:
            return f"Dziś jest dzień {date.today()} - {week_days[date.today().weekday()]}. Na podstawie JSON'a z informacjami na temat pogody podanego na końcu (nie wspominaj o nim w odpowiedzi), odpowiedz krótko na pytanie: "

    def get_audio_text(self):
        base_filename = self.questionFileData['question']
        base_extension = self.questionFileData['question'].split('.')[-1]
        wav_filename = ''.join(random.choices(string.ascii_letters + string.digits, k=10)) + '.wav'

        track = AudioSegment.from_file(base_filename, format=base_extension)
        track.export(wav_filename, format='wav')

        r = sr.Recognizer()

        with sr.AudioFile(wav_filename) as source:
            audio_data = r.record(source)
            text = r.recognize_google(audio_data, language="pl-PL")

        os.remove(wav_filename)

        return text

    def generate_question(self):
        question = self.get_question()
        pre_question = self.get_pre_question()

        if question:
            if self.questionFileData['should-remember-context']:
                self.load_previous_context()
            else:
                self.load_starting_prompt()

            self.messages.append({"role": "user", "content": pre_question + question + ('?' if self.questionFileData['type'] == 'audio' else '')})

    def send_question(self):
        self.response = self.openai.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=self.messages,
        )

    def generate_voice(self):
        message = self.questionFileData['message']
        speech_file_path = Path(__file__).parent.absolute() / "public/audio/speech.mp3"

        response = self.openai.audio.speech.create(
          model="tts-1",
          voice="echo",
          input=message
        )

        response.write_to_file(speech_file_path)

    def load_previous_context(self):
        context_file_path = open(self.questionFileData['context-filepath'])

        if not context_file_path.closed:
            context = json.load(context_file_path)
            context_file_path.close()

            if context:
                self.messages = context
            else:
                self.load_starting_prompt()

        return

    def save_context(self):
        self.messages.append({"role": "assistant", "content": self.response['choices'][0]['message']['content']})

        with open(self.questionFileData['context-filepath'], 'w') as context_file:
            json.dump(self.messages, context_file)

    def load_starting_prompt(self):
        self.messages.append({"role": "system", "content": self.questionFileData['assistant-mode-prompt']})

if __name__ == '__main__':
    filename = sys.argv[1]

    mediator = GPTMediator()
    mediator.load_question_file(filename)
    mediator.ask()
