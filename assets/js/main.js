/**
 * Wood Shop theme interactions.
 * Vanilla JS, no dependencies.
 */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ------------------------------------------------------------------
	 * Mobile navigation
	 * ------------------------------------------------------------------ */
	var navToggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('site-nav');

	function closeNav() {
		if (!nav || !navToggle) {
			return;
		}
		nav.classList.remove('is-open');
		navToggle.setAttribute('aria-expanded', 'false');
		document.body.classList.remove('ws-nav-open');
	}

	if (navToggle && nav) {
		navToggle.addEventListener('click', function () {
			var open = navToggle.getAttribute('aria-expanded') === 'true';
			navToggle.setAttribute('aria-expanded', open ? 'false' : 'true');
			nav.classList.toggle('is-open', !open);
			document.body.classList.toggle('ws-nav-open', !open);
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeNav();
			}
		});

		document.addEventListener('click', function (event) {
			if (!nav.classList.contains('is-open')) {
				return;
			}
			if (!nav.contains(event.target) && !navToggle.contains(event.target)) {
				closeNav();
			}
		});

		window.addEventListener('resize', function () {
			if (window.innerWidth > 1024) {
				closeNav();
			}
		});

		nav.querySelectorAll('a').forEach(function (link) {
			link.addEventListener('click', function () {
				closeNav();
			});
		});
	}

	/* Submenu toggles (mobile) */
	var submenuParents = document.querySelectorAll('.header-nav .menu-item-has-children');

	submenuParents.forEach(function (item) {
		var link = item.querySelector(':scope > a');
		if (!link) {
			return;
		}

		var button = document.createElement('button');
		button.type = 'button';
		button.className = 'submenu-toggle';
		button.setAttribute('aria-expanded', 'false');
		button.setAttribute('aria-label', 'باز کردن زیرمنو');
		button.innerHTML = '<svg class="ws-icon" width="12" height="12" viewBox="0 0 448 512" aria-hidden="true" focusable="false"><path fill="currentColor" d="M207.029 381.476L12.686 187.132c-9.373-9.373-9.373-24.569 0-33.941l22.667-22.667c9.357-9.357 24.522-9.375 33.901-.04L224 284.505l154.745-154.021c9.379-9.335 24.544-9.317 33.901.04l22.667 22.667c9.373 9.373 9.373 24.569 0 33.941L240.971 381.476c-9.373 9.372-24.569 9.372-33.942 0z"></path></svg>';
		item.classList.add('has-toggle');
		item.appendChild(button);

		button.addEventListener('click', function () {
			var isOpen = item.classList.contains('is-open');

			submenuParents.forEach(function (sibling) {
				if (sibling !== item) {
					sibling.classList.remove('is-open');
					var siblingButton = sibling.querySelector('.submenu-toggle');
					if (siblingButton) {
						siblingButton.setAttribute('aria-expanded', 'false');
					}
				}
			});

			item.classList.toggle('is-open', !isOpen);
			button.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
		});
	});

	/* ------------------------------------------------------------------
	 * Hero slider
	 * ------------------------------------------------------------------ */
	document.querySelectorAll('[data-hero]').forEach(function (hero) {
		var slides = Array.prototype.slice.call(hero.querySelectorAll('.hero__slide'));
		var dots = Array.prototype.slice.call(hero.querySelectorAll('.hero__dot'));
		var pauseButton = hero.querySelector('.hero__pause');

		/* Start the Ken Burns effect only after the page has painted so it
		   cannot delay Largest Contentful Paint. */
		function enableMotion() {
			/* Keep the viewport still on phones/tablets: the continuous zoom is
			   decorative and expensive to rasterise on low-power devices. */
			if (reduceMotion || window.innerWidth <= 1024) {
				return;
			}

			window.setTimeout(function () {
				hero.classList.add('hero--ready');
			}, 700);
		}

		if (document.readyState === 'complete') {
			enableMotion();
		} else {
			window.addEventListener('load', enableMotion, { once: true });
		}

		if (slides.length < 2) {
			return;
		}

		var current = 0;
		var timer = null;
		var manuallyPaused = false;
		var interacted = false;

		function show(index) {
			current = (index + slides.length) % slides.length;

			slides.forEach(function (slide, i) {
				slide.classList.toggle('is-active', i === current);
			});

			dots.forEach(function (dot, i) {
				dot.classList.toggle('is-active', i === current);
				dot.setAttribute('aria-selected', i === current ? 'true' : 'false');
			});
		}

		function start() {
			if (reduceMotion || manuallyPaused || document.hidden) {
				return;
			}
			stop();
			timer = window.setInterval(function () {
				show(current + 1);
			}, 5600);
		}

		function stop() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		dots.forEach(function (dot, index) {
			dot.addEventListener('click', function () {
				interacted = true;
				show(index);
				start();
			});
		});

		hero.addEventListener('mouseenter', stop);
		hero.addEventListener('mouseleave', function () {
			if (!interacted) {
				start();
			}
		});
		hero.addEventListener('focusin', stop);
		hero.addEventListener('focusout', start);

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				stop();
			} else {
				start();
			}
		});

		if (pauseButton) {
			pauseButton.addEventListener('click', function () {
				manuallyPaused = !manuallyPaused;
				pauseButton.setAttribute('aria-pressed', manuallyPaused ? 'true' : 'false');
				pauseButton.setAttribute('aria-label', manuallyPaused ? 'پخش اسلایدها' : 'توقف پخش اسلایدها');
				pauseButton.classList.toggle('is-paused', manuallyPaused);

				if (manuallyPaused) {
					stop();
				} else {
					start();
				}
			});
		}

		start();
	});

	/* ------------------------------------------------------------------
	 * Cart drawer + live count
	 * ------------------------------------------------------------------ */
	var cartConfig = window.woodshopCart || null;
	var cartDrawer = document.querySelector('[data-cart-drawer]');
	var cartPanel = cartDrawer ? cartDrawer.querySelector('.cart-drawer__panel') : null;
	var cartBody = cartDrawer ? cartDrawer.querySelector('[data-cart-body]') : null;
	var cartStatus = document.getElementById('woodshop-cart-status');
	var cartTrigger = document.querySelector('[data-cart-open]');
	var lastCartFocus = null;

	function faDigits(value) {
		return String(value).replace(/[0-9]/g, function (digit) {
			return '۰۱۲۳۴۵۶۷۸۹'[Number(digit)];
		});
	}

	function setCartStatus(message) {
		if (!cartStatus || !message) {
			return;
		}

		cartStatus.textContent = '';
		window.setTimeout(function () {
			cartStatus.textContent = message;
		}, 30);
	}

	function setCartBusy(busy) {
		if (!cartDrawer) {
			return;
		}

		cartDrawer.classList.toggle('is-loading', busy);
		cartDrawer.setAttribute('aria-busy', busy ? 'true' : 'false');
	}

	function openCartDrawer() {
		if (!cartDrawer || cartDrawer.classList.contains('is-open')) {
			return;
		}

		lastCartFocus = document.activeElement;

		var openNav = document.querySelector('.header-nav.is-open');
		if (openNav) {
			openNav.classList.remove('is-open');
			document.body.classList.remove('ws-nav-open');
			var navButton = document.querySelector('.nav-toggle');
			if (navButton) {
				navButton.setAttribute('aria-expanded', 'false');
			}
		}

		cartDrawer.classList.add('is-open');
		cartDrawer.setAttribute('aria-hidden', 'false');
		document.body.classList.add('ws-drawer-open');

		if (cartTrigger) {
			cartTrigger.setAttribute('aria-expanded', 'true');
		}

		var closeButton = cartDrawer.querySelector('.cart-drawer__close');
		if (closeButton) {
			window.setTimeout(function () {
				closeButton.focus();
			}, 60);
		}
	}

	function closeCartDrawer() {
		if (!cartDrawer || !cartDrawer.classList.contains('is-open')) {
			return;
		}

		cartDrawer.classList.remove('is-open');
		cartDrawer.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('ws-drawer-open');

		if (cartTrigger) {
			cartTrigger.setAttribute('aria-expanded', 'false');
		}

		if (lastCartFocus && typeof lastCartFocus.focus === 'function') {
			lastCartFocus.focus();
		}
	}

	function updateCartCount(count) {
		document.querySelectorAll('[data-cart-count]').forEach(function (element) {
			element.textContent = faDigits(count);
			element.classList.toggle('has-items', Number(count) > 0);

			if (!reduceMotion) {
				element.classList.remove('is-bumped');
				void element.offsetWidth;
				element.classList.add('is-bumped');
				window.setTimeout(function () {
					element.classList.remove('is-bumped');
				}, 500);
			}
		});

		var trigger = document.querySelector('[data-cart-open]');
		if (trigger) {
			trigger.setAttribute('aria-label', 'مشاهده سبد خرید، ' + faDigits(count) + ' کالا');
		}
	}

	function updateCartView(payload) {
		if (!payload) {
			return;
		}

		updateCartCount(payload.count);

		if (cartBody && typeof payload.html === 'string') {
			cartBody.innerHTML = payload.html;
		}
	}

	function cartRequest(action, data) {
		if (!cartConfig) {
			return Promise.reject(new Error('cart unavailable'));
		}

		var body = new FormData();
		body.append('action', action);
		body.append('nonce', cartConfig.nonce);

		Object.keys(data || {}).forEach(function (key) {
			body.append(key, data[key]);
		});

		return window.fetch(cartConfig.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		}).then(function (response) {
			return response.json();
		});
	}

	function refreshCart(openAfter) {
		if (!cartConfig || !cartDrawer) {
			return;
		}

		cartRequest('woodshop_get_cart', {})
			.then(function (result) {
				if (result && result.success) {
					updateCartView(result.data);
					if (openAfter) {
						openCartDrawer();
					}
				}
			})
			.catch(function () {});
	}

	function setButtonState(button, state) {
		if (!button) {
			return;
		}

		var label = button.querySelector('.product-card__btn-label');
		var strings = cartConfig ? cartConfig.strings : {};

		button.classList.remove('is-loading', 'is-added', 'is-error');

		if (state && state !== 'default') {
			button.classList.add('is-' + state);
		}

		button.disabled = state === 'loading';
		button.setAttribute('aria-disabled', state === 'loading' ? 'true' : 'false');
		button.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');

		if (label) {
			if (!button.dataset.originalLabel) {
				button.dataset.originalLabel = label.textContent;
			}

			if (state === 'loading') {
				label.textContent = strings.adding || 'در حال افزودن…';
			} else if (state === 'added') {
				label.textContent = strings.added || 'به سبد اضافه شد';
			} else if (state === 'error') {
				label.textContent = strings.error || 'خطا، دوباره تلاش کنید';
			} else {
				label.textContent = button.dataset.originalLabel;
			}
		} else {
			if (!button.dataset.originalHtml) {
				button.dataset.originalHtml = button.innerHTML;
			}

			if (state === 'loading') {
				button.textContent = strings.adding || 'در حال افزودن…';
			} else if (state === 'added') {
				button.textContent = strings.added || 'به سبد اضافه شد';
			} else if (state === 'error') {
				button.textContent = strings.error || 'خطا، دوباره تلاش کنید';
			} else {
				button.innerHTML = button.dataset.originalHtml;
			}
		}

		window.clearTimeout(button._wsStateTimer);

		if (state === 'added' || state === 'error') {
			button._wsStateTimer = window.setTimeout(function () {
				setButtonState(button, 'default');
			}, state === 'added' ? 2500 : 3200);
		}
	}

	function addProductToCart(productId, quantity, button) {
		if (!productId || !cartConfig) {
			return;
		}

		if (button && (button.classList.contains('is-loading') || button.disabled)) {
			return;
		}

		setButtonState(button, 'loading');
		setCartBusy(true);

		cartRequest('woodshop_add_to_cart', {
			product_id: productId,
			quantity: quantity || 1
		})
			.then(function (result) {
				if (result && result.success) {
					updateCartView(result.data.payload);
					setButtonState(button, 'added');
					setCartStatus(result.data.message || (cartConfig.strings && cartConfig.strings.addedMessage));
					openCartDrawer();
				} else {
					setButtonState(button, 'error');
					setCartStatus((result && result.data && result.data.message) || (cartConfig.strings && cartConfig.strings.errorMessage));
				}
			})
			.catch(function () {
				setButtonState(button, 'error');
				setCartStatus(cartConfig.strings && cartConfig.strings.errorMessage);
			})
			.finally(function () {
				setCartBusy(false);
			});
	}

	function removeCartItem(cartItemKey, button) {
		if (!cartConfig || !cartItemKey) {
			return;
		}

		if (button) {
			button.disabled = true;
			button.classList.add('is-removing');
		}

		setCartBusy(true);

		cartRequest('woodshop_remove_cart_item', { cart_item_key: cartItemKey })
			.then(function (result) {
				if (result && result.success) {
					updateCartView(result.data.payload);
					setCartStatus(result.data.message || (cartConfig.strings && cartConfig.strings.removedMessage));
				} else {
					setCartStatus((result && result.data && result.data.message) || (cartConfig.strings && cartConfig.strings.errorMessage));
					if (button) {
						button.disabled = false;
						button.classList.remove('is-removing');
					}
				}
			})
			.catch(function () {
				setCartStatus(cartConfig.strings && cartConfig.strings.errorMessage);
				if (button) {
					button.disabled = false;
					button.classList.remove('is-removing');
				}
			})
			.finally(function () {
				setCartBusy(false);
			});
	}

	if (cartDrawer && cartConfig) {
		document.addEventListener('click', function (event) {
			var openTrigger = event.target.closest('[data-cart-open]');
			if (openTrigger) {
				event.preventDefault();
				openCartDrawer();
				return;
			}

			var closeTrigger = event.target.closest('[data-cart-close]');
			if (closeTrigger) {
				event.preventDefault();
				closeCartDrawer();
				return;
			}

			var addButton = event.target.closest('[data-add-to-cart]');
			if (addButton) {
				event.preventDefault();
				addProductToCart(addButton.dataset.addToCart, addButton.dataset.quantity, addButton);
				return;
			}

			var removeButton = event.target.closest('[data-cart-remove]');
			if (removeButton) {
				event.preventDefault();
				removeCartItem(removeButton.dataset.cartRemove, removeButton);
			}
		});

		document.addEventListener('keydown', function (event) {
			if (!cartDrawer.classList.contains('is-open')) {
				return;
			}

			if (event.key === 'Escape') {
				closeCartDrawer();
				return;
			}

			if (event.key !== 'Tab' || !cartPanel) {
				return;
			}

			var focusable = Array.prototype.slice.call(
				cartPanel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])')
			).filter(function (element) {
				return element.offsetParent !== null;
			});

			if (!focusable.length) {
				return;
			}

			var first = focusable[0];
			var last = focusable[focusable.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});

		if (window.jQuery) {
			window.jQuery(document.body).on('added_to_cart', function () {
				refreshCart(true);
			});
		}

		var singleCartForm = document.querySelector('form.cart');

		if (singleCartForm && !singleCartForm.querySelector('input[name="variation_id"]')) {
			singleCartForm.addEventListener('submit', function (event) {
				var button = singleCartForm.querySelector('.single_add_to_cart_button');
				var idInput = singleCartForm.querySelector('input[name="add-to-cart"]') ||
					singleCartForm.querySelector('button[name="add-to-cart"]') ||
					singleCartForm.querySelector('input[name="product_id"]');
				var qtyInput = singleCartForm.querySelector('input.qty');
				var productId = idInput ? parseInt(idInput.value, 10) : 0;

				if (!productId) {
					return;
				}

				event.preventDefault();
				addProductToCart(productId, qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1, button);
			});
		}
	}

	function initDeferred() {
	/* ------------------------------------------------------------------
	 * Reveal on scroll
	 * ------------------------------------------------------------------ */
	var revealItems = document.querySelectorAll('.reveal');

	if (window.innerWidth > 767 && 'IntersectionObserver' in window && revealItems.length) {
		var revealObserver = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-in');
						revealObserver.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
		);

		revealItems.forEach(function (item) {
			revealObserver.observe(item);
		});
	}

	/* ------------------------------------------------------------------
	 * Counters
	 * ------------------------------------------------------------------ */
	var counters = document.querySelectorAll('[data-count]');

	function animateCounter(el) {
		var target = parseInt(el.getAttribute('data-count'), 10);

		if (isNaN(target)) {
			return;
		}

		if (reduceMotion) {
			el.textContent = String(target);
			return;
		}

		var start = null;
		var duration = 1500;

		function step(timestamp) {
			if (!start) {
				start = timestamp;
			}
			var progress = Math.min((timestamp - start) / duration, 1);
			var eased = 1 - Math.pow(1 - progress, 3);
			el.textContent = String(Math.round(target * eased));

			if (progress < 1) {
				window.requestAnimationFrame(step);
			}
		}

		el.textContent = '0';
		window.requestAnimationFrame(step);
	}

	if ('IntersectionObserver' in window && counters.length) {
		var counterObserver = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						animateCounter(entry.target);
						counterObserver.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.4 }
		);

		counters.forEach(function (counter) {
			counterObserver.observe(counter);
		});
	}

	/* ------------------------------------------------------------------
	 * Testimonials
	 * ------------------------------------------------------------------ */
	function setupTestimonials(slider) {
		var slides = Array.prototype.slice.call(slider.querySelectorAll('.testimonial'));
		var dotsWrap = slider.querySelector('.testimonials__dots');
		var prev = slider.querySelector('[data-testimonial-prev]');
		var next = slider.querySelector('[data-testimonial-next]');

		if (slides.length < 2) {
			if (dotsWrap) {
				dotsWrap.style.display = 'none';
			}
			if (prev) {
				prev.style.display = 'none';
			}
			if (next) {
				next.style.display = 'none';
			}
			return;
		}

		var index = 0;
		var timer = null;
		var paused = false;

		function render() {
			slides.forEach(function (slide, i) {
				var active = i === index;
				slide.classList.toggle('is-active', active);
				slide.setAttribute('aria-hidden', active ? 'false' : 'true');
			});

			if (dotsWrap) {
				Array.prototype.forEach.call(dotsWrap.children, function (dot, i) {
					dot.classList.toggle('is-active', i === index);
					dot.setAttribute('aria-selected', i === index ? 'true' : 'false');
				});
			}
		}

		function go(step) {
			index = (index + step + slides.length) % slides.length;
			render();
		}

		if (dotsWrap) {
			slides.forEach(function (slide, i) {
				var dot = document.createElement('button');
				dot.type = 'button';
				dot.className = 'testimonials__dot';
				dot.setAttribute('role', 'tab');
				dot.setAttribute('aria-label', 'نظر ' + (i + 1));
				dot.addEventListener('click', function () {
					index = i;
					render();
				});
				dotsWrap.appendChild(dot);
			});
		}

		if (prev) {
			prev.addEventListener('click', function () {
				go(-1);
			});
		}

		if (next) {
			next.addEventListener('click', function () {
				go(1);
			});
		}

		function start() {
			if (reduceMotion) {
				return;
			}
			stop();
			timer = window.setInterval(function () {
				if (!paused && !document.hidden) {
					go(1);
				}
			}, 7000);
		}

		function stop() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		slider.addEventListener('mouseenter', function () {
			paused = true;
		});
		slider.addEventListener('mouseleave', function () {
			paused = false;
		});
		slider.addEventListener('focusin', function () {
			paused = true;
		});
		slider.addEventListener('focusout', function () {
			paused = false;
		});

		render();
		start();
	}

	document.querySelectorAll('[data-testimonials]').forEach(function (slider) {
		if (!('IntersectionObserver' in window)) {
			setupTestimonials(slider);
			return;
		}

		var testimonialObserver = new IntersectionObserver(
			function (entries) {
				if (entries[0].isIntersecting) {
					testimonialObserver.disconnect();
					setupTestimonials(slider);
				}
			},
			{ rootMargin: '300px 0px' }
		);

		testimonialObserver.observe(slider);
	});

	/* ------------------------------------------------------------------
	 * Shop filters drawer
	 * ------------------------------------------------------------------ */
	var shopFilters = document.querySelector('[data-shop-filters]');
	var filtersOpen = document.querySelector('[data-shop-filters-open]');
	var filtersClose = document.querySelectorAll('[data-shop-filters-close]');

	function closeShopFilters() {
		if (!shopFilters) {
			return;
		}
		shopFilters.classList.remove('is-open');
		document.body.classList.remove('ws-filters-open');
		if (filtersOpen) {
			filtersOpen.setAttribute('aria-expanded', 'false');
		}
	}

	if (shopFilters && filtersOpen) {
		filtersOpen.addEventListener('click', function () {
			shopFilters.classList.add('is-open');
			document.body.classList.add('ws-filters-open');
			filtersOpen.setAttribute('aria-expanded', 'true');
			var firstField = shopFilters.querySelector('input, select, button');
			if (firstField) {
				firstField.focus({ preventScroll: true });
			}
		});

		filtersClose.forEach(function (button) {
			button.addEventListener('click', closeShopFilters);
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeShopFilters();
			}
		});

		window.addEventListener('resize', function () {
			if (window.innerWidth > 1023) {
				closeShopFilters();
			}
		});
	}

	/* ------------------------------------------------------------------
	 * Form niceties
	 * ------------------------------------------------------------------ */
	var dateField = document.getElementById('ws-field-date');
	if (dateField) {
		var today = new Date();
		var iso = today.toISOString().split('T')[0];
		dateField.setAttribute('min', iso);
	}

	var form = document.querySelector('.request-form__form');
	if (form) {
		form.addEventListener('submit', function () {
			var button = form.querySelector('button[type="submit"]');
			if (button) {
				button.disabled = true;
				button.textContent = 'در حال ارسال…';
			}
		});
	}
	}

	function scheduleDeferred() {
		if ('requestIdleCallback' in window) {
			window.requestIdleCallback(initDeferred, { timeout: 2500 });
		} else {
			window.setTimeout(initDeferred, 300);
		}
	}

	if (document.readyState === 'complete') {
		scheduleDeferred();
	} else {
		window.addEventListener('load', scheduleDeferred, { once: true });
	}

})();
