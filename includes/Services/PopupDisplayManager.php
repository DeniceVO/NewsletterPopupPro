<?php
namespace NewsletterPopupPro\Services;

class PopupDisplayManager {
    /**
     * Sprawdza czy popup powinien być wyświetlony na bieżącej stronie
     */
    public static function shouldDisplayPopup() {
        $settings = \NewsletterPopupPro\Admin\SettingsManager::getPopupSettings();

        // Sprawdź urządzenia mobilne
        if ($settings['disable_mobile'] && wp_is_mobile()) {
            return false;
        }

        // Sprawdź typ strony
        switch ($settings['pages']) {
            case 'home':
                return is_front_page();
            case 'posts':
                return is_single() && get_post_type() === 'post';
            case 'pages':
                return is_page();
            case 'all':
            default:
                return true;
        }
    }

    /**
     * Pobiera konfigurację JavaScript dla popup-a
     */
    public static function getJSConfig() {
        $settings = \NewsletterPopupPro\Admin\SettingsManager::getPopupSettings();

        return [
            'delay' => $settings['delay'] * 1000, // konwersja na milisekundy
            'duration' => $settings['duration'] * 1000, // 0 = bez limitu
            'hideAfterEmail' => $settings['hide_after_email']
        ];
    }
}