// assets/js/admin-settings.js

(function($) {
    'use strict';

    $(document).ready(function() {
        const $delayField = $('input[name="npp_popup_delay"]');
        const $durationField = $('input[name="npp_popup_duration"]');
        const $hideAfterEmailField = $('input[name="npp_hide_after_email"]');
        const $pagesField = $('select[name="npp_popup_pages"]');

        // Podgląd ustawień w czasie rzeczywistym
        initSettingsPreview();

        // Walidacja pól
        initFieldValidation();

        // Podpowiedzi kontekstowe
        initTooltips();
    });

    /**
     * Inicjalizuje podgląd ustawień
     */
    function initSettingsPreview() {
        const $previewContainer = createPreviewContainer();

        // Aktualizuj podgląd przy każdej zmianie
        $('input, select').on('change input', updatePreview);

        // Pierwsza aktualizacja
        updatePreview();

        function updatePreview() {
            const delay = parseInt($('input[name="npp_popup_delay"]').val()) || 0;
            const duration = parseInt($('input[name="npp_popup_duration"]').val()) || 0;
            const hideAfterEmail = $('input[name="npp_hide_after_email"]').is(':checked');
            const pages = $('select[name="npp_popup_pages"]').val();
            const disableMobile = $('input[name="npp_disable_mobile"]').is(':checked');

            let previewText = '<strong>Podgląd konfiguracji:</strong><br>';
            previewText += `📅 Popup pojawi się po <strong>${delay} sekundach</strong><br>`;

            if (duration > 0) {
                previewText += `⏰ Automatycznie zniknie po <strong>${duration} sekundach</strong><br>`;
            } else {
                previewText += `⏰ Nie zniknie automatycznie<br>`;
            }

            if (hideAfterEmail) {
                previewText += `✉️ Zniknie po wpisaniu prawidłowego emaila<br>`;
            }

            previewText += `📱 Urządzenia mobilne: ${disableMobile ? '<strong>wyłączone</strong>' : '<strong>włączone</strong>'}<br>`;

            const pageTexts = {
                'all': 'wszystkie strony',
                'home': 'tylko strona główna',
                'posts': 'tylko wpisy',
                'pages': 'tylko strony statyczne'
            };

            previewText += `🎯 Wyświetlanie: <strong>${pageTexts[pages] || pages}</strong>`;

            $previewContainer.html(previewText);
        }

        function createPreviewContainer() {
            const $container = $('<div>', {
                class: 'npp-settings-preview',
                style: `
                    background: #e7f3ff;
                    border: 1px solid #0073aa;
                    border-radius: 4px;
                    padding: 15px;
                    margin: 20px 0;
                    font-size: 14px;
                    line-height: 1.6;
                `
            });

            $('form').after($container);
            return $container;
        }
    }

    /**
     * Inicjalizuje walidację pól
     */
    function initFieldValidation() {
        // Walidacja opóźnienia
        $('input[name="npp_popup_delay"]').on('input', function() {
            const value = parseInt($(this).val());
            const $field = $(this);

            if (value < 0 || value > 300) {
                showFieldError($field, 'Opóźnienie musi być między 0 a 300 sekundami');
            } else {
                clearFieldError($field);
            }
        });

        // Walidacja czasu trwania
        $('input[name="npp_popup_duration"]').on('input', function() {
            const value = parseInt($(this).val());
            const $field = $(this);

            if (value < 0 || value > 3600) {
                showFieldError($field, 'Czas trwania musi być między 0 a 3600 sekundami');
            } else {
                clearFieldError($field);
            }
        });

        // Logiczne sprawdzenie czasu
        $('input[name="npp_popup_delay"], input[name="npp_popup_duration"]').on('input', function() {
            const delay = parseInt($('input[name="npp_popup_delay"]').val()) || 0;
            const duration = parseInt($('input[name="npp_popup_duration"]').val()) || 0;

            if (duration > 0 && duration <= delay) {
                showFormWarning('⚠️ Uwaga: Czas wyświetlania jest krótszy lub równy opóźnieniu. Popup może się nie pokazać lub zniknąć natychmiast.');
            } else {
                clearFormWarning();
            }
        });

        function showFieldError($field, message) {
            clearFieldError($field);

            const $error = $('<div>', {
                class: 'npp-field-error',
                text: message,
                style: 'color: #d63638; font-size: 12px; margin-top: 5px;'
            });

            $field.after($error);
            $field.css('border-color', '#d63638');
        }

        function clearFieldError($field) {
            $field.next('.npp-field-error').remove();
            $field.css('border-color', '');
        }

        function showFormWarning(message) {
            clearFormWarning();

            const $warning = $('<div>', {
                class: 'npp-form-warning notice notice-warning',
                html: `<p>${message}</p>`,
                style: 'margin: 15px 0;'
            });

            $('form').before($warning);
        }

        function clearFormWarning() {
            $('.npp-form-warning').remove();
        }
    }

    /**
     * Inicjalizuje podpowiedzi kontekstowe
     */
    function initTooltips() {
        // Dodaj przyciski pomocy
        addHelpButton('input[name="npp_popup_delay"]',
            'Czas w sekundach po załadowaniu strony, po którym pojawi się popup. ' +
            'Krótsze opóźnienie = więcej konwersji, ale może irytować użytkowników. ' +
            'Zalecane: 3-10 sekund.'
        );

        addHelpButton('input[name="npp_popup_duration"]',
            'Maksymalny czas wyświetlania popup-a. Po tym czasie popup automatycznie zniknie. ' +
            '0 oznacza brak limitu czasowego. ' +
            'Zalecane: 0 (bez limitu) lub 30-60 sekund.'
        );

        addHelpButton('input[name="npp_hide_after_email"]',
            'Gdy włączone, popup zniknie natychmiast po wpisaniu prawidłowego adresu email, ' +
            'jeszcze przed wysłaniem formularza. Poprawia doświadczenie użytkownika.'
        );

        addHelpButton('select[name="npp_popup_pages"]',
            'Wybierz na jakich stronach ma się pokazywać popup. ' +
            '"Wszystkie strony" = wszędzie, "Strona główna" = tylko na głównej, itd.'
        );

        function addHelpButton($field, helpText) {
            const $field = $($field);
            const $helpButton = $('<button>', {
                type: 'button',
                class: 'button button-small npp-help-button',
                text: '?',
                style: 'margin-left: 5px; width: 24px; height: 24px; font-weight: bold;',
                title: 'Kliknij aby zobaczyć pomoc'
            });

            $field.after($helpButton);

            $helpButton.on('click', function(e) {
                e.preventDefault();
                showTooltip($(this), helpText);
            });
        }

        function showTooltip($button, text) {
            // Usuń istniejące tooltips
            $('.npp-tooltip').remove();

            const $tooltip = $('<div>', {
                class: 'npp-tooltip',
                html: text,
                style: `
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    max-width: 300px;
                    z-index: 1000;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                    line-height: 1.4;
                `
            });

            $('body').append($tooltip);

            // Pozycjonuj tooltip
            const buttonOffset = $button.offset();
            $tooltip.css({
                top: buttonOffset.top - $tooltip.outerHeight() - 5,
                left: buttonOffset.left - $tooltip.outerWidth() / 2 + $button.outerWidth() / 2
            });

            // Automatyczne ukrycie po 5 sekundach
            setTimeout(() => {
                $tooltip.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            // Ukryj przy kliknięciu gdziekolwiek
            $(document).one('click', function() {
                $tooltip.remove();
            });
        }
    }

})(jQuery);