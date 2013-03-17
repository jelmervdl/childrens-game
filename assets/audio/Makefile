CONVERSATION_FILES = $(shell ls ./conversations/*.wav)

CUE_FILES = $(shell ls ./cues/*.wav)

INSTRUCTION_FILES = $(shell ls ./instructions/*.wav)

DECORATION_FILES = $(shell ls ./decorations/*.wav)

AUDIO_FILES = $(CONVERSATION_FILES) $(CUE_FILES) $(INSTRUCTION_FILES) $(DECORATION_FILES)

.PHONY: all

.DEFAULT: all

sprites.json: $(AUDIO_FILES)
	audiosprite --output sprites --export m4a,ogg $(AUDIO_FILES)

all: sprites.json

clean:
	rm -f sprites.json sprites.m4a sprites.ogg