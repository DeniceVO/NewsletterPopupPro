<?php
namespace NewsletterPopupPro\Core;

use NewsletterPopupPro\Admin\SettingsManager;
use NewsletterPopupPro\Services\PopupDisplayManager;

class AssetsManager {
    public function init() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Ładuje zasoby CSS i JS dla frontendu
     */
    public function enqueueFrontendAssets() {
        // Sprawdź czy popup powinien być wyświetlony na tej stronie
        if (!PopupDisplayManager::shouldDisplayPopup()) {
            return;
        }

        // Ładowanie stylów CSS
        wp_enqueue_style(
            'npp-frontend',
            NPP_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            NPP_VERSION
        );

        // Ładowanie skryptów JavaScript
        wp_enqueue_script(
            'npp-frontend',
            NPP_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            NPP_VERSION,
            true
        );

        // Przekazanie danych do JavaScript (lokalizacja)
        wp_localize_script('npp-frontend', 'npp_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('npp_subscribe'),
            'messages' => [
                'success' => __('Dziękujemy za zapisanie się do newslettera!', 'newsletter-popup-pro'),
                'error' => __('Wystąpił błąd. Spróbuj ponownie.', 'newsletter-popup-pro'),
                'invalid_email' => __('Podaj prawidłowy adres email.', 'newsletter-popup-pro'),
                'already_subscribed' => __('Ten adres email jest już zapisany.', 'newsletter-popup-pro')
            ]
        ]);

        // Przekazanie konfiguracji popup-a do JavaScript
        wp_localize_script('npp-frontend', 'npp_popup_config', PopupDisplayManager::getJSConfig());
    }

    /**
     * Ładuje zasoby CSS dla panelu administracyjnego
     */
    public function enqueueAdminAssets($hook) {
        // Ładuj tylko na stronach wtyczki
        if (strpos($hook, 'npp-subscribers') === false && strpos($hook, 'npp-settings') === false) {
            return;
        }

        wp_enqueue_style(
            'npp-admin',
            NPP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            NPP_VERSION
        );

        // Dodatkowe skrypty dla strony ustawień
        if (strpos($hook, 'npp-settings') !== false) {
            wp_enqueue_script(
                'npp-admin-settings',
                NPP_PLUGIN_URL . 'assets/js/admin-settings.js',
                ['jquery'],
                NPP_VERSION,
                true
            );
        }
    }
}