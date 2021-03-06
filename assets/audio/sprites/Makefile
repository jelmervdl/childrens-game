SHELL = /bin/bash
AUDIOSPRITE = audiosprite
FORMATS = m4a,ogg
ARGS = --export $(FORMATS)

PRACTISE_CONVERSATION_FILES = $(shell ls ../conversations/p{1,2,3}.*.wav)
NO_REPORT_CONVERSATION_FILES = $(shell ls ../conversations/*.{ik,jij,hij}.wav)
DIRECT_INDIRECT_CONVERSATION_FILES = $(shell ls ../conversations/*.{dirik,dirjij,dirhij,indik,indjij,indhij}.wav)
CUE_FILES = $(shell ls ../cues/*.wav)
INSTRUCTION_FILES = $(shell ls ../instructions/*.wav)
DECORATION_FILES = $(shell ls ../decorations/*.wav)

CHARACTER_INTRODUCTION_FILES = $(shell ls ../instructions/m{1,3,4,5,6}*.wav ../instructions/intro.*.wav)
PRACTISE_FILES = ../instructions/instruction1.wav ../instructions/m7.wav $(PRACTISE_CONVERSATION_FILES) $(DECORATION_FILES)
NO_REPORT_FILES = ../instructions/instruction2.wav $(DECORATION_FILES) $(NO_REPORT_CONVERSATION_FILES)
DIRECT_INDIRECT_FILES = ../instructions/instruction3.wav $(DECORATION_FILES) $(DIRECT_INDIRECT_CONVERSATION_FILES)
OBJECT_INTRODUCTION_FILES = ../instructions/m2.wav $(shell ls ../instructions/o*.wav)
THANK_YOU_FILES = ../instructions/m8.wav

AUDIO_FILES = $(PRACTISE_CONVERSATION_FILES) $(NO_REPORT_CONVERSATION_FILES) $(DIRECT_INDIRECT_CONVERSATION_FILES) $(INSTRUCTION_FILES) $(DECORATION_FILES)

.PHONY: all

.DEFAULT: all

character_introduction.json: $(CHARACTER_INTRODUCTION_FILES)
	$(AUDIOSPRITE) $(ARGS) --output character_introduction $(CHARACTER_INTRODUCTION_FILES)

object_introduction.json: $(OBJECT_INTRODUCTION_FILES)
	$(AUDIOSPRITE) $(ARGS) --output object_introduction $(OBJECT_INTRODUCTION_FILES)

practise_items.json: $(PRACTISE_FILES)
	$(AUDIOSPRITE) $(ARGS) --output practise_items $(PRACTISE_FILES)	

no_report_items.json: $(NO_REPORT_FILES)
	$(AUDIOSPRITE) $(ARGS) --output no_report_items $(NO_REPORT_FILES)

direct_and_indirect_speech_items.json: $(DIRECT_INDIRECT_FILES) 
	$(AUDIOSPRITE) $(ARGS) --output  direct_and_indirect_speech_items $(DIRECT_INDIRECT_FILES)

thank_you.json: $(THANK_YOU_FILES)
	$(AUDIOSPRITE) $(ARGS) --output thank_you $(THANK_YOU_FILES)

all: character_introduction.json practise_items.json no_report_items.json direct_and_indirect_speech_items.json object_introduction.json thank_you.json

clean:
	rm -f *.{$(FORMATS),json}