<?php
namespace NewsletterPopupPro\Models;

class Subscriber {
    private $id;
    private $email;
    private $ip_address;
    private $user_agent;
    private $created_at;

    public function __construct($email, $ip_address = null, $user_agent = null) {
        $this->email = sanitize_email($email);
        $this->ip_address = $ip_address;
        $this->user_agent = $user_agent;
    }

    /**
     * Tworzy obiekt Subscriber z tablicy danych
     */
    public static function fromArray(array $data) {
        $subscriber = new self($data['email'], $data['ip_address'], $data['user_agent']);
        $subscriber->id = $data['id'];
        $subscriber->created_at = $data['created_at'];
        return $subscriber;
    }

    /**
     * Konwertuje obiekt do tablicy (do zapisu w bazie)
     */
    public function toArray() {
        return [
            'email' => $this->email,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent
        ];
    }

    // Metody dostępowe (gettery)
    public function getId() { return $this->id; }
    public function getEmail() { return $this->email; }
    public function getIpAddress() { return $this->ip_address; }
    public function getUserAgent() { return $this->user_agent; }
    public function getCreatedAt() { return $this->created_at; }
}
