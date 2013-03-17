PRACTISE_CONVERSATION_FILES = $(shell ls ../conversations/p{1,2,3}.*.wav)

NO_REPORT_CONVERSATION_FILES = $(shell ls ../conversations/*.{ik,jij,hij}.wav)

DIRECT_INDIRECT_CONVERSATION_FILES = $(shell ls ../conversations/*.{dirik,dirjij,dirhij,indik,indjij,indhij}.wav)

CUE_FILES = $(shell ls ../cues/*.wav)

INSTRUCTION_FILES = $(shell ls ../instructions/*.wav)

DECORATION_FILES = $(shell ls ../decorations/*.wav)


CHARACTER_INTRODUCTION_FILES = $(shell ls ../instructions/m{3,4,5,6}*.wav ../instructions/intro.*.wav)

NO_REPORT_FILES = ../instructions/instruction2.wav $(DECORATION_FILES) $(NO_REPORT_CONVERSATION_FILES)

DIRECT_INDIRECT_FILES = ../instructions/instruction3.wav $(DECORATION_FILES) $(DIRECT_INDIRECT_CONVERSATION_FILES)


AUDIO_FILES = $(PRACTISE_CONVERSATION_FILES) $(NO_REPORT_CONVERSATION_FILES) $(DIRECT_INDIRECT_CONVERSATION_FILES) $(INSTRUCTION_FILES) $(DECORATION_FILES)

.PHONY: all

.DEFAULT: all

character_introduction.json: $(CHARACTER_INTRODUCTION_FILES)
	audiosprite --output character_introduction --export m4a,ogg $(CHARACTER_INTRODUCTION_FILES)

practice_items.json: $(PRACTISE_CONVERSATION_FILES) $(INSTRUCTION_FILES) $(DECORATION_FILES)
	audiosprite --output practice_items --export m4a,ogg $(PRACTISE_CONVERSATION_FILES) $(INSTRUCTION_FILES) $(DECORATION_FILES)

no_report_items.json: $(NO_REPORT_FILES)
	audiosprite --output no_report_items --export m4a,ogg $(NO_REPORT_FILES)

direct_and_indirect_speech_items.json: $(DIRECT_INDIRECT_FILES) 
	audiosprite --output  direct_and_indirect_speech_items --export m4a,ogg $(DIRECT_INDIRECT_FILES)

sprites.json: $(AUDIO_FILES)
	audiosprite --output sprites --export m4a,ogg $(AUDIO_FILES)

all: character_introduction.json practice_items.json no_report_items.json direct_and_indirect_speech_items.json

clean:
	rm -f *.{m4a,json,ogg}