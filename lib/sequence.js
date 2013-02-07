function Sequence(steps)
{
	this.isCancelled = null;

	this.steps = steps || [];

	if (!Array.isArray(this.steps))
		throw new Error("argument should be an array");
}

Sequence.prototype.play = function(callback)
{
	this.isCancelled = false;
	this.playStep(0, callback || function() {});
};

Sequence.prototype.playStep = function(step, callback)
{
	var self = this;

	// Have we been cancelled?
	if (this.isCancelled)
		callback(self);

	// Are we finished then?
	else if (step == this.steps.length)
		callback(self);

	// Nope, go back to work.
	else
		this.steps[step](function() {
			self.playStep(step + 1, callback);
		});
};

Sequence.prototype.cancel = function()
{
	this.isCancelled = true;
};
