<?php
namespace NewsletterPopupPro\Ajax;

use NewsletterPopupPro\Repositories\SubscriberRepository;
use NewsletterPopupPro\Services\CookieManager;
use NewsletterPopupPro\Services\EmailValidator;
use NewsletterPopupPro\Models\Subscriber;

class SubscriptionHandler {
    private $repository;
    private $cookieManager;
    private $validator;

    public function __construct(
        SubscriberRepository $repository,
        CookieManager $cookieManager,
        EmailValidator $validator
    ) {
        $this->repository = $repository;
        $this->cookieManager = $cookieManager;
        $this->validator = $validator;
    }

    public function init() {
        add_action('wp_ajax_npp_subscribe', [$this, 'handleSubscription']);
        add_action('wp_ajax_nopriv_npp_subscribe', [$this, 'handleSubscription']);
    }

    public function handleSubscription() {
        // Weryfikacja tokenu bezpieczeństwa (nonce)
        if (!check_ajax_referer('npp_subscribe', 'nonce', false)) {
            wp_send_json_error(['message' => __('Błąd bezpieczeństwa.', 'newsletter-popup-pro')]);
        }

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        // Walidacja adresu email
        $validation = $this->validator->validate($email);
        if (!$validation['valid']) {
            wp_send_json_error(['message' => $validation['message']]);
        }

        // Sprawdź czy email już istnieje w bazie danych
        if ($this->repository->emailExists($email)) {
            // Ustaw ciasteczko mimo że użytkownik już jest zapisany
            $this->cookieManager->setSubscribedCookie($email);
            wp_send_json_success(['message' => __('Jesteś już zapisany do newslettera.', 'newsletter-popup-pro')]);
        }

        // Utwórz nowy obiekt subskrybenta
        $subscriber = new Subscriber(
            $email,
            $this->getUserIP(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        );

        // Zapisz subskrybenta do bazy danych
        if ($this->repository->create($subscriber)) {
            // Ustaw ciasteczko potwierdzające subskrypcję
            $this->cookieManager->setSubscribedCookie($email);

            // Oznacz zalogowanego użytkownika jako subskrybenta
            if (is_user_logged_in()) {
                $this->cookieManager->markUserAsSubscribed(get_current_user_id());
            }

            // Hook dla dodatkowych integracji
            do_action('npp_subscriber_added', $email);

            wp_send_json_success([
                'message' => __('Dziękujemy za zapisanie się do newslettera!', 'newsletter-popup-pro')
            ]);
        }

        wp_send_json_error(['message' => __('Wystąpił błąd. Spróbuj ponownie.', 'newsletter-popup-pro')]);
    }

    /**
     * Pobiera adres IP użytkownika z różnych nagłówków HTTP
     */
    private function getUserIP() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'HTTP_X_REAL_IP'];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = filter_var($_SERVER[$key], FILTER_VALIDATE_IP);
                if ($ip !== false) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}