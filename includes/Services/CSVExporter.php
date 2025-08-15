<?php
namespace NewsletterPopupPro\Services;

use NewsletterPopupPro\Repositories\SubscriberRepository;

class CSVExporter {
    private $repository;

    public function __construct(SubscriberRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Eksportuje subskrybentów do pliku CSV
     */
    public function export() {
        // Ustaw nagłówki HTTP dla pobrania pliku
        $filename = 'newsletter-subscribers-' . date('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');

        // Otwórz strumień wyjściowy do przeglądarki
        $output = fopen('php://output', 'w');

        // Nagłówki kolumn CSV (użyj przecinka jako separatora)
        $headers = [
            'ID',
            'Email',
            'Adres IP',
            'User Agent',
            'Data zapisania'
        ];

        // Zapisz nagłówki do pliku CSV
        fputcsv($output, $headers);

        // Pobierz wszystkich subskrybentów (używamy dużego limitu)
        $subscribers = $this->repository->getAll(100000, 0);

        // Zapisz dane subskrybentów do CSV
        if (!empty($subscribers)) {
            foreach ($subscribers as $subscriber) {
                $row = [
                    $subscriber->getId(),
                    $subscriber->getEmail(),
                    $subscriber->getIpAddress() ?: '-',
                    $subscriber->getUserAgent() ?: '-',
                    $subscriber->getCreatedAt()
                ];

                fputcsv($output, $row);
            }
        } else {
            // Jeśli brak subskrybentów, dodaj informacyjny wiersz
            fputcsv($output, ['Brak subskrybentów']);
        }

        // Zamknij strumień i zakończ wykonanie
        fclose($output);
        exit;
    }
}