<?php
namespace NewsletterPopupPro\Services;

use NewsletterPopupPro\Repositories\SubscriberRepository;
use NewsletterPopupPro\Models\Subscriber;

class BulkImporter {
    private $repository;
    private $validator;
    private $errors = [];
    private $imported = 0;
    private $skipped = 0;

    public function __construct(
        SubscriberRepository $repository,
        EmailValidator $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * Importuje subskrybentów z pliku CSV
     */
    public function importFromCSV($file_path) {
        // Sprawdź czy plik istnieje i można go odczytać
        if (!file_exists($file_path) || !is_readable($file_path)) {
            $this->errors[] = __('Plik nie istnieje lub nie można go odczytać.', 'newsletter-popup-pro');
            return false;
        }

        $handle = fopen($file_path, 'r');
        if (!$handle) {
            $this->errors[] = __('Nie można otworzyć pliku.', 'newsletter-popup-pro');
            return false;
        }

        // Automatycznie wykryj separator CSV
        $separator = $this->detectSeparator($file_path);

        // Sprawdź czy pierwsza linia to nagłówek
        $firstLine = fgetcsv($handle, 0, $separator);
        if ($this->isHeaderRow($firstLine)) {
            // Pierwsza linia to nagłówek, kontynuuj od następnej
        } else {
            // Pierwsza linia to dane, cofnij wskaźnik
            rewind($handle);
        }

        // Przetwarzaj każdy wiersz pliku CSV
        while (($data = fgetcsv($handle, 0, $separator)) !== false) {
            $this->processRow($data);
        }

        fclose($handle);

        return [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'errors' => $this->errors
        ];
    }

    /**
     * Automatycznie wykrywa separator CSV
     */
    private function detectSeparator($file_path) {
        $delimiters = [';', ',', "\t", '|'];
        $handle = fopen($file_path, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        return array_search(max($counts), $counts);
    }

    /**
     * Sprawdza czy wiersz zawiera nagłówki
     */
    private function isHeaderRow($row) {
        if (empty($row)) {
            return false;
        }

        // Sprawdź czy pierwszy element wygląda jak adres email
        $firstField = trim($row[0]);
        return !filter_var($firstField, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Przetwarza pojedynczy wiersz CSV
     */
    private function processRow($data) {
        if (empty($data[0])) {
            return;
        }

        $email = trim($data[0]);

        // Walidacja adresu email
        $validation = $this->validator->validate($email);
        if (!$validation['valid']) {
            $this->errors[] = sprintf(
                __('Wiersz %d: %s', 'newsletter-popup-pro'),
                $this->imported + $this->skipped + 1,
                $validation['message']
            );
            $this->skipped++;
            return;
        }

        // Sprawdź czy email już istnieje w bazie
        if ($this->repository->emailExists($email)) {
            $this->skipped++;
            return;
        }

        // Dodaj nowego subskrybenta
        $subscriber = new Subscriber($email, null, 'CSV Import');
        if ($this->repository->create($subscriber)) {
            $this->imported++;
        } else {
            $this->skipped++;
        }
    }

    // Metody dostępowe do statystyk importu
    public function getImported() {
        return $this->imported;
    }

    public function getSkipped() {
        return $this->skipped;
    }

    public function getErrors() {
        return $this->errors;
    }
}
