// Timer Class

// Static Constants
Timer.STATE_RUNNING = 1;
Timer.STATE_STOPPED = 2;

// Constructor
function Timer() {
	this.state = Timer.STATE_STOPPED;
}

// Member Functions

Timer.prototype.start = function(interval, len, oncomplete) {
	if (this.state === Timer.STATE_STOPPED) {
		this.state = Timer.STATE_RUNNING;
		
		// Set param values, applying defaults where neccessary
		this.interval 	= typeof interval === 'undefined' ? 100 : interval;
		this.len 		= typeof len === 'undefined' ? 0 : len;
		this.oncomplete = typeof oncomplete === 'undefined' ? null : oncomplete;
		
		this.elapsed = 0;
		this.begin = new Date().getTime();
		
		var self = this;
		this._id = setInterval(
			function() { // Preserve 'this' context in Timer.tick function
				self.tick();
			}, 
			interval
		);
	} else {
		throw 'IllegalStateException: Timer cannot be started in current state';
	}
};

Timer.prototype.stop = function() {
	if (this.state === Timer.STATE_RUNNING) {
		this.state = Timer.STATE_STOPPED;
		clearInterval(this._id);
	} else {
		throw 'IllegalStateException: Timer cannot be stopped in current state';
	}
};

// Called by setInterval()
Timer.prototype.tick = function() {
	var time = new Date().getTime();
	this.elapsed += time - (this.begin + this.elapsed);
	
	if (this.len != 0 && this.elapsed >= this.len) {
		this.stop();
		if (this.oncomplete != null) {
			this.oncomplete(this);
		}
	}
};

Timer.prototype.isRunning = function() {
	return this.state === Timer.STATE_RUNNING;
}