<?php
namespace NewsletterPopupPro\Services;

class CookieManager {
    private const COOKIE_NAME = 'npp_subscribed';
    private const COOKIE_DURATION = 365 * DAY_IN_SECONDS; // 1 rok

    /**
     * Ustawia ciasteczko potwierdzające subskrypcję
     */
    public function setSubscribedCookie($email) {
        $value = wp_hash($email . wp_salt('secure_auth'));
        setcookie(
            self::COOKIE_NAME,
            $value,
            time() + self::COOKIE_DURATION,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // HttpOnly
        );
        $_COOKIE[self::COOKIE_NAME] = $value;
    }

    /**
     * Sprawdza czy użytkownik ma ciasteczko subskrypcji
     */
    public function hasSubscribedCookie() {
        return isset($_COOKIE[self::COOKIE_NAME]);
    }

    /**
     * Określa czy popup powinien być wyświetlony
     */
    public function shouldShowPopup() {
        // Nie pokazuj jeśli użytkownik ma ciasteczko
        if ($this->hasSubscribedCookie()) {
            return false;
        }

        // Sprawdź czy zalogowany użytkownik już się zapisał
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $meta = get_user_meta($user->ID, 'npp_subscribed', true);
            if ($meta === 'yes') {
                return false;
            }
        }

        return true;
    }

    /**
     * Oznacza użytkownika jako subskrybenta w meta danych
     */
    public function markUserAsSubscribed($user_id) {
        update_user_meta($user_id, 'npp_subscribed', 'yes');
    }
}