// assets/js/frontend.js

(function($) {
    'use strict';

    class NewsletterPopup {
        constructor() {
            // Domyślna konfiguracja (nadpisywana przez ustawienia z PHP)
            this.config = {
                delay: 5000,
                duration: 0, // 0 = bez limitu czasowego
                hideAfterEmail: true,
                cookieName: 'npp_popup_closed',
                cookieDays: 1
            };

            this.elements = {
                overlay: null,
                form: null,
                emailInput: null,
                submitBtn: null,
                message: null,
                closeBtn: null
            };

            this.timers = {
                show: null,
                hide: null
            };

            this.isVisible = false;
            this.init();
        }

        init() {
            $(document).ready(() => {
                // Pobierz konfigurację z PHP jeśli dostępna
                if (typeof npp_popup_config !== 'undefined') {
                    this.config = { ...this.config, ...npp_popup_config };
                }

                this.cacheElements();

                if (this.shouldShowPopup()) {
                    this.schedulePopup();
                }

                this.bindEvents();
            });
        }

        cacheElements() {
            this.elements.overlay = $('#npp-popup-overlay');
            this.elements.form = $('#npp-subscribe-form');
            this.elements.emailInput = $('#npp-email');
            this.elements.submitBtn = this.elements.form.find('.npp-submit');
            this.elements.message = this.elements.form.find('.npp-message');
            this.elements.closeBtn = $('.npp-close');
        }

        bindEvents() {
            // Zamknięcie popup-a
            this.elements.closeBtn.on('click', (e) => {
                e.preventDefault();
                this.closePopup(true); // true = ustaw ciasteczko
            });

            // Zamknięcie przez kliknięcie w nakładkę
            this.elements.overlay.on('click', (e) => {
                if (e.target === e.currentTarget) {
                    this.closePopup(true);
                }
            });

            // Zamknięcie przez klawisz ESC
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.isVisible) {
                    this.closePopup(true);
                }
            });

            // Obsługa formularza
            this.elements.form.on('submit', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });

            // Walidacja emaila w czasie rzeczywistym
            this.elements.emailInput.on('blur', () => {
                this.validateEmail();
            });

            // Ukrywanie popup-a po wpisaniu prawidłowego emaila
            if (this.config.hideAfterEmail) {
                this.elements.emailInput.on('input', () => {
                    this.clearError();
                    this.checkEmailAndHide();
                });
            } else {
                // Tylko czyszczenie błędów jeśli opcja wyłączona
                this.elements.emailInput.on('input', () => {
                    this.clearError();
                });
            }
        }

        shouldShowPopup() {
            // Sprawdź czy popup nie był już zamknięty
            if (this.getCookie(this.config.cookieName)) {
                return false;
            }

            // Sprawdź czy element istnieje
            return this.elements.overlay.length > 0;
        }

        schedulePopup() {
            // Zaplanuj wyświetlenie popup-a
            this.timers.show = setTimeout(() => {
                this.showPopup();
            }, this.config.delay);
        }

        showPopup() {
            this.elements.overlay.addClass('npp-show');
            $('body').css('overflow', 'hidden');
            this.elements.emailInput.focus();
            this.isVisible = true;

            // Dostępność
            this.elements.overlay.attr('aria-hidden', 'false');
            this.trapFocus();

            // Zaplanuj automatyczne ukrycie jeśli ustawione
            if (this.config.duration > 0) {
                this.timers.hide = setTimeout(() => {
                    this.closePopup(true);
                }, this.config.duration);
            }

            // Event hook dla deweloperów
            $(document).trigger('npp_popup_shown');
        }

        closePopup(setCookie = false) {
            // Wyczyść timery
            if (this.timers.show) {
                clearTimeout(this.timers.show);
                this.timers.show = null;
            }
            if (this.timers.hide) {
                clearTimeout(this.timers.hide);
                this.timers.hide = null;
            }

            this.elements.overlay.removeClass('npp-show');
            $('body').css('overflow', '');
            this.isVisible = false;

            // Dostępność
            this.elements.overlay.attr('aria-hidden', 'true');
            this.releaseFocus();

            // Ustaw ciasteczko jeśli wymagane
            if (setCookie) {
                this.setClosedCookie();
            }

            // Event hook dla deweloperów
            $(document).trigger('npp_popup_closed');
        }

        checkEmailAndHide() {
            if (!this.config.hideAfterEmail) {
                return;
            }

            const email = this.elements.emailInput.val().trim();

            // Sprawdź czy email jest prawidłowy
            if (email && this.isValidEmail(email)) {
                // Ukryj popup po krótkim opóźnieniu dla lepszego UX
                setTimeout(() => {
                    this.closePopup(false); // nie ustawiaj ciasteczka
                }, 800);
            }
        }

        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        handleSubmit() {
            const email = this.elements.emailInput.val().trim();

            // Walidacja
            if (!this.validateEmail()) {
                return;
            }

            // Włącz stan ładowania
            this.setLoading(true);

            // Anuluj automatyczne ukrycie podczas wysyłania
            if (this.timers.hide) {
                clearTimeout(this.timers.hide);
                this.timers.hide = null;
            }

            // Żądanie AJAX
            $.ajax({
                url: npp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'npp_subscribe',
                    email: email,
                    nonce: npp_ajax.nonce
                },
                success: (response) => {
                    this.setLoading(false);

                    if (response.success) {
                        // Pokaż komunikat sukcesu
                        this.showMessage(response.data.message, 'success');

                        // Ukryj pola formularza ale zostaw komunikat widoczny
                        this.elements.emailInput.hide();
                        this.elements.submitBtn.hide();

                        // Zamknij popup po 3 sekundach
                        setTimeout(() => {
                            this.closePopup(true);
                        }, 3000);
                    } else {
                        this.showMessage(response.data.message || 'Wystąpił błąd', 'error');
                    }
                },
                error: () => {
                    this.showMessage(npp_ajax.messages.error, 'error');
                    this.setLoading(false);
                }
            });
        }

        validateEmail() {
            const email = this.elements.emailInput.val().trim();

            if (!email) {
                this.showError(npp_ajax.messages.invalid_email);
                return false;
            }

            if (!this.isValidEmail(email)) {
                this.showError(npp_ajax.messages.invalid_email);
                return false;
            }

            this.clearError();
            return true;
        }

        showError(message) {
            this.elements.emailInput.addClass('npp-error');
            this.showMessage(message, 'error');
        }

        clearError() {
            this.elements.emailInput.removeClass('npp-error');
            this.elements.message.hide().removeClass('npp-error npp-success');
        }

        showMessage(message, type) {
            this.elements.message
                .removeClass('npp-error npp-success')
                .addClass('npp-' + type)
                .html(message)
                .show();
        }

        setLoading(loading) {
            if (loading) {
                this.elements.submitBtn
                    .addClass('npp-loading')
                    .prop('disabled', true);
                this.elements.emailInput.prop('readonly', true);
            } else {
                this.elements.submitBtn
                    .removeClass('npp-loading')
                    .prop('disabled', false);
                this.elements.emailInput.prop('readonly', false);
            }
        }

        // Zarządzanie ciasteczkami
        setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = 'expires=' + date.toUTCString();
            document.cookie = name + '=' + value + ';' + expires + ';path=/;SameSite=Lax';
        }

        getCookie(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');

            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1, c.length);
                }
                if (c.indexOf(nameEQ) === 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }
            return null;
        }

        setClosedCookie() {
            this.setCookie(this.config.cookieName, '1', this.config.cookieDays);
        }

        // Dostępność - Pułapka focusu
        trapFocus() {
            const focusableElements = this.elements.overlay.find(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            ).filter(':visible');

            const firstFocusable = focusableElements.first();
            const lastFocusable = focusableElements.last();

            $(document).on('keydown.npp-focus-trap', (e) => {
                if (e.key !== 'Tab') return;

                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable[0]) {
                        lastFocusable.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusable[0]) {
                        firstFocusable.focus();
                        e.preventDefault();
                    }
                }
            });
        }

        releaseFocus() {
            $(document).off('keydown.npp-focus-trap');
        }

        // Publiczne API dla deweloperów
        forceShow() {
            if (this.timers.show) {
                clearTimeout(this.timers.show);
            }
            this.showPopup();
        }

        forceHide() {
            this.closePopup(true);
        }

        updateConfig(newConfig) {
            this.config = { ...this.config, ...newConfig };
        }
    }

    // Inicjalizacja i udostępnienie globalnie
    const popup = new NewsletterPopup();

    // Udostępnij API dla deweloperów
    window.NPP = {
        show: () => popup.forceShow(),
        hide: () => popup.forceHide(),
        config: (newConfig) => popup.updateConfig(newConfig)
    };

})(jQuery);