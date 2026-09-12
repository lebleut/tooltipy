/**
 * Tooltipy admin settings: tabs, easy_tags, style preview helpers.
 */
(function (window, $) {
	'use strict';

	function bluetShowTab(tabId) {
		var container = document.getElementById('bluet-sections-div');
		if (!container || !tabId) {
			return;
		}

		$(container)
			.children('.bluet-section')
			.removeClass('is-active')
			.attr('hidden', true)
			.hide();

		var $target = $('#' + tabId);
		if ($target.length) {
			$target.addClass('is-active').removeAttr('hidden').show();
		}
	}

	function initSettingsTabs() {
		var $wrap = $('#bluet-general');
		if (!$wrap.length || !$('#bluet-sections-div').length) {
			return;
		}

		// Event delegation — survives focus/hash quirks on <a class="nav-tab">.
		$wrap
			.off('click.tooltipyTabs', '.nav-tab-wrapper a.nav-tab[data-tab]')
			.on('click.tooltipyTabs', '.nav-tab-wrapper a.nav-tab[data-tab]', function (e) {
				e.preventDefault();
				e.stopPropagation();

				var $link = $(this);
				var tabToShow = $link.attr('data-tab');
				if (!tabToShow) {
					return false;
				}

				$wrap.find('.nav-tab-wrapper a.nav-tab[data-tab]').removeClass('nav-tab-active');
				$link.addClass('nav-tab-active');
				bluetShowTab(tabToShow);
				return false;
			});

		// Initial state: Style panel.
		$wrap.find('.nav-tab-wrapper a.nav-tab[data-tab]').removeClass('nav-tab-active');
		$('#bluet_style_tab').addClass('nav-tab-active');
		bluetShowTab('bluet-section-style');
	}

	function bluet_hide_bg() {
		var elem = document.getElementsByClassName('bluet_tooltip')[0];
		var noBg = document.getElementById('bluet_kw_no_background');
		var bgHide = document.getElementById('bluet_kw_bg_hide');
		if (!elem || !noBg) {
			return;
		}

		var txt_color = elem.style.color;
		if (noBg.checked) {
			elem.style.backgroundColor = 'initial';
			elem.style.borderBottom = txt_color + ' 1px dotted';
			elem.style.borderRadius = '0px';
			if (bgHide) {
				bgHide.style.display = 'none';
			}
		} else {
			var bgInput = document.getElementsByName('bluet_kw_style[bt_kw_tt_bg_color]')[0];
			elem.style.backgroundColor = bgInput ? bgInput.value : '';
			if (bgHide) {
				bgHide.style.display = 'block';
			}
			elem.style.borderBottom = '0px';
		}
	}

	function hideIfChecked(myId, idToDeal) {
		if ($('#' + myId).prop('checked')) {
			$('#' + idToDeal).hide();
		} else {
			$('#' + idToDeal).show();
		}
	}

	function bleutExcludeKwStyle() {
		var checked_ones = 0;
		$('#bluet_kw_admin_div_terms li input').each(function () {
			if ($(this).prop('checked')) {
				checked_ones++;
			}
		});

		if (checked_ones < 1) {
			$('#bluet_kw_admin_div_terms li').css('text-decoration', 'initial');
			return;
		}

		$('#bluet_kw_admin_div_terms li').css('text-decoration', 'line-through');
		$('#bluet_kw_admin_div_terms li').each(function (ind) {
			if ($('#bluet_kw_admin_div_terms li input').eq(ind).prop('checked')) {
				$('#bluet_kw_admin_div_terms li').eq(ind).css('text-decoration', 'initial');
			}
		});
	}

	function resolveEasyTagsDelimiter($root) {
		var attr = $root.attr('data-easy-tags-delimiter');
		if (typeof attr !== 'undefined') {
			return attr;
		}
		return easy_tags.delimiter || ' ';
	}

	var easy_tags = {
		delimiter: ' ',

		construct: function (deli) {
			this.delimiter = deli;
			return this;
		},

		add_to_send: function (element) {
			var $el = $(element);
			var delimiter = resolveEasyTagsDelimiter($el);
			var list = $el.find('.easy_tags-list');
			var to_send = $el.find('.easy_tags-to_send');
			var res = '';

			list.find('.elem_class').each(function () {
				res += $(this).find('.class_val').html() + delimiter;
			});

			to_send.val(res);
		},

		init: function (element_class) {
			var element = element_class && element_class.jquery ? element_class : $(element_class);

			element.each(function () {
				var $root = $(this);
				var delimiter = resolveEasyTagsDelimiter($root);
				var to_send = $root.find('.easy_tags-to_send');
				var list = $root.find('.easy_tags-list');
				var tab_tmp = to_send.val().split(delimiter);

				for (var i = 0; i < tab_tmp.length; i++) {
					if (tab_tmp[i] !== '') {
						var elem = document.createElement('span');
						elem.className = 'elem_class';
						elem.innerHTML =
							"<a class='ntdelbutton' href='#' onclick=\"var p=this.parentNode.parentNode.parentNode; this.parentNode.remove(); easy_tags.add_to_send(p); return false;\">X</a> <span class='class_val'>" +
							tab_tmp[i] +
							'</span>';
						list.append(elem);
					}
				}

				$root
					.find('.easy_tags-field')
					.off('keydown.tooltipyEasyTags')
					.on('keydown.tooltipyEasyTags', function (e) {
						if ($(this).val() === '' && e.keyCode === 8) {
							$(this).closest('.easy_tags').find('.elem_class').last().remove();
							easy_tags.add_to_send($(this).closest('.easy_tags').get(0));
						}
					});
			});
		},

		fill_classes: function (element_class) {
			var element = element_class && element_class.jquery ? element_class : $(element_class);

			element.each(function () {
				var root = $(this);
				var field = root.find('.easy_tags-field');
				var add = root.find('.easy_tags-add');
				var list = root.find('.easy_tags-list');

				add.off('click.tooltipyEasyTags').on('click.tooltipyEasyTags', function () {
					if (field.val().trim() !== '') {
						var elem = document.createElement('span');
						elem.className = 'elem_class';
						elem.innerHTML =
							"<a class='ntdelbutton' href='#' onclick=\"var p=this.parentNode.parentNode.parentNode; this.parentNode.remove(); easy_tags.add_to_send(p); return false;\">X</a> <span class='class_val'>" +
							field.val().trim() +
							'</span>';
						list.append(elem);
					}

					field.val('');
					easy_tags.add_to_send(root.get(0));
					field.focus();
				});
			});
		},
	};

	// Global aliases (legacy inline scripts + metaboxes).
	window.easy_tags = easy_tags;
	window.bluetShowTab = bluetShowTab;
	window.bluet_hide_bg = bluet_hide_bg;
	window.hideIfChecked = hideIfChecked;
	window.bleutExcludeKwStyle = bleutExcludeKwStyle;

	function initStylePreviewHelpers() {
		var noBg = document.getElementById('bluet_kw_no_background');
		if (noBg) {
			bluet_hide_bg();
			noBg.addEventListener('change', bluet_hide_bg, false);
		}

		var holders = document.getElementsByClassName('wp-picker-holder');
		for (var i = 0; i < holders.length; i++) {
			holders[i].addEventListener('mousemove', function () {
				var tip = document.getElementsByClassName('bluet_tooltip')[0];
				var block = document.getElementsByClassName('bluet_block_container')[0];
				if (!tip || !block) {
					return;
				}

				var noBgEl = document.getElementById('bluet_kw_no_background');
				var bluet_keyword_bg =
					noBgEl && noBgEl.checked
						? 'initial'
						: (document.getElementsByName('bluet_kw_style[bt_kw_tt_bg_color]')[0] || {}).value;
				var bluet_keyword_color = (document.getElementsByName('bluet_kw_style[bt_kw_tt_color]')[0] || {}).value;
				var bluet_tooltip_bg = (document.getElementsByName('bluet_kw_style[bt_kw_desc_bg_color]')[0] || {}).value;
				var bluet_tooltip_color = (document.getElementsByName('bluet_kw_style[bt_kw_desc_color]')[0] || {}).value;

				tip.style.backgroundColor = bluet_keyword_bg;
				tip.style.color = bluet_keyword_color;
				block.style.backgroundColor = bluet_tooltip_bg;
				block.style.boxShadow = '0px 0px 10px ' + bluet_tooltip_bg;
				block.style.color = bluet_tooltip_color;
			});
		}

		if ($('#bt_kw_fetch_mode-icon').is(':checked')) {
			$('#tooltip_highlight_fetch_mode').hide();
		}
		$('#bt_kw_fetch_mode-highlight').on('change', function () {
			$('#tooltip_highlight_fetch_mode').show();
		});
		$('#bt_kw_fetch_mode-icon').on('change', function () {
			$('#tooltip_highlight_fetch_mode').hide();
		});
	}

	function initEasyTagsWidgets() {
		var roots = $('.easy_tags');
		if (!roots.length) {
			return;
		}

		roots.each(function () {
			var $root = $(this);
			var delimiter = $root.attr('data-easy-tags-delimiter');
			if (typeof delimiter === 'undefined') {
				delimiter = ' ';
			}
			easy_tags.construct(delimiter);
			easy_tags.init($root);
			easy_tags.fill_classes($root);
		});
	}

	function initPostMetaboxHelpers() {
		if (!$('#bluet_kw_admin_div_terms').length && !$('.bluet_tooltip').length) {
			// Still allow exclude checkbox helper when present.
		}
		if ($('#bluet_kw_admin_div_terms').length) {
			bleutExcludeKwStyle();
			$('#bluet_kw_admin_div_terms li input').on('change', bleutExcludeKwStyle);
			hideIfChecked('bluet_kw_admin_exclude_post_from_matching_id', 'bluet_kw_admin_div_terms');
			$('#bluet_kw_admin_exclude_post_from_matching_id').on('change', function () {
				hideIfChecked('bluet_kw_admin_exclude_post_from_matching_id', 'bluet_kw_admin_div_terms');
			});
		}
	}

	$(function () {
		initSettingsTabs();
		initStylePreviewHelpers();
		initEasyTagsWidgets();
		initPostMetaboxHelpers();
	});
})(window, jQuery);
