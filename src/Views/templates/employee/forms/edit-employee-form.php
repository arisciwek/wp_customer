<?php
/**
 * Edit Employee Form Template
 *
 * @package     WP_Customer
 * @subpackage  Views/Templates/Employee/Forms
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-customer/src/Views/templates/employee/forms/edit-employee-form.php
 *
 * Description: Form modal untuk mengedit data karyawan.
 *              Includes input validation, error handling,
 *              dan AJAX submission handling.
 *              Terintegrasi dengan komponen toast notification.
 *
 * Changelog:
 * 1.0.0 - 2024-01-12
 * - Initial release
 * - Added form structure
 * - Added validation markup
 * - Added AJAX integration
 */
defined('ABSPATH') || exit;
?>

<div id="edit-employee-modal" class="modal-overlay wp-customer-modal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h3><?php _e('Edit Karyawan', 'wp-customer'); ?></h3>
            <button type="button" class="modal-close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="edit-employee-form" method="post">
            <?php wp_nonce_field('wp_customer_nonce'); ?>
            <input type="hidden" name="id" id="edit-employee-id">

            <div class="modal-content">
                <!-- Nama Karyawan -->
                <div class="employee-form-group">
                    <label for="edit-employee-name" class="required-field">
                        <?php _e('Nama Karyawan', 'wp-customer'); ?>
                    </label>
                    <input type="text"
                           id="edit-employee-name"
                           name="name"
                           class="regular-text"
                           maxlength="100"
                           required>
                </div>

                <!-- Cabang -->
                <div class="employee-form-group">
                    <label for="edit-employee-branch" class="required-field">
                        <?php _e('Cabang', 'wp-customer'); ?>
                    </label>
                    <select id="edit-employee-branch" name="branch_id" required>
                        <option value=""><?php _e('Pilih Cabang', 'wp-customer'); ?></option>
                        <!-- Options will be populated via JavaScript -->
                    </select>
                </div>

                <!-- Jabatan -->
                <div class="employee-form-group">
                    <label for="edit-employee-position" class="required-field">
                        <?php _e('Jabatan', 'wp-customer'); ?>
                    </label>
                    <input type="text"
                           id="edit-employee-position"
                           name="position"
                           class="regular-text"
                           maxlength="100"
                           required>
                </div>

                <!-- Departemen -->
                <div class="employee-form-group">
                    <label for="edit-employee-department" class="required-field">
                        <?php _e('Departemen', 'wp-customer'); ?>
                    </label>
                    <input type="text"
                           id="edit-employee-department"
                           name="department"
                           class="regular-text"
                           maxlength="100"
                           required>
                </div>

                <!-- Email -->
                <div class="employee-form-group">
                    <label for="edit-employee-email" class="required-field">
                        <?php _e('Email', 'wp-customer'); ?>
                    </label>
                    <input type="email"
                           id="edit-employee-email"
                           name="email"
                           class="regular-text"
                           maxlength="100"
                           required>
                    <p class="description">
                        <?php _e('Email akan digunakan untuk login dan komunikasi', 'wp-customer'); ?>
                    </p>
                </div>

                <!-- Nomor Telepon -->
                <div class="employee-form-group">
                    <label for="edit-employee-phone">
                        <?php _e('Nomor Telepon', 'wp-customer'); ?>
                    </label>
                    <input type="tel"
                           id="edit-employee-phone"
                           name="phone"
                           class="regular-text"
                           maxlength="20"
                           pattern="\+?[0-9\-\(\)\s]*">
                    <p class="description">
                        <?php _e('Format: +62xxx atau 08xxx (opsional)', 'wp-customer'); ?>
                    </p>
                </div>

                <!-- Status -->
                <div class="employee-form-group">
                    <label for="edit-employee-status" class="required-field">
                        <?php _e('Status', 'wp-customer'); ?>
                    </label>
                    <select id="edit-employee-status" name="status" required>
                        <option value="active"><?php _e('Aktif', 'wp-customer'); ?></option>
                        <option value="inactive"><?php _e('Nonaktif', 'wp-customer'); ?></option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <div class="employee-form-actions">
                    <button type="button" class="button cancel-edit">
                        <?php _e('Batal', 'wp-customer'); ?>
                    </button>
                    <button type="submit" class="button button-primary">
                        <?php _e('Perbarui', 'wp-customer'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </div>
        </form>
    </div>
</div>
