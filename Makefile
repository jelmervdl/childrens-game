AUDIO_SPRITES_DIR = assets/audio/sprites

.PHONY: all

all:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) all

clean:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) clean