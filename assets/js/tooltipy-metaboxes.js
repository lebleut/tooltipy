/**
 * Metabox helpers for Tooltipy admin.
 */
(function () {
	'use strict';

	function initExcludeToggle() {
		document.querySelectorAll('[data-tooltipy-toggle-target]').forEach(function (input) {
			var targetSelector = input.getAttribute('data-tooltipy-toggle-target');
			var target = targetSelector ? document.querySelector(targetSelector) : null;
			if (!target) {
				return;
			}

			function sync() {
				if (input.checked) {
					target.setAttribute('hidden', 'hidden');
				} else {
					target.removeAttribute('hidden');
				}
			}

			input.addEventListener('change', sync);
			sync();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initExcludeToggle);
	} else {
		initExcludeToggle();
	}
})();
