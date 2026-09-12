/**
 * Tippy.js bridge for Tooltipy.
 * Keeps legacy CSS classes on keyword markup while Tippy handles positioning.
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

	/**
	 * Build tippy content from the AJAX/source blocks.
	 * Do NOT wrap with .bluet_block_to_show — that class is display:none in legacy CSS.
	 */
	function getContentForKeyword(id) {
		var $source = $('#tooltip_blocks_to_show').children('[data-tooltip="' + id + '"]').first();
		if (!$source.length) {
			$source = $('#tooltip_blocks_to_show').children('#loading_tooltip, [data-tooltip="0"]').first();
		}
		if (!$source.length) {
			return '';
		}

		var $container = $source.find('.bluet_block_container').first();
		if ($container.length) {
			return $container.clone(true, true).get(0);
		}

		// Fallback: clone inner HTML without the hidden wrapper class.
		var $clone = $source.clone(true, true);
		$clone.removeClass('bluet_block_to_show').css({
			display: 'block',
			opacity: '1',
			position: 'relative',
			visibility: 'visible',
		});
		return $clone.get(0);
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
		if (typeof tippy !== 'function' || typeof window.Popper === 'undefined') {
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
			maxWidth: 400,
			zIndex: 999999,
			content: function (reference) {
				var id = reference.getAttribute('data-tooltip');
				return getContentForKeyword(id) || '…';
			},
			onShow: function (instance) {
				if (window.currentHoveredKeyword !== 'done') {
					window.currentHoveredKeyword = $(instance.reference);
				}

				// Refresh content each show (AJAX may have filled blocks after first init).
				var id = instance.reference.getAttribute('data-tooltip');
				var fresh = getContentForKeyword(id);
				if (fresh) {
					instance.setContent(fresh);
				}

				var box = instance.popper && instance.popper.querySelector('.tippy-content');
				if (box) {
					// Keep tooltipy-pop for styling hooks — never bluet_block_to_show (display:none).
					box.classList.add('tooltipy-pop');
					box.classList.remove('bluet_block_to_show');
				}
			},
			onHide: function (instance) {
				var iframe = instance.popper && instance.popper.querySelector('iframe');
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
