<?php
/**
 * Plugin Name: Newsletter Popup Pro
 * Plugin URI: https://danieloportfolio.pl/
 * Description: Profesjonalna wtyczka do zbierania adresów email z popup formularzem
 * Version: 1.0.0
 * Author: Daniel Obuchowicz
 * License: GPL v2 or later
 * Text Domain: newsletter-popup-pro
 */

namespace NewsletterPopupPro;

// Zabezpieczenie przed bezpośrednim dostępem
if (!defined('ABSPATH')) {
    exit;
}

// Definicje stałych wtyczki
define('NPP_VERSION', '1.2.0');
define('NPP_PLUGIN_FILE', __FILE__);
define('NPP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NPP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NPP_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader dla klas wtyczki
spl_autoload_register(function ($class) {
    // Sprawdź czy klasa należy do naszej przestrzeni nazw
    if (strpos($class, 'NewsletterPopupPro\\') !== 0) {
        return;
    }

    // Przekształć namespace na ścieżkę pliku
    $class_file = str_replace(
        ['NewsletterPopupPro\\', '\\'],
        ['', '/'],
        $class
    );

    $file_path = NPP_PLUGIN_DIR . 'includes/' . $class_file . '.php';

    if (file_exists($file_path)) {
        require_once $file_path;
    }
});

/**
 * Główna klasa wtyczki
 */
class NewsletterPopupPro {
    private static $instance = null;
    private $components = [];

    /**
     * Singleton - pobiera instancję wtyczki
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Konstruktor - inicjalizuje wtyczkę
     */
    private function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    /**
     * Inicjalizuje wszystkie komponenty wtyczki
     */
    public function init() {
        // Załaduj tłumaczenia
        load_plugin_textdomain(
            'newsletter-popup-pro',
            false,
            dirname(NPP_PLUGIN_BASENAME) . '/languages'
        );

        // Inicjalizuj bazę danych
        $database = new \NewsletterPopupPro\Core\Database();

        // Inicjalizuj repozytoria
        $subscriberRepository = new \NewsletterPopupPro\Repositories\SubscriberRepository($database);

        // Inicjalizuj serwisy
        $cookieManager = new \NewsletterPopupPro\Services\CookieManager();
        $emailValidator = new \NewsletterPopupPro\Services\EmailValidator();

        // Inicjalizuj komponenty administracyjne
        if (is_admin()) {
            $this->components['menu'] = new \NewsletterPopupPro\Admin\MenuManager($subscriberRepository);
            $this->components['settings'] = new \NewsletterPopupPro\Admin\SettingsManager();

            $this->components['menu']->init();
            $this->components['settings']->init();
        }

        // Inicjalizuj komponenty frontendowe
        if (!is_admin()) {
            $this->components['popup_renderer'] = new \NewsletterPopupPro\Frontend\PopupRenderer($cookieManager);
            $this->components['popup_renderer']->init();
        }

        // Inicjalizuj komponenty działające wszędzie
        $this->components['assets'] = new \NewsletterPopupPro\Core\AssetsManager();
        $this->components['subscription_handler'] = new \NewsletterPopupPro\Ajax\SubscriptionHandler(
            $subscriberRepository,
            $cookieManager,
            $emailValidator
        );

        $this->components['assets']->init();
        $this->components['subscription_handler']->init();

        // Hook dla deweloperów
        do_action('npp_loaded', $this);
    }

    /**
     * Aktywacja wtyczki
     */
    public function activate() {
        // Utwórz tabele bazy danych
        $database = new \NewsletterPopupPro\Core\Database();
        $database->createTables();

        // Ustaw domyślne opcje
        $this->setDefaultOptions();

        // Wyczyść cache
        flush_rewrite_rules();

        // Hook dla deweloperów
        do_action('npp_activated');
    }

    /**
     * Deaktywacja wtyczki
     */
    public function deactivate() {
        // Wyczyść zaplanowane zadania (jeśli jakieś są)
        wp_clear_scheduled_hook('npp_cleanup_subscribers');

        // Wyczyść cache
        flush_rewrite_rules();

        // Hook dla deweloperów
        do_action('npp_deactivated');
    }

    /**
     * Ustawia domyślne opcje wtyczki
     */
    private function setDefaultOptions() {
        $defaults = [
            'npp_popup_delay' => 5,
            'npp_popup_duration' => 0,
            'npp_popup_pages' => 'all',
            'npp_disable_mobile' => false,
            'npp_hide_after_email' => true
        ];

        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }

    /**
     * Pobiera instancję komponentu
     */
    public function getComponent($name) {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }

    /**
     * Sprawdza czy wtyczka jest aktywna
     */
    public static function isActive() {
        return self::$instance !== null;
    }

    /**
     * Pobiera wersję wtyczki
     */
    public static function getVersion() {
        return NPP_VERSION;
    }
}

/**
 * Funkcja pomocnicza do pobierania instancji wtyczki
 */
function npp() {
    return NewsletterPopupPro::getInstance();
}

// Uruchom wtyczkę
NewsletterPopupPro::getInstance();

/**
 * Funkcje pomocnicze dla deweloperów
 */

/**
 * Sprawdza czy użytkownik jest subskrybentem
 */
function npp_is_user_subscribed($email = null) {
    if (!$email && is_user_logged_in()) {
        $user = wp_get_current_user();
        $email = $user->user_email;
    }

    if (!$email) {
        return false;
    }

    $repository = new \NewsletterPopupPro\Repositories\SubscriberRepository(
        new \NewsletterPopupPro\Core\Database()
    );

    return $repository->emailExists($email);
}

/**
 * Wymusza wyświetlenie popup-a (funkcja dla deweloperów)
 */
function npp_force_show_popup() {
    if (!is_admin()) {
        echo '<script>
            if (typeof NPP !== "undefined") {
                NPP.show();
            }
        </script>';
    }
}

/**
 * Ukrywa popup (funkcja dla deweloperów)
 */
function npp_force_hide_popup() {
    if (!is_admin()) {
        echo '<script>
            if (typeof NPP !== "undefined") {
                NPP.hide();
            }
        </script>';
    }
}

/**
 * Pobiera liczbę subskrybentów
 */
function npp_get_subscriber_count() {
    $repository = new \NewsletterPopupPro\Repositories\SubscriberRepository(
        new \NewsletterPopupPro\Core\Database()
    );

    return $repository->count();
}

