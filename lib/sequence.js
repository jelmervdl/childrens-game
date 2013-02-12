function Sequence(steps)
{
	this.isCancelled = null;

	this.isPlaying = false;

	this.step = null;

	this.steps = steps || [];

	this.listeners = [];

	if (!Array.isArray(this.steps))
		throw new Error("argument should be an array");
};

Sequence.prototype.addListener = function(listener)
{
	this.listeners.push(listener);
};

Sequence.prototype.removeListener = function(listener)
{
	var index = this.listeners.indexOf(listener);

	if (index !== -1)
		this.listeners.splice(index, 1);
};

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
		this.listeners.forEach(function(listener) {
			listener.call(self, step);
		});

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
