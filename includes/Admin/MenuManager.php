<?php
namespace NewsletterPopupPro\Admin;

use NewsletterPopupPro\Repositories\SubscriberRepository;

class MenuManager {
    private $repository;

    public function __construct(SubscriberRepository $repository) {
        $this->repository = $repository;
    }

    public function init() {
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_action('admin_action_npp_delete_subscriber', [$this, 'handleDelete']);
        add_action('admin_action_npp_export_csv', [$this, 'handleExportCSV']);
    }

    public function addMenuPages() {
        add_menu_page(
            __('Newsletter Subscribers', 'newsletter-popup-pro'),
            __('Newsletter', 'newsletter-popup-pro'),
            'manage_options',
            'npp-subscribers',
            [$this, 'renderSubscribersPage'],
            'dashicons-email-alt',
            30
        );
    }

    public function renderSubscribersPage() {
        // Pobierz numer strony z parametrów URL
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        // Pobierz subskrybentów z bazy danych
        $subscribers = $this->repository->getAll($per_page, $offset);
        $total = $this->repository->count();
        $total_pages = ceil($total / $per_page);

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Subskrybenci newslettera', 'newsletter-popup-pro'); ?></h1>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Subskrybent został usunięty.', 'newsletter-popup-pro'); ?></p>
                </div>
            <?php endif; ?>

            <p class="description">
                <?php echo sprintf(
                    esc_html__('Łącznie subskrybentów: %d', 'newsletter-popup-pro'),
                    $total
                ); ?>
            </p>

            <?php if (empty($subscribers)): ?>
                <p><?php esc_html_e('Brak subskrybentów.', 'newsletter-popup-pro'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'newsletter-popup-pro'); ?></th>
                        <th><?php esc_html_e('Email', 'newsletter-popup-pro'); ?></th>
                        <th><?php esc_html_e('Adres IP', 'newsletter-popup-pro'); ?></th>
                        <th><?php esc_html_e('Data zapisania', 'newsletter-popup-pro'); ?></th>
                        <th><?php esc_html_e('Akcje', 'newsletter-popup-pro'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <td><?php echo esc_html($subscriber->getId()); ?></td>
                            <td><?php echo esc_html($subscriber->getEmail()); ?></td>
                            <td><?php echo esc_html($subscriber->getIpAddress()); ?></td>
                            <td><?php echo esc_html($subscriber->getCreatedAt()); ?></td>
                            <td>
                                <a href="<?php echo esc_url($this->getDeleteUrl($subscriber->getId())); ?>"
                                   class="button button-small"
                                   onclick="return confirm('<?php esc_attr_e('Czy na pewno chcesz usunąć tego subskrybenta?', 'newsletter-popup-pro'); ?>');">
                                    <?php esc_html_e('Usuń', 'newsletter-popup-pro'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links([
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $page
                            ]);
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="npp-export">
                    <h3><?php esc_html_e('Eksport', 'newsletter-popup-pro'); ?></h3>
                    <p>
                        <a href="<?php echo esc_url(wp_nonce_url(
                            add_query_arg('action', 'npp_export_csv', admin_url('admin.php')),
                            'npp_export_csv'
                        )); ?>"
                           class="button button-primary">
                            <?php esc_html_e('Eksportuj do CSV', 'newsletter-popup-pro'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function handleDelete() {
        // Sprawdź uprawnienia użytkownika
        if (!current_user_can('manage_options')) {
            wp_die(__('Brak uprawnień.', 'newsletter-popup-pro'));
        }

        $id = isset($_GET['subscriber']) ? intval($_GET['subscriber']) : 0;

        // Sprawdź nonce i usuń subskrybenta
        if ($id && check_admin_referer('npp_delete_' . $id)) {
            $this->repository->delete($id);
            wp_redirect(add_query_arg('deleted', '1', admin_url('admin.php?page=npp-subscribers')));
            exit;
        }

        // Przekieruj z powrotem do listy subskrybentów
        wp_redirect(admin_url('admin.php?page=npp-subscribers'));
        exit;
    }

    private function getDeleteUrl($id) {
        return wp_nonce_url(
            add_query_arg([
                'action' => 'npp_delete_subscriber',
                'subscriber' => $id
            ], admin_url('admin.php')),
            'npp_delete_' . $id
        );
    }

    public function handleExportCSV() {
        // Sprawdź uprawnienia użytkownika
        if (!current_user_can('manage_options')) {
            wp_die(__('Brak uprawnień.', 'newsletter-popup-pro'));
        }

        // Sprawdź nonce dla bezpieczeństwa
        if (!check_admin_referer('npp_export_csv')) {
            wp_die(__('Błąd bezpieczeństwa.', 'newsletter-popup-pro'));
        }

        // Uruchom eksport do pliku CSV
        $exporter = new \NewsletterPopupPro\Services\CSVExporter($this->repository);
        $exporter->export();
    }
}