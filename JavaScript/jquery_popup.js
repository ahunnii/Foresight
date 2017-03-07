(function( $ ) {

var methods = {
'init'		: init,
'hide'		: hide,
'show'		: show,
'destroy'	: destroy,
'disable'	: disable,
'enable'	: enable,
'reposition': reposition,
};

// Attach to jQuery
$.fn.popup = function(method) {
    // Method calling logic
    if ( methods[method] ) {
		return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || !method ) {
		return methods.init.apply( this, arguments );
    } else {
		$.error( 'Method ' +  method + ' does not exist on jQuery.popup' );
    }
};

function init(options) {

	var settings = $.extend({
		'boxclass'		: 'PopupBox', 	// css class for popup div
		'element'		: null,			// jquery or dom element to place in popup div
		'helper'		: null,			// function to create jquery or dom element to place in popup div
		'recreate'		: false,		// false to cache element created by helper, true to recreate on show
		'showdelay'		: 0,			// time before showing on hover (ms)
		'hidedelay'		: 400,			// time before hiding on leave (ms)
		'tickinterval' 	: 100,			// show / hide timer tick interval (ms)
		'showoptions'	: {				// options to pass to jQuery.show( options )
			'duration' : 0,
		},
		'hideoptions'	: {				// options to pass to jQuery.hide( options )
			'duration' : 0,
		},
		'appendto'		: 'body',		// selector or element to append popup to
		'originx'		: 'left', 		// one of { 'left', 'right', 'center' }
		'originy'		: 'bottom',		// one of { 'top', 'bottom', 'center' }
		'hoverpopup'	: true,			// remain visible if cursor is over popup box
		'displayfunc'	: 'default',	// { 'fade', 'default' } fadeIn() / fadeOut() & show() / hide() respectively
	}, 
	options
	);
	
	settings.hideoptions.always = function() {
		$(this).empty();
	};
	
	if (!settings.element && !settings.helper) { // create placeholder element
		settings.element = $('<p>jQuery.popup</p>');
	}
	
	this.each(function() {
		var $this = $(this);
		var data = $this.data('popup');
		
		if (!data) {
			data = { 
			'settings'	: settings,
			'enabled'	: true,
			'visible'	: false,
			'timer'		: new Timer(),
			'attached'	: $this,
			};
			
			var boxElement = $('<div/>');
			boxElement.addClass(settings.boxclass);
			boxElement.hide();
			
			if (settings.hoverpopup) {
				boxElement.bind('mouseenter.popup', onEnter);
				boxElement.bind('mouseleave.popup', onLeave);
				boxElement.data('popup', data);
			}
			
			data.boxElement = boxElement;
			
			$this.data('popup', data);
			
			$this.bind('mouseenter.popup', onEnter);
			$this.bind('mouseleave.popup', onLeave);
			
			$(settings.appendto).append(data.boxElement);
		}
	});
	
	return this;
}

function hide() {
	return this.each(function() {
		var $this = $(this);
		var data = $this.data('popup');
		if (data.visible) {
			//data.boxElement.empty();
			data.visible = false;
			
			switch (data.settings.displayfunc) {
				case 'fade':
					data.boxElement.fadeOut(data.settings.hideoptions);
					break;
				case 'default':
					data.boxElement.hide(data.settings.hideoptions);
					break;
			}
			//console.log('hide()');
		}
	});
}

function show(x, y) {
	return this.each(function() {
		var $this = $(this);
		var data = $this.data('popup');
		
		if (!data.visible) {
			var element;
			
			if (data.element) { // use cached element
				
				element = data.element;
				
			} else if (data.settings.element) { // use settings element
			
				element = data.element = data.settings.element;
				
			} else { // call helper function to create element
			
				element = data.settings.helper.apply(this);
				
				if (!data.settings.recreate) { // cache created element
					data.element = element;
				}
				
			}
				
			data.boxElement.append(data.element);
			
			if (typeof x !== 'undefined' && typeof y !== 'undefined') {
				var offset = $(data.settings.appendto).offset();
				//console.log('mouse coords: ( ' + e.pageX + ', ' + e.pageY + ' )');
				//console.log('offset: ( ' + offset.left + ', ' + offset.top + ' )');
				reposition.apply($this, [x - offset.left, y - offset.top]);
			}
			
			data.visible = true;
			
			switch (data.settings.displayfunc) {
				case 'fade':
					data.boxElement.fadeIn(data.settings.showoptions);
					break;
				case 'default':
					data.boxElement.show(data.settings.showoptions);
					break;
			}
		}
	});
}

// Removes popup box element from dom & unbinds events
function destroy() {
	return this.each(function() {
		var $this = $(this);
		var data = $this.data('popup');
		data.boxElement.remove();
		$this.unbind('mouseenter.popup');
		$this.unbind('mouseleave.popup');
		$this.removeData('popup');
	});
}

// Ignore hover events
function disable() {
	return this.each(function() {
		var data = $(this).data('popup');
		data.enabled = false;
		if (data.visible) {
			data.visible = false;
			hide.apply(data.attached);
		}
	});
}

// Activate on hover events
function enable() {
	return this.each(function() {
	
		var data = $(this).data('popup');
		data.enabled = true;
		
		if (data.hovered && !data.visible) {
			data.visible = true;
			show.apply(data.attached);
		}
		
	});
}

// Sets the popup box position within its parent ( options.appendto )
function reposition(x, y) {
	return this.each(function() {
	
		var $this = $(this);
		var data = $this.data('popup');
		
		switch (data.settings.originx) {
			case 'right':
				x -= data.boxElement.outerWidth();
				break;
			case 'center':
				x -= data.boxElement.outerWidth() / 2;
				break;
		}
		
		switch (data.settings.originy) {
			case 'bottom':
				y -= data.boxElement.outerHeight();
				break;
			case 'center':
				y -= data.boxElement.outerHeight() / 2;
				break;
		}
		
		data.boxElement.css({
			'left'	: x + 'px',
			'top'	: y + 'px',
		});
		
		//console.log('popup coords: ( ' + x + ', ' + y + ' )');
	});
}

// Called when cursor enters the element
function onEnter(e) {
	//console.log('onEnter() From: ' + e.relatedTarget + ' To: ' + this);
	
	var $this = $(this);
	var data = $this.data('popup');
	
	if (data.enabled && !data.hovered) {
		data.hovered = true;
	
		var timer = data.timer;
		if (timer.isRunning()) // cancel hide timer
			timer.stop();
		
		if (!data.visible) {
			if (data.settings.showdelay > 0) {
				timer.start(
					data.settings.tickinterval, 
					data.settings.showdelay, 
					function() { 
						//console.log('Show timer complete');
						show.apply(data.attached, [e.pageX, e.pageY]);
					}
				);
			} else {
				show.apply(data.attached, [e.pageX, e.pageY]);
			}
		}
		
	}
}

// Called when cursor leaves the element
function onLeave(e) {
	//console.log('onLeave() From: ' + this + ' To: ' + e.relatedTarget);
	
	var $this = $(this);
	var data = $this.data('popup');
	
	if (e.relatedTarget !== data.attached[0] && 
		(!data.settings.hoverpopup || e.relatedTarget !== data.boxElement[0])) {
		
		//console.log('Leave Prep');
		
		data.hovered = false;
		
		if (data.enabled && data.visible) {
			
			//console.log('Leave Setup');
			
			var timer = data.timer;
			if (timer.isRunning()) {
				timer.stop();
			}
			
			if (data.settings.hidedelay > 0) {
				timer.start(
					data.settings.tickinterval, 
					data.settings.hidedelay,
					function() {
						//console.log('Hide timer complete');
						hide.apply(data.attached);
					}
				);
			} else {
				hide.apply(data.attached);
			}
		}
	}
}

})( jQuery );