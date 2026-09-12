/**
 * Dedicated settings-page navigation (vanilla JS — no jQuery required).
 */
(function () {
	'use strict';

	function activatePanel(root, panelId) {
		var buttons = root.querySelectorAll('.tooltipy-settings__nav-item[data-tooltipy-panel]');
		var panels = root.querySelectorAll('.tooltipy-settings__panel[data-panel]');

		buttons.forEach(function (btn) {
			var active = btn.getAttribute('data-tooltipy-panel') === panelId;
			btn.classList.toggle('is-active', active);
			btn.setAttribute('aria-pressed', active ? 'true' : 'false');
		});

		panels.forEach(function (panel) {
			var active = panel.getAttribute('data-panel') === panelId;
			panel.classList.toggle('is-active', active);
			if (active) {
				panel.removeAttribute('hidden');
			} else {
				panel.setAttribute('hidden', 'hidden');
			}
		});

		try {
			window.sessionStorage.setItem('tooltipy_settings_panel', panelId);
		} catch (e) {
			/* ignore */
		}
	}

	function init() {
		var root = document.getElementById('tooltipy-settings');
		if (!root) {
			return;
		}

		root.addEventListener('click', function (event) {
			var btn = event.target.closest('.tooltipy-settings__nav-item[data-tooltipy-panel]');
			if (!btn || !root.contains(btn)) {
				return;
			}
			event.preventDefault();
			activatePanel(root, btn.getAttribute('data-tooltipy-panel'));
		});

		var initial = 'style';
		try {
			var stored = window.sessionStorage.getItem('tooltipy_settings_panel');
			if (stored && root.querySelector('.tooltipy-settings__panel[data-panel="' + stored + '"]')) {
				initial = stored;
			}
		} catch (e) {
			/* ignore */
		}

		activatePanel(root, initial);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
