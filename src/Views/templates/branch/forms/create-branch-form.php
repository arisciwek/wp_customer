<?php
/**
 * Create Branch Form Template
 *
 * @package     WP_Customer
 * @subpackage  Views/Templates/Branch/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-customer/src/Views/templates/branch/forms/create-branch-form.php
 *
 * Description: Form modal untuk menambah cabang baru.
 *              Includes input validation, error handling,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan komponen toast notification.
 *
 * Changelog:
 * 1.0.0 - 2024-12-10
 * - Initial release
 * - Added form structure
 * - Added validation markup
 * - Added AJAX integration
 */
defined('ABSPATH') || exit;
?>

<div id="create-branch-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3><?php _e('Tambah Cabang', 'wp-customer'); ?></h3>
            <button type="button" class="modal-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="create-branch-form" method="post">
            <?php wp_nonce_field('wp_customer_nonce'); ?>
            <input type="hidden" name="customer_id" id="customer_id">

            <div class="modal-content">
<<<<<<< HEAD
                <div class="branch-form-group">
=======
                <div class="wp-customer-form-group">
>>>>>>> 5cf836118009a5ac1dadec359c758f0538598b1e
                    <label for="branch-code" class="required-field">
                        <?php _e('Kode Cabang', 'wp-customer'); ?>
                    </label>
                    <input type="text"
                           id="branch-code"
                           name="code"
                           class="small-text"
                           maxlength="4"
                           pattern="\d{4}"
                           required>
                    <p class="description">
                        <?php _e('Masukkan 4 digit angka', 'wp-customer'); ?>
                    </p>
                </div>
<<<<<<< HEAD
                <div class="branch-form-group">
=======
                <div class="wp-customer-form-group">
>>>>>>> 5cf836118009a5ac1dadec359c758f0538598b1e
                    <label for="branch-name" class="required-field">
                        <?php _e('Nama Cabang', 'wp-customer'); ?>
                    </label>
                    <input type="text"
                           id="branch-name"
                           name="name"
                           class="regular-text"
                           maxlength="100"
                           required>
                </div>

<<<<<<< HEAD
                <div class="branch-form-group">
=======
                <div class="wp-customer-form-group">
>>>>>>> 5cf836118009a5ac1dadec359c758f0538598b1e
                    <label for="branch-type" class="required-field">
                        <?php _e('Tipe', 'wp-customer'); ?>
                    </label>
                    <select id="branch-type" name="type" required>
                        <option value=""><?php _e('Pilih Tipe', 'wp-customer'); ?></option>
                        <option value="kabupaten"><?php _e('Kabupaten', 'wp-customer'); ?></option>
                        <option value="kota"><?php _e('Kota', 'wp-customer'); ?></option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
<<<<<<< HEAD
                <div class="branch-form-actions">
=======
                <div class="wp-customer-form-actions">
>>>>>>> 5cf836118009a5ac1dadec359c758f0538598b1e
                    <button type="button" class="button cancel-create">
                        <?php _e('Batal', 'wp-customer'); ?>
                    </button>
                    <button type="submit" class="button button-primary">
                        <?php _e('Simpan', 'wp-customer'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
    </div>
</div>
