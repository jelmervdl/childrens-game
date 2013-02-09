function Sequence(steps)
{
	this.isCancelled = null;

	this.isPlaying = false;

	this.step = null;

	this.steps = steps || [];

	if (!Array.isArray(this.steps))
		throw new Error("argument should be an array");
}

Sequence.prototype.play = function(callback)
{
	if (this.isPlaying)
		throw new Error("Sequence is already being played");

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
	else {
		this.isPlaying = true;
		this.step = step;
		this.steps[step](function() {
			self.isPlaying = false;
			self.playStep(step + 1, callback);
		});
	}
};

Sequence.prototype.cancel = function()
{
	this.isCancelled = true;
};
