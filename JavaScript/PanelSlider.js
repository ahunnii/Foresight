function PanelSlider() {
	// Array to store frames
	this.frameList = new Array();
	
	// Current frame index
	this.frameIndex = 0;
	
	this.isAnimating = false;
	
	this.slider = null;
	
	// Cached margin-left value for slider
	this.margin = 0;
	
	this.targetMargin = 0;
	
	// Millis between animation updates
	this.animRate = 15;
}

PanelSlider.prototype.nextFrame = function() {
	if (!this.isAnimating && this.frameIndex < (this.frameList.length - 1)) {
	
		var func = this.frameList[this.frameIndex].onExit;
		if (func == null || window[func]()) {
			
			func = this.frameList[++this.frameIndex].onLoad;
			if (func)
				window[func]();
				
			this.isAnimating = true;
			this.targetMargin = this.margin - this.frameList[this.frameIndex].elem.outerWidth();
			this.slideLeft();
			return true;
		}
	}
	
	return false;
}

PanelSlider.prototype.previousFrame = function() {
	if (!this.isAnimating && this.frameIndex > 0) { 
	
		var func = this.frameList[this.frameIndex].onExit;
		if (func == null || window[func]()) {
			
			func = this.frameList[--this.frameIndex].onLoad;
			if (func)
				window[func]();
				
			this.isAnimating = true;
			this.targetMargin = this.margin + this.frameList[this.frameIndex].elem.outerWidth();
			this.slideRight();
			return true;
		}
	}
	
	return false;
}

PanelSlider.prototype.slideLeft = function() {
	var delta = this.margin - this.targetMargin;
	if (delta > 20)
		delta = 20;
		
	this.margin -= delta; 
	
	this.slider.style.marginLeft = this.margin + 'px';
	
	if (this.margin == this.targetMargin) {
		this.isAnimating = false;
	} else {
		var self = this;
		setTimeout(function() { self.slideLeft(); }, this.animRate);
	}
}

PanelSlider.prototype.slideRight = function () {
	var delta = this.targetMargin - this.margin;
	if (delta > 20)
		delta = 20;
		
	this.margin += delta;
	
	this.slider.style.marginLeft = this.margin + 'px';
	
	if (this.margin == this.targetMargin) {
		this.isAnimating = false;
	} else {
		var self = this;
		setTimeout(function() { self.slideRight(); }, this.animRate);
	}
}

PanelSlider.prototype.toStart = function() {
	if (!this.isAnimating && this.frameIndex != 0) {
		this.isAnimating = true;
		this.frameIndex = 0;
		this.targetMargin = 0;
		this.slideRight();
		return true;
	} else {
		return false;
	}
}

PanelSlider.prototype.hasNext = function() {
	return this.frameIndex < (frameList.length - 1);
}

PanelSlider.prototype.addFrame = function(frame) {
	this.frameList[this.frameList.length] = frame;
	
	window[frame.onInit](this);
}

PanelSlider.prototype.initialize = function(div) {
	var self = this;
	$(div).children('div').each(function(index, element) {
		var frame = new Frame($(this));
		
		var hold;
		if ((hold = element.getAttribute('onFrameInit')) != null) {
			frame.onInit = hold;
		}
		if ((hold = element.getAttribute('onFrameLoad')) != null) {
			frame.onLoad = hold;
		}
		if ((hold = element.getAttribute('onFrameExit')) != null) {
			frame.onExit = hold;
		}
		
		self.addFrame(frame);
	});
	
	this.slider = div;
	
	if (this.frameList.length > 0 && this.frameList[0].onLoad)
		window[this.frameList[0].onLoad]();
}

function Frame(elem) {
	this.elem = elem;
	this.onInit = null;
	this.onLoad = null;
	this.onExit = null;
}