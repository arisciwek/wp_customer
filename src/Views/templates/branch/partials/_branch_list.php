<?php
/**
 * Branch List Template
 *
 * @package     WP_Customer
 * @subpackage  Views/Templates/Branch/Partials
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-customer/src/Views/templates/branch/partials/_branch_list.php
 *
 * Description: Template untuk menampilkan daftar cabang.
 *              Includes DataTable, loading states, empty states,
 *              dan action buttons dengan permission checks.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial release
 * - Added loading states
 * - Added empty state messages
 * - Added proper DataTable structure
 */

defined('ABSPATH') || exit;
error_log('=== Debug Branch List Template ===');
error_log('Access variable On Branch Template: ' . print_r($access ?? 'null', true));
error_log('Customer variable On Branch Template: ' . print_r($customer ?? 'null', true));

// Sebelum permission check
error_log('Access type On Branch Template: ' . ($access['access_type'] ?? 'undefined'));
error_log('Can add branch On Branch Template: ' . (current_user_can('add_branch') ? 'yes' : 'no'));
?>

<div id="branch-list" class="tab-content">

    <div class="wp-customer-branch-header">
        <div class="branch-header-title">
            <h3><?php _e('Daftar Cabang', 'wp-customer'); ?></h3>
        </div>

            <div class="branch-header-actions">
                <?php if (($access['access_type'] === 'admin' || 
                           $access['access_type'] === 'owner') && 
                          current_user_can('add_branch')) : ?>
                    <button type="button" class="button button-primary" id="add-branch-btn">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e('Tambah Cabang', 'wp-customer'); ?>
                    </button>
                <?php endif; ?>
            </div>

        <div class="branch-header-actions">
            <?php 
            if (isset($customer) && is_object($customer)) {
                //echo $controller->generateAddBranchButton($customer);
            }
            ?>
        </div>

    </div>

    <div class="wp-customer-branch-content">
        <!-- Loading State -->
        <div class="branch-loading-state" style="display: none;">
            <span class="spinner is-active"></span>
            <p><?php _e('Memuat data...', 'wp-customer'); ?></p>
        </div>

        <!-- Empty State -->
        <div class="empty-state" style="display: none;">
            <div class="empty-state-content">
                <span class="dashicons dashicons-location"></span>
                <h4><?php _e('Belum Ada Data', 'wp-customer'); ?></h4>
                <p>
                    <?php
                    if (current_user_can('add_branch')) {
                        _e('Belum ada cabang yang ditambahkan. Klik tombol "Tambah Cabang" untuk menambahkan data baru.', 'wp-customer');
                    } else {
                        _e('Belum ada cabang yang ditambahkan.', 'wp-customer');
                    }
                    ?>
                </p>
            </div>
        </div>

        <!-- Data Table -->
        <div class="wi-table-container">
            <table id="branch-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th><?php _e('Kode', 'wp-customer'); ?></th>
                        <th><?php _e('Nama', 'wp-customer'); ?></th>
                        <th><?php _e('Admin', 'wp-customer'); ?></th>
                        <th><?php _e('Tipe', 'wp-customer'); ?></th>
                        <th class="text-center no-sort">
                            <?php _e('Aksi', 'wp-customer'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this -->
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Kode', 'wp-customer'); ?></th>
                        <th><?php _e('Nama', 'wp-customer'); ?></th>
                        <th><?php _e('Admin', 'wp-customer'); ?></th>
                        <th><?php _e('Tipe', 'wp-customer'); ?></th>
                        <th><?php _e('Aksi', 'wp-customer'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Error State -->
        <div class="error-state" style="display: none;">
            <div class="error-state-content">
                <span class="dashicons dashicons-warning"></span>
                <h4><?php _e('Gagal Memuat Data', 'wp-customer'); ?></h4>
                <p><?php _e('Terjadi kesalahan saat memuat data. Silakan coba lagi.', 'wp-customer'); ?></p>
                <button type="button" class="button reload-table">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Muat Ulang', 'wp-customer'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Export Buttons (Optional, can be enabled via settings) -->
    <?php if (apply_filters('wp_customer_enable_export', false)): ?>
        <div class="export-actions">
            <button type="button" class="button export-excel">
                <span class="dashicons dashicons-media-spreadsheet"></span>
                <?php _e('Export Excel', 'wp-customer'); ?>
            </button>
            <button type="button" class="button export-pdf">
                <span class="dashicons dashicons-pdf"></span>
                <?php _e('Export PDF', 'wp-customer'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>
