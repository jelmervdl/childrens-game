function sequence(steps)
{
	var isCancelled = false;

	var play = function(step)
	{
		isCancelled = false;

		if (!step)
			step = 0;

		steps[step](function() {
			if (!isCancelled && step + 1 < steps.length)
				play(step + 1);
		});
	};

	var cancel = function()
	{
		isCancelled = true;
	};

	return {
		play: play,
		cancel: cancel
	};
}