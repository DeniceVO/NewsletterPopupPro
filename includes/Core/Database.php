<?php
namespace NewsletterPopupPro\Core;

class Database {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'npp_subscribers';
    }

    public function getTableName() {
        return $this->table_name;
    }

    /**
     * Tworzy tabele bazy danych dla wtyczki
     */
    public function createTables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // SQL do utworzenia tabeli subskrybentów
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}