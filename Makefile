AUDIO_SPRITES_DIR = assets/audio/sprites

GAME_FILES = sprites.js configuration.js index.html game.css lib/sequence.js \
	$(AUDIO_SPRITES_DIR)/*.m4a $(AUDIO_SPRITES_DIR)/*.ogg \
	assets/*.svg assets/characters/*.svg assets/objects/*.svg

.PHONY: all

sprites.js: $(AUDIO_SPRITES_DIR)/*.json
	./concat-json.php $(AUDIO_SPRITES_DIR)/*.json > ./sprites.js

game.zip: $(GAME_FILES)
	zip ./game.zip $(GAME_FILES)

all:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) all

clean:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) clean
	rm -f ./sprites.js