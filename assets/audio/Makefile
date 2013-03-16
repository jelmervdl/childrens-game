CHARACTER_INTRO_FILES = instructions/m3.wav \
	instructions/intro.aap.wav \
	instructions/intro.hond.wav \
	instructions/intro.olif.wav \
	instructions/m4.wav \
	instructions/m4n.wav \
	instructions/m5.wav \
	instructions/m5n.wav \
	instructions/m6.wav \
	instructions/m6n.wav

sprites/character-introduction.m4a: $(CHARACTER_INTRO_FILES)
	audiosprite --output sprites/character-introduction --export m4a $(CHARACTER_INTRO_FILES)

all: sprites/character-introduction.m4a

clean:
	rm -f sprites/character-introduction.{m4a,json}