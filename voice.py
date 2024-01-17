import sys

from main import GPTMediator

if __name__ == '__main__':
    filename = sys.argv[1]

    mediator = GPTMediator()
    mediator.load_question_file(filename)
    mediator.generate_voice()
