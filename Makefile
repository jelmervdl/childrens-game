AUDIO_SPRITES_DIR = assets/audio/sprites

.PHONY: all

sprites.js: $(AUDIO_SPRITES_DIR)/*.json
	./concat-json.php $(AUDIO_SPRITES_DIR)/*.json > ./sprites.js

all:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) all

clean:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) clean
	rm -f ./sprites.js