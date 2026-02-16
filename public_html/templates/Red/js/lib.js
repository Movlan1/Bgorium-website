/*!
 * Bootstrap v3.4.1 (https://getbootstrap.com/)
 * Copyright 2011-2023 Twitter, Inc.
 * Licensed under the MIT license
 */

if("undefined"==typeof jQuery)throw new Error("Bootstrap's JavaScript requires jQuery");+function(t){"use strict";var e=t.fn.jquery.split(" ")[0].split(".");if(e[0]<2&&e[1]<9||1==e[0]&&9==e[1]&&e[2]<1||e[0]>4)throw new Error("Bootstrap's JavaScript requires jQuery version 1.9.1 or higher, but lower than version 5s")}(jQuery),+function(t){"use strict";function e(e){var i=e.attr("data-target");i||(i=e.attr("href"),i=i&&/#[A-Za-z]/.test(i)&&i.replace(/.*(?=#[^\s]*$)/,""));var n="#"!==i?t(document).find(i):null;return n&&n.length?n:e.parent()}function i(i){i&&3===i.which||(t(a).remove(),t(s).each(function(){var n=t(this),a=e(n),s={relatedTarget:this};a.hasClass("open")&&(i&&"click"==i.type&&/input|textarea/i.test(i.target.tagName)&&t.contains(a[0],i.target)||(a.trigger(i=t.Event("hide.bs.dropdown",s)),i.isDefaultPrevented()||(n.attr("aria-expanded","false"),a.removeClass("open").trigger(t.Event("hidden.bs.dropdown",s)))))}))}function n(e){return this.each(function(){var i=t(this),n=i.data("bs.dropdown");n||i.data("bs.dropdown",n=new r(this)),"string"==typeof e&&n[e].call(i)})}var a=".dropdown-backdrop",s='[data-toggle="dropdown"]',r=function(e){t(e).on("click.bs.dropdown",this.toggle)};r.VERSION="3.4.1",r.prototype.toggle=function(n){var a=t(this);if(!a.is(".disabled, :disabled")){var s=e(a),r=s.hasClass("open");if(i(),!r){"ontouchstart"in document.documentElement&&!s.closest(".navbar-nav").length&&t(document.createElement("div")).addClass("dropdown-backdrop").insertAfter(t(this)).on("click",i);var o={relatedTarget:this};if(s.trigger(n=t.Event("show.bs.dropdown",o)),n.isDefaultPrevented())return;a.trigger("focus").attr("aria-expanded","true"),s.toggleClass("open").trigger(t.Event("shown.bs.dropdown",o))}return!1}},r.prototype.keydown=function(i){if(/(38|40|27|32)/.test(i.which)&&!/input|textarea/i.test(i.target.tagName)){var n=t(this);if(i.preventDefault(),i.stopPropagation(),!n.is(".disabled, :disabled")){var a=e(n),r=a.hasClass("open");if(!r&&27!=i.which||r&&27==i.which)return 27==i.which&&a.find(s).trigger("focus"),n.trigger("click");var o=" li:not(.disabled):visible a",l=a.find(".dropdown-menu"+o);if(l.length){var d=l.index(i.target);38==i.which&&d>0&&d--,40==i.which&&d<l.length-1&&d++,~d||(d=0),l.eq(d).trigger("focus")}}}};var o=t.fn.dropdown;t.fn.dropdown=n,t.fn.dropdown.Constructor=r,t.fn.dropdown.noConflict=function(){return t.fn.dropdown=o,this},t(document).on("click.bs.dropdown.data-api",i).on("click.bs.dropdown.data-api",".dropdown form",function(t){t.stopPropagation()}).on("click.bs.dropdown.data-api",s,r.prototype.toggle).on("keydown.bs.dropdown.data-api",s,r.prototype.keydown).on("keydown.bs.dropdown.data-api",".dropdown-menu",r.prototype.keydown)}(jQuery),+function(t){"use strict";function e(e){return this.each(function(){var n=t(this),a=n.data("bs.tab");a||n.data("bs.tab",a=new i(this)),"string"==typeof e&&a[e]()})}var i=function(e){this.element=t(e)};i.VERSION="3.4.1",i.TRANSITION_DURATION=150,i.prototype.show=function(){var e=this.element,i=e.closest("ul:not(.dropdown-menu)"),n=e.data("target");if(n||(n=e.attr("href"),n=n&&n.replace(/.*(?=#[^\s]*$)/,"")),!e.parent("li").hasClass("active")){var a=i.find(".active:last a"),s=t.Event("hide.bs.tab",{relatedTarget:e[0]}),r=t.Event("show.bs.tab",{relatedTarget:a[0]});if(a.trigger(s),e.trigger(r),!r.isDefaultPrevented()&&!s.isDefaultPrevented()){var o=t(document).find(n);this.activate(e.closest("li"),i),this.activate(o,o.parent(),function(){a.trigger({type:"hidden.bs.tab",relatedTarget:e[0]}),e.trigger({type:"shown.bs.tab",relatedTarget:a[0]})})}}},i.prototype.activate=function(e,n,a){function s(){r.removeClass("active").find("> .dropdown-menu > .active").removeClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded",!1),e.addClass("active").find('[data-toggle="tab"]').attr("aria-expanded",!0),o?(e[0].offsetWidth,e.addClass("in")):e.removeClass("fade"),e.parent(".dropdown-menu").length&&e.closest("li.dropdown").addClass("active").end().find('[data-toggle="tab"]').attr("aria-expanded",!0),a&&a()}var r=n.find("> .active"),o=a&&t.support.transition&&(r.length&&r.hasClass("fade")||!!n.find("> .fade").length);r.length&&o?r.one("bsTransitionEnd",s).emulateTransitionEnd(i.TRANSITION_DURATION):s(),r.removeClass("in")};var n=t.fn.tab;t.fn.tab=e,t.fn.tab.Constructor=i,t.fn.tab.noConflict=function(){return t.fn.tab=n,this};var a=function(i){i.preventDefault(),e.call(t(this),"show")};t(document).on("click.bs.tab.data-api",'[data-toggle="tab"]',a).on("click.bs.tab.data-api",'[data-toggle="pill"]',a)}(jQuery),+function(t){"use strict";function e(e){var i,n=e.attr("data-target")||(i=e.attr("href"))&&i.replace(/.*(?=#[^\s]+$)/,"");return t(document).find(n)}function i(e){return this.each(function(){var i=t(this),a=i.data("bs.collapse"),s=t.extend({},n.DEFAULTS,i.data(),"object"==typeof e&&e);!a&&s.toggle&&/show|hide/.test(e)&&(s.toggle=!1),a||i.data("bs.collapse",a=new n(this,s)),"string"==typeof e&&a[e]()})}var n=function(e,i){this.$element=t(e),this.options=t.extend({},n.DEFAULTS,i),this.$trigger=t('[data-toggle="collapse"][href="#'+e.id+'"],[data-toggle="collapse"][data-target="#'+e.id+'"]'),this.transitioning=null,this.options.parent?this.$parent=this.getParent():this.addAriaAndCollapsedClass(this.$element,this.$trigger),this.options.toggle&&this.toggle()};n.VERSION="3.4.1",n.TRANSITION_DURATION=350,n.DEFAULTS={toggle:!0},n.prototype.dimension=function(){var t=this.$element.hasClass("width");return t?"width":"height"},n.prototype.show=function(){if(!this.transitioning&&!this.$element.hasClass("in")){var e,a=this.$parent&&this.$parent.children(".panel").children(".in, .collapsing");if(!(a&&a.length&&(e=a.data("bs.collapse"),e&&e.transitioning))){var s=t.Event("show.bs.collapse");if(this.$element.trigger(s),!s.isDefaultPrevented()){a&&a.length&&(i.call(a,"hide"),e||a.data("bs.collapse",null));var r=this.dimension();this.$element.removeClass("collapse").addClass("collapsing")[r](0).attr("aria-expanded",!0),this.$trigger.removeClass("collapsed").attr("aria-expanded",!0),this.transitioning=1;var o=function(){this.$element.removeClass("collapsing").addClass("collapse in")[r](""),this.transitioning=0,this.$element.trigger("shown.bs.collapse")};if(!t.support.transition)return o.call(this);var l=t.camelCase(["scroll",r].join("-"));this.$element.one("bsTransitionEnd",t.proxy(o,this)).emulateTransitionEnd(n.TRANSITION_DURATION)[r](this.$element[0][l])}}}},n.prototype.hide=function(){if(!this.transitioning&&this.$element.hasClass("in")){var e=t.Event("hide.bs.collapse");if(this.$element.trigger(e),!e.isDefaultPrevented()){var i=this.dimension();this.$element[i](this.$element[i]())[0].offsetHeight,this.$element.addClass("collapsing").removeClass("collapse in").attr("aria-expanded",!1),this.$trigger.addClass("collapsed").attr("aria-expanded",!1),this.transitioning=1;var a=function(){this.transitioning=0,this.$element.removeClass("collapsing").addClass("collapse").trigger("hidden.bs.collapse")};return t.support.transition?void this.$element[i](0).one("bsTransitionEnd",t.proxy(a,this)).emulateTransitionEnd(n.TRANSITION_DURATION):a.call(this)}}},n.prototype.toggle=function(){this[this.$element.hasClass("in")?"hide":"show"]()},n.prototype.getParent=function(){return t(document).find(this.options.parent).find('[data-toggle="collapse"][data-parent="'+this.options.parent+'"]').each(t.proxy(function(i,n){var a=t(n);this.addAriaAndCollapsedClass(e(a),a)},this)).end()},n.prototype.addAriaAndCollapsedClass=function(t,e){var i=t.hasClass("in");t.attr("aria-expanded",i),e.toggleClass("collapsed",!i).attr("aria-expanded",i)};var a=t.fn.collapse;t.fn.collapse=i,t.fn.collapse.Constructor=n,t.fn.collapse.noConflict=function(){return t.fn.collapse=a,this},t(document).on("click.bs.collapse.data-api",'[data-toggle="collapse"]',function(n){var a=t(this);a.attr("data-target")||n.preventDefault();var s=e(a),r=s.data("bs.collapse"),o=r?"toggle":a.data();i.call(s,o)})}(jQuery),+function(t){"use strict";function e(){var t=document.createElement("bootstrap"),e={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd otransitionend",transition:"transitionend"};for(var i in e)if(void 0!==t.style[i])return{end:e[i]};return!1}t.fn.emulateTransitionEnd=function(e){var i=!1,n=this;t(this).one("bsTransitionEnd",function(){i=!0});var a=function(){i||t(n).trigger(t.support.transition.end)};return setTimeout(a,e),this},t(function(){t.support.transition=e(),t.support.transition&&(t.event.special.bsTransitionEnd={bindType:t.support.transition.end,delegateType:t.support.transition.end,handle:function(e){return t(e.target).is(this)?e.handleObj.handler.apply(this,arguments):void 0}})})}(jQuery);


$(function() {
	
	$('.dropdown-form').click(function(e) {
        e.stopPropagation();
    });

	var classes=[],
		a=$(".h_btn").click(function(e){
			e.preventDefault();
			a.not(this).toggleClass("open",false);

			$("html").removeClass(classes);

			if( $(this).toggleClass("open").hasClass("open") )
				$("html").addClass( $(this).prop("id")+"_open" );
		});

	a.each(function(){
		classes.push( $(this).attr("id")+"_open" );
	});

	classes=classes.join(" ");
	
	$('#closemenu').on('click', function(){
		$('html').removeClass('mainmenu_open');
		$('#mainmenu').removeClass('open');
		return false;
	});

	$('.soc_links a').on('click',function(){
	   var href = $(this).attr('href');
       var width  = 820;
       var height = 420;
       var left   = (screen.width  - width)/2;
       var top   = (screen.height - height)/2-100;   

       var auth_window = window.open(href, 'auth_window', "width="+width+",height="+height+",top="+top+",left="+left+"menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no");
       return false;
	});

});

function ShowCommentsUploader() {

	if ($("#hidden-image-uploader").css("display") == "none") { 

		$("#hidden-image-uploader").show('blind',{}, 250, function(){
			$('#comments-image-uploader').plupload('refresh');
		});

	} else {

		$("#hidden-image-uploader").hide('blind',{}, 250 );

	}

	return false;
};