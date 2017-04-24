/**
 * @component AwoCoupon Pro
 * @copyright Copyright (C) Seyi Awofadeju - All rights reserved.
 * @Website : http://awodev.com
 **/

function ScrollHeader(content, scrollHorizontal, scrollVertical, width, height) {

	this.divContent = null;
	this.divHeaderRow = null;
	this.divHeaderColumn = null;
	this.divHeaderRowColumn = null;
	this.headerRowFirstColumn = null;
	this.x;
	this.y;
	this.horizontal = false;
	this.vertical = false;
	this.childElement = 0;
	this.width = 900;
	this.height = 400;
	this.browser = null;

	// Copy table to top and to left
	ScrollHeader.prototype.init = function(content, scrollHorizontal, scrollVertical, width, height) {
/*this.divContent.childNodes[0].childNodes[0].childNodes[0].childNodes[0].style.background = 'blue';			
this.divContent.childNodes[0].childNodes[1].childNodes[0].childNodes[0].style.background = 'blue';			
this.divHeaderRow.childNodes[0].childNodes[0].childNodes[0].childNodes[1].style.background = 'blue';
this.divHeaderRowColumn.childNodes[0].childNodes[0].childNodes[0].childNodes[0].style.background = 'blue';*/
		if(width != undefined) this.width = width;
		if(height != undefined) this.height = height;
		this.horizontal = scrollHorizontal;
		this.vertical = scrollVertical;
		
		//this.browser = navigator.userAgent.toLowerCase().indexOf("msie") != -1 ? 'ie' : 'fx';


		if (content != null) {
			this.divContent = content;
			if (this.divContent.childNodes[this.childElement].tagName == null) {
				this.childElement = 1;
			}

			var headerRow = this.divContent.childNodes[this.childElement].childNodes[this.childElement];
			this.x = this.divContent.childNodes[this.childElement].offsetWidth;
			this.y = this.divContent.childNodes[this.childElement].offsetHeight; 

			this.divHeaderRow = this.divContent.cloneNode(true); 
			if (this.horizontal) {
				this.divHeaderRow.style.height = headerRow.offsetHeight + 'px';
				this.divHeaderRow.style.overflow = "hidden";

				this.divContent.parentNode.insertBefore(this.divHeaderRow, this.divContent);
				this.divContent.childNodes[this.childElement].style.position = "absolute";
				this.divContent.childNodes[this.childElement].style.top = "-" + headerRow.offsetHeight + 'px';
				this.divContent.childNodes[this.childElement].style.left = 0; // added for ie7 fix

				this.y = this.y - headerRow.offsetHeight;
			}

			this.divHeaderRowColumn = this.divHeaderRow.cloneNode(true); 
			this.headerRowFirstColumn = headerRow.childNodes[this.childElement].childNodes[this.childElement];
			//this.headerRowFirstColumn = headerRow.childNodes[this.childElement].childNodes[0];
			this.divHeaderColumn = this.divContent.cloneNode(true);
			this.divContent.style.position = "relative";
			

			if (this.vertical) {
/*affects after count*/
				//this.divContent.style.position = 'absolute'; // added for ie7 fix
				this.divContent.style.left = this.headerRowFirstColumn.offsetWidth + 'px';
	
				this.divContent.parentNode.insertBefore(this.divHeaderColumn, this.divContent);
				this.divContent.childNodes[this.childElement].style.position = "absolute";
				this.divContent.childNodes[this.childElement].style.left = "-" + this.headerRowFirstColumn.offsetWidth + 'px';
			} else {
				this.divContent.style.left = "0";
			}

			if (this.vertical) {
				this.divHeaderColumn.style.width = this.headerRowFirstColumn.offsetWidth + 'px';
				this.divHeaderColumn.style.overflow = "hidden";
				this.divHeaderColumn.style.zIndex = "99";

				this.divHeaderColumn.style.position = "absolute";
				this.divHeaderColumn.style.left = 0;
				this.addScrollSynchronization(this.divHeaderColumn, this.divContent, "vertical");
				this.x = this.x - this.headerRowFirstColumn.offsetWidth;
			}
			if (this.horizontal) {
				if (this.vertical) {
					this.divContent.parentNode.insertBefore(this.divHeaderRowColumn, this.divContent);
				}
				this.divHeaderRowColumn.style.position = "absolute";
				this.divHeaderRowColumn.style.left = 0;
				this.divHeaderRowColumn.style.top = 0;
				this.divHeaderRowColumn.style.width = this.headerRowFirstColumn.offsetWidth + 'px';
				this.divHeaderRowColumn.overflow = "hidden";
				this.divHeaderRowColumn.style.zIndex = "100";
				this.divHeaderRowColumn.style.backgroundColor = "#ffffff";

			}

			if (this.horizontal) {
				this.addScrollSynchronization(this.divHeaderRow, this.divContent, "horizontal");
			}

			if (this.horizontal || this.vertical) {
			//window.onresize = ResizeScrollArea;
				this.ResizeScrollArea();
			}
		}
	}


	// Resize scroll area to window size.
	ScrollHeader.prototype.ResizeScrollArea = function(e) {
		//var height = document.documentElement.clientHeight - 120;
		var height = document.body.clientHeight - 120;
		if (!this.vertical) {
			height -= this.divHeaderRow.offsetHeight;
		}
		//var width = document.documentElement.clientWidth - 50;
		var width = document.body.clientWidth - 50;
		if (!this.horizontal) {
			width -= this.divHeaderColumn.offsetWidth;
		}
		var headerRowsWidth = 0;
		this.divContent.childNodes[this.childElement].style.width = this.x + 'px';
		this.divContent.childNodes[this.childElement].style.height = this.y + 'px';

		if (this.divHeaderRowColumn != null) {
			headerRowsWidth = this.divHeaderRowColumn.offsetWidth;
		}

width=this.width;
height=this.height;

		// width
		if (this.divContent.childNodes[this.childElement].offsetWidth > width) {
			this.divContent.style.width = Math.max(width - headerRowsWidth, 0) + 'px';
			this.divContent.style.overflowX = "scroll";
			this.divContent.style.overflowY = "auto";
		} else {
			this.divContent.style.width = this.x + 'px';
			//this.divContent.style.overflowX = "auto";
			this.divContent.style.overflowX = "hidden";
			this.divContent.style.overflowY = "auto";
		}

		if (this.divHeaderRow != null) {
			this.divHeaderRow.style.width = (this.divContent.offsetWidth + headerRowsWidth) + 'px';
		}

		// height
		if (this.divContent.childNodes[this.childElement].offsetHeight > height) {
			this.divContent.style.height = Math.max(height, 80) + 'px';
			this.divContent.style.overflowY = "scroll";
		} else {
			this.divContent.style.height = this.y + 'px';
			this.divContent.style.overflowY = "hidden";
		}
		if (this.divHeaderColumn != null) {
			this.divHeaderColumn.style.height = this.divContent.offsetHeight + 'px';
		}

		// check scrollbars
		if (this.divContent.style.overflowY == "scroll") {
			this.divContent.style.width = (this.divContent.offsetWidth + 17) + 'px';
		}
		if (this.divContent.style.overflowX == "scroll") {
			this.divContent.style.height = (this.divContent.offsetHeight + 17) + 'px';
		}

		this.divContent.parentNode.style.width = this.divContent.offsetWidth + 'px'; // added for ie7 fix
/*deleted ie7 fix*/ //		this.divContent.parentNode.style.width = (this.divContent.offsetWidth + headerRowsWidth-20) + 'px';

	}


	// ********************************************************************************
	// Synchronize div elements when scrolling 
	// from http://webfx.eae.net/dhtml/syncscroll/syncscroll.html
	// ********************************************************************************
	// This is a function that returns a function that is used
	// in the event listener
	ScrollHeader.prototype.getOnScrollFunction = function(oElement, srcElement) {
		return function () {
			if (oElement._scrollSyncDirection == "horizontal" || oElement._scrollSyncDirection == "both") oElement.scrollLeft = srcElement.scrollLeft;
			if (oElement._scrollSyncDirection == "vertical" || oElement._scrollSyncDirection == "both") oElement.scrollTop = srcElement.scrollTop;
		};
	}

	// This function adds scroll syncronization for the fromElement to the toElement
	// this means that the fromElement will be updated when the toElement is scrolled
	ScrollHeader.prototype.addScrollSynchronization = function(fromElement, toElement, direction) {
		this.removeScrollSynchronization(fromElement);

		fromElement._syncScroll = this.getOnScrollFunction(fromElement, toElement);
		fromElement._scrollSyncDirection = direction;
		fromElement._syncTo = toElement;
		if (toElement.addEventListener) {
			toElement.addEventListener("scroll", fromElement._syncScroll, false);
		} else {
			toElement.attachEvent("onscroll", fromElement._syncScroll);
		}
	}

	// removes the scroll synchronization for an element
	ScrollHeader.prototype.removeScrollSynchronization = function(fromElement) {
		if (fromElement._syncTo != null) {
			if (fromElement._syncTo.removeEventListener) {
				fromElement._syncTo.removeEventListener("scroll", fromElement._syncScroll, false);
			} else {
				fromElement._syncTo.detachEvent("onscroll", fromElement._syncScroll);
			}
		}

		fromElement._syncTo = null;
		fromElement._syncScroll = null;
		fromElement._scrollSyncDirection = null;
	}
	this.init(content, scrollHorizontal, scrollVertical, width, height);
}
