function Sequence(steps)
{
	this.isCancelled = null;

	this.steps = steps || [];
}

Sequence.prototype.play = function(callback)
{
	this.playStep(0, callback);
};

Sequence.prototype.playStep = function(step, callback)
{
	var self = this;
	this.isCancelled = false;

	this.steps[step](function() {
		if (!self.isCancelled && step + 1 < self.steps.length)
			self.playStep(step + 1, callback);
		else if (callback)
			callback(self);
	});
};

Sequence.prototype.cancel = function()
{
	this.isCancelled = true;
};
