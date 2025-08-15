<?php
namespace NewsletterPopupPro\Admin;

class SettingsManager {
    private $option_group = 'npp_settings';
    private $settings_page = 'npp-settings';

    public function init() {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Dodaje stronę ustawień do menu administracyjnego
     */
    public function addSettingsPage() {
        add_submenu_page(
            'npp-subscribers',
            __('Ustawienia Popup', 'newsletter-popup-pro'),
            __('Ustawienia', 'newsletter-popup-pro'),
            'manage_options',
            $this->settings_page,
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Rejestruje ustawienia wtyczki
     */
    public function registerSettings() {
        // Grupa ustawień popup-a
        add_settings_section(
            'npp_popup_settings',
            __('Ustawienia wyświetlania popup-a', 'newsletter-popup-pro'),
            [$this, 'renderPopupSectionDescription'],
            $this->settings_page
        );

        // Opóźnienie wyświetlenia popup-a
        add_settings_field(
            'npp_popup_delay',
            __('Opóźnienie wyświetlenia (sekundy)', 'newsletter-popup-pro'),
            [$this, 'renderDelayField'],
            $this->settings_page,
            'npp_popup_settings'
        );

        // Czas przez który popup ma być widoczny
        add_settings_field(
            'npp_popup_duration',
            __('Maksymalny czas wyświetlania (sekundy)', 'newsletter-popup-pro'),
            [$this, 'renderDurationField'],
            $this->settings_page,
            'npp_popup_settings'
        );

        // Wyświetlanie na konkretnych stronach
        add_settings_field(
            'npp_popup_pages',
            __('Strony do wyświetlania', 'newsletter-popup-pro'),
            [$this, 'renderPagesField'],
            $this->settings_page,
            'npp_popup_settings'
        );

        // Wyłączenie popup-a na urządzeniach mobilnych
        add_settings_field(
            'npp_disable_mobile',
            __('Wyłącz na urządzeniach mobilnych', 'newsletter-popup-pro'),
            [$this, 'renderMobileField'],
            $this->settings_page,
            'npp_popup_settings'
        );

        // Zachowanie po wpisaniu emaila
        add_settings_field(
            'npp_hide_after_email',
            __('Ukryj popup po wpisaniu emaila', 'newsletter-popup-pro'),
            [$this, 'renderHideAfterEmailField'],
            $this->settings_page,
            'npp_popup_settings'
        );

        // Rejestracja opcji
        register_setting($this->option_group, 'npp_popup_delay', [
            'type' => 'integer',
            'default' => 5,
            'sanitize_callback' => [$this, 'sanitizeDelay']
        ]);

        register_setting($this->option_group, 'npp_popup_duration', [
            'type' => 'integer',
            'default' => 0,
            'sanitize_callback' => [$this, 'sanitizeDuration']
        ]);

        register_setting($this->option_group, 'npp_popup_pages', [
            'type' => 'string',
            'default' => 'all',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting($this->option_group, 'npp_disable_mobile', [
            'type' => 'boolean',
            'default' => false
        ]);

        register_setting($this->option_group, 'npp_hide_after_email', [
            'type' => 'boolean',
            'default' => true
        ]);
    }

    /**
     * Renderuje stronę ustawień
     */
    public function renderSettingsPage() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Ustawienia Newsletter Popup', 'newsletter-popup-pro'); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_group);
                do_settings_sections($this->settings_page);
                submit_button();
                ?>
            </form>

            <div class="npp-settings-info" style="margin-top: 30px; padding: 15px; background: #f1f1f1; border-radius: 5px;">
                <h3><?php esc_html_e('Informacje o ustawieniach', 'newsletter-popup-pro'); ?></h3>
                <ul>
                    <li><strong><?php esc_html_e('Opóźnienie:', 'newsletter-popup-pro'); ?></strong> <?php esc_html_e('Czas w sekundach po załadowaniu strony, po którym pojawi się popup.', 'newsletter-popup-pro'); ?></li>
                    <li><strong><?php esc_html_e('Maksymalny czas:', 'newsletter-popup-pro'); ?></strong> <?php esc_html_e('Czas po którym popup automatycznie zniknie (0 = nie znika automatycznie).', 'newsletter-popup-pro'); ?></li>
                    <li><strong><?php esc_html_e('Ukryj po emailu:', 'newsletter-popup-pro'); ?></strong> <?php esc_html_e('Popup zniknie natychmiast po wpisaniu prawidłowego adresu email (bez wysyłania).', 'newsletter-popup-pro'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    public function renderPopupSectionDescription() {
        echo '<p>' . esc_html__('Skonfiguruj kiedy i jak ma być wyświetlany popup z newsletterem.', 'newsletter-popup-pro') . '</p>';
    }

    public function renderDelayField() {
        $value = get_option('npp_popup_delay', 5);
        ?>
        <input type="number"
               name="npp_popup_delay"
               value="<?php echo esc_attr($value); ?>"
               min="0"
               max="300"
               class="small-text" />
        <p class="description"><?php esc_html_e('Sekundy oczekiwania przed pokazaniem popup-a (0-300)', 'newsletter-popup-pro'); ?></p>
        <?php
    }

    public function renderDurationField() {
        $value = get_option('npp_popup_duration', 0);
        ?>
        <input type="number"
               name="npp_popup_duration"
               value="<?php echo esc_attr($value); ?>"
               min="0"
               max="3600"
               class="small-text" />
        <p class="description"><?php esc_html_e('Maksymalny czas wyświetlania popup-a w sekundach (0 = bez limitu)', 'newsletter-popup-pro'); ?></p>
        <?php
    }

    public function renderPagesField() {
        $value = get_option('npp_popup_pages', 'all');
        ?>
        <select name="npp_popup_pages">
            <option value="all" <?php selected($value, 'all'); ?>><?php esc_html_e('Wszystkie strony', 'newsletter-popup-pro'); ?></option>
            <option value="home" <?php selected($value, 'home'); ?>><?php esc_html_e('Tylko strona główna', 'newsletter-popup-pro'); ?></option>
            <option value="posts" <?php selected($value, 'posts'); ?>><?php esc_html_e('Tylko wpisy', 'newsletter-popup-pro'); ?></option>
            <option value="pages" <?php selected($value, 'pages'); ?>><?php esc_html_e('Tylko strony', 'newsletter-popup-pro'); ?></option>
        </select>
        <?php
    }

    public function renderMobileField() {
        $value = get_option('npp_disable_mobile', false);
        ?>
        <label>
            <input type="checkbox"
                   name="npp_disable_mobile"
                   value="1"
                <?php checked($value); ?> />
            <?php esc_html_e('Nie wyświetlaj popup-a na telefonach i tabletach', 'newsletter-popup-pro'); ?>
        </label>
        <?php
    }

    public function renderHideAfterEmailField() {
        $value = get_option('npp_hide_after_email', true);
        ?>
        <label>
            <input type="checkbox"
                   name="npp_hide_after_email"
                   value="1"
                <?php checked($value); ?> />
            <?php esc_html_e('Ukryj popup natychmiast po wpisaniu prawidłowego adresu email', 'newsletter-popup-pro'); ?>
        </label>
        <p class="description"><?php esc_html_e('Popup zniknie gdy użytkownik wpisze prawidłowy email, jeszcze przed wysłaniem formularza.', 'newsletter-popup-pro'); ?></p>
        <?php
    }

    /**
     * Walidacja opóźnienia
     */
    public function sanitizeDelay($input) {
        $value = intval($input);
        return max(0, min(300, $value));
    }

    /**
     * Walidacja czasu trwania
     */
    public function sanitizeDuration($input) {
        $value = intval($input);
        return max(0, min(3600, $value));
    }

    /**
     * Pobiera ustawienia popup-a jako tablicę
     */
    public static function getPopupSettings() {
        return [
            'delay' => get_option('npp_popup_delay', 5),
            'duration' => get_option('npp_popup_duration', 0),
            'pages' => get_option('npp_popup_pages', 'all'),
            'disable_mobile' => get_option('npp_disable_mobile', false),
            'hide_after_email' => get_option('npp_hide_after_email', true)
        ];
    }
}
