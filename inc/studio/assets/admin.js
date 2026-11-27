/**
 * Wood Shop Studio admin interactions.
 */
(function () {
	'use strict';

	/* Reset confirmation */
	var resetLinks = document.querySelectorAll('[data-wss-reset]');

	resetLinks.forEach(function (link) {
		link.addEventListener('click', function (event) {
			if (!window.confirm(window.wssAdmin ? window.wssAdmin.confirmReset : 'Reset all settings?')) {
				event.preventDefault();
			}
		});
	});

	/* Media pickers */
	function initMedia(scope) {
		(scope || document).querySelectorAll('[data-media]').forEach(function (wrapper) {
			if (wrapper.dataset.mediaReady === '1') {
				return;
			}
			wrapper.dataset.mediaReady = '1';

			var input = wrapper.querySelector('[data-media-input]');
			var preview = wrapper.querySelector('[data-media-preview]');
			var urlLabel = wrapper.querySelector('[data-media-url]');
			var selectButton = wrapper.querySelector('[data-media-select]');
			var removeButton = wrapper.querySelector('[data-media-remove]');

			if (!input || !preview) {
				return;
			}

			selectButton.addEventListener('click', function () {
				if (!window.wp || !window.wp.media) {
					return;
				}

				var frame = window.wp.media({
					title: window.wssAdmin ? window.wssAdmin.selectImage : 'Select image',
					button: { text: window.wssAdmin ? window.wssAdmin.useImage : 'Use image' },
					multiple: false,
					library: { type: 'image' },
				});

				frame.on('select', function () {
					var attachment = frame.state().get('selection').first().toJSON();
					var url = attachment.url;

					if (attachment.sizes) {
						if (attachment.sizes.large) {
							url = attachment.sizes.large.url;
						} else if (attachment.sizes.full) {
							url = attachment.sizes.full.url;
						}
					}

					input.value = attachment.id;
					preview.innerHTML = '<img alt="" src="' + url + '">';
					if (urlLabel) {
						urlLabel.textContent = url;
					}
				});

				frame.open();
			});

			if (removeButton) {
				removeButton.addEventListener('click', function (event) {
					event.preventDefault();
					input.value = '';
					preview.innerHTML = '<span class="wss-media__empty">No image selected</span>';
					if (urlLabel) {
						urlLabel.textContent = '';
					}
				});
			}
		});
	}

	initMedia(document);

	/* Repeaters */
	document.querySelectorAll('[data-repeater]').forEach(function (repeater) {
		var rows = repeater.querySelector('[data-rows]');
		var addButton = repeater.querySelector('[data-repeater-add]');
		var template = repeater.querySelector('template');
		var count = repeater.querySelector('[data-repeater-count]');
		var nextIndex = rows ? rows.querySelectorAll('[data-row]').length : 0;

		if (!rows || !addButton || !template) {
			return;
		}

		function refresh() {
			var items = rows.querySelectorAll('[data-row]');
			items.forEach(function (row, index) {
				var number = row.querySelector('[data-row-number]');
				if (number) {
					number.textContent = String(index + 1);
				}
			});
			if (count) {
				count.textContent = String(items.length);
			}
		}

		function bindRemove(row) {
			var remove = row.querySelector('[data-row-remove]');
			if (remove) {
				remove.addEventListener('click', function () {
					row.remove();
					refresh();
				});
			}
		}

		rows.querySelectorAll('[data-row]').forEach(bindRemove);

		addButton.addEventListener('click', function () {
			var index = nextIndex++;
			var html = template.innerHTML.replace(/__i__/g, String(index));
			var holder = document.createElement('div');
			holder.innerHTML = html.trim();
			var row = holder.firstElementChild;

			if (!row) {
				return;
			}

			rows.appendChild(row);
			bindRemove(row);
			initMedia(row);
			refresh();
		});

		refresh();
	});

	/* Warn before leaving with unsaved changes */
	var form = document.querySelector('.wss-form');
	if (form) {
		var dirty = false;
		var saving = false;

		form.addEventListener('input', function () {
			dirty = true;
		});

		form.addEventListener('change', function () {
			dirty = true;
		});

		form.addEventListener('submit', function () {
			saving = true;
		});

		window.addEventListener('beforeunload', function (event) {
			if (dirty && !saving) {
				event.preventDefault();
				event.returnValue = '';
			}
		});
	}
})();

/* Color pickers */
document.querySelectorAll('[data-color-picker]').forEach(function (picker) {
	var text = picker.parentElement.querySelector('[data-color-text]');
	if (!text) {
		return;
	}

	function expand(value) {
		var match = value.trim().match(/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i);
		if (match) {
			return '#' + match[1] + match[1] + match[2] + match[2] + match[3] + match[3];
		}
		return value.trim();
	}

	picker.addEventListener('input', function () {
		text.value = picker.value;
	});

	text.addEventListener('input', function () {
		var value = expand(text.value);
		if (/^#[0-9a-f]{6}$/i.test(value)) {
			picker.value = value;
		}
	});

	text.addEventListener('blur', function () {
		var value = expand(text.value);
		if (/^#[0-9a-f]{6}$/i.test(value)) {
			text.value = value;
			picker.value = value;
		}
	});
});
