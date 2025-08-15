<?php
namespace NewsletterPopupPro\Repositories;

use NewsletterPopupPro\Core\Database;
use NewsletterPopupPro\Models\Subscriber;

class SubscriberRepository {
    private $db;
    private $table;

    public function __construct(Database $db) {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $db->getTableName();
    }

    /**
     * Zapisuje nowego subskrybenta do bazy danych
     */
    public function create(Subscriber $subscriber) {
        return $this->db->insert(
            $this->table,
            $subscriber->toArray(),
            ['%s', '%s', '%s']
        );
    }

    /**
     * Znajduje subskrybenta po adresie email
     */
    public function findByEmail($email) {
        $query = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE email = %s",
            $email
        );

        $result = $this->db->get_row($query, ARRAY_A);

        return $result ? Subscriber::fromArray($result) : null;
    }

    /**
     * Pobiera listę subskrybentów z paginacją
     */
    public function getAll($limit = 20, $offset = 0) {
        $query = $this->db->prepare(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        );

        $results = $this->db->get_results($query, ARRAY_A);

        return array_map(function($row) {
            return Subscriber::fromArray($row);
        }, $results);
    }

    /**
     * Zlicza wszystkich subskrybentów
     */
    public function count() {
        return $this->db->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * Usuwa subskrybenta z bazy danych
     */
    public function delete($id) {
        return $this->db->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Sprawdza czy email już istnieje w bazie
     */
    public function emailExists($email) {
        $query = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE email = %s",
            $email
        );

        return (bool) $this->db->get_var($query);
    }

    /**
     * Pobiera wszystkich subskrybentów do eksportu
     */
    public function getAllForExport() {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $results = $this->db->get_results($query, ARRAY_A);

        return array_map(function($row) {
            return Subscriber::fromArray($row);
        }, $results);
    }
}