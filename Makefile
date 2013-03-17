AUDIO_SPRITES_DIR = assets/audio/sprites

.PHONY: audio_sprites

audio_sprites:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) all

clean:
	$(MAKE) -C $(AUDIO_SPRITES_DIR) clean