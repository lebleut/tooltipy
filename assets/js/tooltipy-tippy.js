/**
 * Tippy.js bridge for Tooltipy.
 * Keeps legacy CSS classes (bluet_tooltip, bluet_block_to_show, …) while Tippy handles positioning.
 */
(function (window, $) {
	'use strict';

	var instances = [];

	function mapPlacement(position) {
		switch (position) {
			case 'top':
			case 'bottom':
			case 'left':
			case 'right':
				return position;
			default:
				return 'bottom';
		}
	}

	function getContentForKeyword(id) {
		var $block = $('#tooltip_blocks_to_show').children('[data-tooltip="' + id + '"]').first();
		if (!$block.length) {
			$block = $('#tooltip_blocks_to_show').children('#loading_tooltip, [data-tooltip="0"]').first();
		}
		if (!$block.length) {
			return '';
		}
		return $block.find('.bluet_block_container').first().clone(true, true).get(0);
	}

	function destroyInstances() {
		instances.forEach(function (inst) {
			if (inst && typeof inst.destroy === 'function') {
				inst.destroy();
			}
		});
		instances = [];
	}

	/**
	 * @param {string} selector Keyword selector (e.g. ".bluet_tooltip, .bluet_img_tooltip")
	 * @param {string} position top|bottom|left|right
	 */
	window.tooltipyInitTippy = function (selector, position) {
		if (typeof tippy !== 'function') {
			return;
		}

		destroyInstances();

		var placement = mapPlacement(position || (window.tooltipyTippy && window.tooltipyTippy.placement) || 'bottom');
		var isMobile = window.matchMedia && window.matchMedia('(max-width: 400px)').matches;
		var trigger = isMobile ? 'click' : 'mouseenter focus';

		var elements = document.querySelectorAll(selector);
		if (!elements.length) {
			return;
		}

		var created = tippy(elements, {
			allowHTML: true,
			interactive: true,
			appendTo: function () {
				return document.body;
			},
			placement: placement,
			trigger: trigger,
			theme: 'tooltipy',
			animation: 'fade',
			maxWidth: 'none',
			zIndex: 999999,
			content: function (reference) {
				var id = reference.getAttribute('data-tooltip');
				return getContentForKeyword(id) || '';
			},
			onShow: function (instance) {
				if (window.currentHoveredKeyword !== 'done') {
					window.currentHoveredKeyword = $(instance.reference);
				}
				var box = instance.popper.querySelector('.tippy-content');
				if (box) {
					box.classList.add('bluet_block_to_show', 'tooltipy-pop');
				}
			},
			onHide: function (instance) {
				var iframe = instance.popper.querySelector('iframe');
				if (iframe && typeof window.callPlayer === 'function') {
					var wrap = iframe.parentElement;
					if (wrap && wrap.id) {
						try {
							window.callPlayer(wrap.id, 'pauseVideo');
						} catch (e) {
							/* ignore */
						}
					}
				}
			},
		});

		instances = Array.isArray(created) ? created : [created];
	};

	// Backward-compatible aliases used by inline matcher scripts.
	window.bluet_placeTooltips = function (inlineClass, position) {
		window.tooltipyInitTippy(inlineClass, position);
	};

	window.moveTooltipElementsTop = function () {
		// Tippy clones content from #tooltip_blocks_to_show; keep the container in place.
	};

	$(document).on('keywordsLoaded', function () {
		var pos = (window.tooltipyTippy && window.tooltipyTippy.placement) || 'bottom';
		window.tooltipyInitTippy('.bluet_tooltip, .bluet_img_tooltip', pos);
	});
})(window, jQuery);
