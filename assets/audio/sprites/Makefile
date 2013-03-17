PRACTISE_CONVERSATION_FILES = $(shell ls ../conversations/p{1,2,3}.*.wav)

NO_REPORT_CONVERSATION_FILES = $(shell ls ../conversations/*.{ik,jij,hij}.wav)

DIRECT_INDIRECT_CONVERSATION_FILES = $(shell ls ../conversations/*.{dirik,dirjij,dirhij,indik,indjij,indhij}.wav)

CUE_FILES = $(shell ls ../cues/*.wav)

INSTRUCTION_FILES = $(shell ls ../instructions/*.wav)

DECORATION_FILES = $(shell ls ../decorations/*.wav)


CHARACTER_INTRODUCTION_FILES = $(shell ls ../instructions/m{3,4,5,6}*.wav ../instructions/intro.*.wav)

PRACTISE_FILES = ../instructions/instruction1.wav $(PRACTISE_CONVERSATION_FILES) $(DECORATION_FILES)

NO_REPORT_FILES = ../instructions/instruction2.wav $(DECORATION_FILES) $(NO_REPORT_CONVERSATION_FILES)

DIRECT_INDIRECT_FILES = ../instructions/instruction3.wav $(DECORATION_FILES) $(DIRECT_INDIRECT_CONVERSATION_FILES)


AUDIO_FILES = $(PRACTISE_CONVERSATION_FILES) $(NO_REPORT_CONVERSATION_FILES) $(DIRECT_INDIRECT_CONVERSATION_FILES) $(INSTRUCTION_FILES) $(DECORATION_FILES)

.PHONY: all

.DEFAULT: all

character_introduction.json: $(CHARACTER_INTRODUCTION_FILES)
	audiosprite --output character_introduction --export m4a $(CHARACTER_INTRODUCTION_FILES)

practise_items.json: $(PRACTISE_FILES)
	audiosprite --output practise_items --export m4a $(PRACTISE_FILES)	

no_report_items.json: $(NO_REPORT_FILES)
	audiosprite --output no_report_items --export m4a $(NO_REPORT_FILES)

direct_and_indirect_speech_items.json: $(DIRECT_INDIRECT_FILES) 
	audiosprite --output  direct_and_indirect_speech_items --export m4a $(DIRECT_INDIRECT_FILES)

sprites.json: $(AUDIO_FILES)
	audiosprite --output sprites --export m4a $(AUDIO_FILES)

all: character_introduction.json practise_items.json no_report_items.json direct_and_indirect_speech_items.json

clean:
	rm -f *.{m4a,json}