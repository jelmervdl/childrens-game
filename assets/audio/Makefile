CONVERSATION_FILES = $(shell ls ./conversations/*.wav)

CUE_FILES = $(shell ls ./cues/*.wav)

INSTRUCTION_FILES = $(shell ls ./instructions/*.wav)

DECORATION_FILES = $(shell ls ./decorations/*.wav)

AUDIO_FILES = $(CONVERSATION_FILES) $(CUE_FILES) $(INSTRUCTION_FILES) $(DECORATION_FILES)

sprites.m4a: $(AUDIO_FILES)
	audiosprite --output sprites --export m4a $(AUDIO_FILES)

all: sprites.m4a

clean:
	rm -f sprites.m4a sprites.json