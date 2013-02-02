function draggable(element, targets, callback)
{
	var state = null;

	var pos = {
		bx: 0, //element.offsetLeft,
		by: 0, //element.offsetTop,
		cx: 0,
		cy: 0
	};

	var moveBy = function(x, y)
	{
		pos.cx = pos.bx + x;
		pos.cy = pos.by + y;

		element.style.webkitTransform = "translate(" + (pos.cx) + "px," + (pos.cy) + "px)";
	};

	var getOverlapWith = function(target)
	{
		var tx1 = target.offsetLeft,
			ty1 = target.offsetTop,
			tx2 = tx1 + target.width,
			ty2 = ty1 + target.height,
			ex1 = element.offsetLeft + pos.cx,
			ey1 = element.offsetTop + pos.cy,
			ex2 = ex1 + element.width,
			ey2 = ey1 + element.height;

		var ox = Math.min(ex2 - tx1, tx2 - ex1),
			oy = Math.min(ey2 - ty1, ty2 - ey1);

		return ox > 0 && oy > 0 ? ox * oy : 0;
	};

	var testForHit = function()
	{
		var top = {
			target: null,
			overlap: 0
		};

		for (var i = 0; i < targets.length; ++i)
		{
			var overlap = getOverlapWith(targets[i]);
			
			if (overlap > top.overlap)
				top = {
					target: targets[i],
					overlap: overlap
				};
		}

		return top.target;

		
	};

	var onPickedUp = function()
	{
		// Update dragged element state
		element.classList.add('picked-up');
	};

	var onMoved = function()
	{
		// Update drop targets state
		var top = testForHit();

		for (var i = 0; i < targets.length; ++i)
		{
			if (targets[i] == top)
				targets[i].classList.add('accepting-draggable');
			else
				targets[i].classList.remove('accepting-draggable');
		}
	};

	var onDropped = function()
	{
		// Update dragged element state
		element.classList.remove('picked-up');

		// See wether we dropped it on one of our targets
		var top = testForHit();

		// If so, remove the class name (clean-up ;) ) and
		// call the callback.
		if (top)
		{
			top.classList.remove('accepting-draggable');

			if (callback)
				callback(element, top);
		}
		// Not dropped on a real target, move back to base.
		else
		{
			moveBy(-pos.bx, -pos.by);
			reset();
		}
	};

	var touchStart = function(e) {
		e.preventDefault();

		state = {
			tx: e.changedTouches[0].pageX,
			ty: e.changedTouches[0].pageY
		};

		element.addEventListener('touchmove', touchMove, false);
		element.addEventListener('touchend', touchEnd, false);

		onPickedUp();
	};

	var touchMove = function(e) {
		e.preventDefault();

		moveBy(
			e.changedTouches[0].pageX - state.tx,
			e.changedTouches[0].pageY - state.ty);

		onMoved();
	};

	var touchEnd = function(e) {
		e.preventDefault();

		state = null;
		pos.bx = pos.cx;
		pos.by = pos.cy;

		element.removeEventListener('touchmove', touchMove);
		element.removeEventListener('touchend', touchEnd);

		onDropped();
	};

	var mouseDown = function(e) {
		e.preventDefault();

		state = {
			tx: e.pageX,
			ty: e.pageY
		};

		window.addEventListener('mousemove', mouseMove, false);
		window.addEventListener('mouseup', mouseUp, false);

		onPickedUp();
	};

	var mouseMove = function(e) {
		e.preventDefault();

		moveBy(
			e.pageX - state.tx,
			e.pageY - state.ty);

		onMoved();
	};

	var mouseUp = function(e) {
		e.preventDefault();

		state = null;

		pos.bx = pos.cx;
		pos.by = pos.cy;

		window.removeEventListener('mousemove', mouseMove);
		window.removeEventListener('mouseup', mouseUp);

		onDropped();
	};

	// Reset draggable state
	var reset = function() {
		state = null;
		pos.bx = 0;
		pos.by = 0;
		pos.cx = 0;
		pos.cy = 0;
		element.style.webkitTransform = '';
	};

	// Create a draggable
	var create = function() {
		// Reset state
		reset();
		
		// Listen for fingers and cursors
		element.addEventListener('touchstart', touchStart, false);
		element.addEventListener('mousedown', mouseDown, false);

		// Add API to element
		element.reset = reset;
		element.destroy = destroy;
	};

	// Destroy a draggable
	var destroy = function() {
		// Stop listening for fingers that touch the element
		element.removeEventListener('touchstart', touchStart);
		element.removeEventListener('mousedown', mouseDown);

		// Delete API from element
		delete element.reset;
		delete element.destroy;
	};

	create();
}