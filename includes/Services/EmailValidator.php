<?php
namespace NewsletterPopupPro\Services;

class EmailValidator {
    /**
     * Waliduje adres email i zwraca wynik z komunikatem
     */
    public function validate($email) {
        $email = trim($email);

        // Sprawdź czy email nie jest pusty
        if (empty($email)) {
            return ['valid' => false, 'message' => __('Email jest wymagany.', 'newsletter-popup-pro')];
        }

        // Sprawdź format adresu email
        if (!is_email($email)) {
            return ['valid' => false, 'message' => __('Nieprawidłowy format adresu email.', 'newsletter-popup-pro')];
        }

        // Dodatkowa walidacja DNS (sprawdź czy domena istnieje)
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            return ['valid' => false, 'message' => __('Domena email nie istnieje.', 'newsletter-popup-pro')];
        }

        return ['valid' => true];
    }
}