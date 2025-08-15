<?php
namespace NewsletterPopupPro\Frontend;

use NewsletterPopupPro\Services\CookieManager;
use NewsletterPopupPro\Services\PopupDisplayManager;

class PopupRenderer {
    private $cookieManager;

    public function __construct(CookieManager $cookieManager) {
        $this->cookieManager = $cookieManager;
    }

    public function init() {
        add_action('wp_footer', [$this, 'renderPopup']);
    }

    /**
     * Renderuje HTML popup-a w stopce strony
     */
    public function renderPopup() {
        // Sprawdź czy popup powinien być wyświetlony
        if (!$this->cookieManager->shouldShowPopup()) {
            return;
        }

        // Sprawdź czy popup powinien być wyświetlony na tej stronie
        if (!PopupDisplayManager::shouldDisplayPopup()) {
            return;
        }

        // Pobierz ustawienia dla dostosowania treści
        $settings = \NewsletterPopupPro\Admin\SettingsManager::getPopupSettings();

        ?>
        <div id="npp-popup-overlay" class="npp-popup-overlay" aria-hidden="true" role="dialog" aria-labelledby="npp-popup-title" aria-describedby="npp-popup-description">
            <div class="npp-popup">
                <button class="npp-close" aria-label="<?php esc_attr_e('Zamknij popup', 'newsletter-popup-pro'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>

                <div class="npp-content">
                    <h2 id="npp-popup-title"><?php esc_html_e('Zapisz się do newslettera', 'newsletter-popup-pro'); ?></h2>
                    <p id="npp-popup-description"><?php esc_html_e('Bądź na bieżąco z najnowszymi wiadomościami!', 'newsletter-popup-pro'); ?></p>

                    <form id="npp-subscribe-form" class="npp-form">
                        <div class="npp-form-group">
                            <label for="npp-email" class="screen-reader-text"><?php esc_html_e('Adres email', 'newsletter-popup-pro'); ?></label>
                            <input
                                    type="email"
                                    id="npp-email"
                                    name="email"
                                    placeholder="<?php esc_attr_e('Twój adres e-mail', 'newsletter-popup-pro'); ?>"
                                    required
                                    class="npp-input"
                                    autocomplete="email"
                            >
                        </div>

                        <button type="submit" class="npp-submit">
                            <?php esc_html_e('Zapisz się', 'newsletter-popup-pro'); ?>
                        </button>

                        <div class="npp-message" role="alert" aria-live="polite"></div>
                    </form>

                    <p class="npp-privacy">
                        <?php esc_html_e('Możesz wypisać się w każdej chwili.', 'newsletter-popup-pro'); ?>
                    </p>

                    <?php if ($settings['hide_after_email']): ?>
                        <p class="npp-hint" style="font-size: 12px; color: #999; margin-top: 10px;">
                            <?php esc_html_e('💡 Popup zniknie automatycznie po wpisaniu prawidłowego adresu email', 'newsletter-popup-pro'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
            .screen-reader-text {
                position: absolute !important;
                clip: rect(1px, 1px, 1px, 1px);
                width: 1px !important;
                height: 1px !important;
                overflow: hidden;
            }

            .npp-hint {
                text-align: center;
                font-style: italic;
                opacity: 0.8;
            }
        </style>
        <?php
    }
}