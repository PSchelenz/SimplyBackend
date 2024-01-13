import sys

from main import GPTMediator

if __name__ == '__main__':
    message = sys.argv[1]

    mediator = GPTMediator()
    mediator.generate_voice(message)