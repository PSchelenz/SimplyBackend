import string
import json
import random
import openai
import os
import sys
from dotenv.main import load_dotenv
import speech_recognition as sr
from pydub import AudioSegment

load_dotenv()


class GPTMediator:
    def __init__(self):
        self.messages = []
        self.questionFileOriginalName = ''
        self.questionFileOriginalExtension = ''
        self.questionFileData = {}
        self.apiKey = os.getenv("OPENAI_API_KEY")
        self.response = ''

    def ask(self):
        self.generate_question()
        self.send_question()
        print(self.response)
        # if context is meant to be remembered
        # self.save_context()
        self.save_answer_file()

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
            json.dump(self.response, outfile)

    def get_question(self):
        if self.questionFileData['type'] == 'text':
            return self.questionFileData['question']
        elif self.questionFileData['type'] == 'audio':
            return self.get_audio_text()
        else:
            return ''

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
            print(text)

        return text

    def generate_question(self):
        question = self.get_question()

        if question:
            # load previous context if available

            # else
            self.messages.append({"role": "system", "content": self.questionFileData['assistant-mode-prompt']})
            #

            self.messages.append({"role": "user", "content": question + '?'})

    def send_question(self):
        openai.api_key = self.apiKey

        self.response = openai.ChatCompletion.create(
            model="gpt-3.5-turbo",
            messages=self.messages,
        )

    def load_previous_messages(self, filename):
        # TODO: load previous messages from file if should remember context
        pass


if __name__ == '__main__':
    filename = sys.argv[1]

    mediator = GPTMediator()
    mediator.load_question_file(filename)
    mediator.ask()
