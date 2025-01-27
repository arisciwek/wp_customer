/**
 * Customer Form Handler
 *
 * @package     WP_Customer
 * @subpackage  Assets/JS/Components
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-customer/assets/js/customer/create-customer-form.js
 *
 * Description: Handler untuk form customer.
 *              Menangani create dan update customer.
 *              Includes validasi form, error handling,
 *              dan integrasi dengan komponen lain.
 *
 * Dependencies:
 * - jQuery
 * - jQuery Validation
 * - CustomerToast for notifications
 * - Customer main component
 * - WordPress AJAX API
 *
 * Changelog:
 * 1.0.0 - 2024-12-03
 * - Added proper form validation
 * - Added AJAX integration
 * - Added modal management
 * - Added loading states
 * - Added error handling
 * - Added toast notifications
 * - Added panel integration
 *
 * Last modified: 2024-12-03 16:30:00
 */
(function($) {
    'use strict';

    const CreateCustomerForm = {
        modal: null,
        form: null,

        init() {
            this.modal = $('#create-customer-modal');
            this.form = $('#create-customer-form');

            this.bindEvents();
            this.initializeValidation();
        },

        bindEvents() {
            // Form submission - menggunakan arrow function untuk menjaga context
            this.form.on('submit', (e) => this.handleCreate(e));
            
            // Field validation - bind dengan proper context
            this.form.on('input', 'input[name="name"]', (e) => {
                this.validateNameField(e.target);
            });

            // Add button handler
            //$('#add-customer-btn').on('click', () => this.showModal());

            $('#add-customer-btn').on('click', () => {
                console.log('Button clicked');
                this.showModal();
            });

            // Modal events
            $('.modal-close', this.modal).on('click', () => this.hideModal());
            $('.cancel-create', this.modal).on('click', () => this.hideModal());

            // Close modal when clicking outside
            this.modal.on('click', (e) => {
                if ($(e.target).is('.modal-overlay')) {
                    this.hideModal();
                }
            });
        },

        // Memisahkan validasi khusus untuk field nama
        validateNameField(field) {
            const $field = $(field);
            const value = $field.val().trim();
            const errors = [];

            if (!value) {
                errors.push('Nama customer wajib diisi');
            } else {
                if (value.length < 3) {
                    errors.push('Nama customer minimal 3 karakter');
                }
                if (value.length > 100) {
                    errors.push('Nama customer maksimal 100 karakter');
                }
                if (!/^[a-zA-Z\s]+$/.test(value)) {
                    errors.push('Nama customer hanya boleh mengandung huruf dan spasi');
                }
            }

            const $error = $field.next('.form-error');
            if (errors.length > 0) {
                $field.addClass('error');
                if ($error.length) {
                    $error.text(errors[0]);
                } else {
                    $('<span class="form-error"></span>')
                        .text(errors[0])
                        .insertAfter($field);
                }
                return false;
            } else {
                $field.removeClass('error');
                $error.remove();
                return true;
            }
        },

        async handleCreate(e) {
            e.preventDefault();

            if (!this.form.valid()) {
                return;
            }

            // Collect form data
            const formData = {
                action: 'create_customer',
                nonce: wpCustomerData.nonce,
                name: this.form.find('[name="name"]').val().trim(),
                provinsi_id: this.form.find('[name="provinsi_id"]').val(),
                regency_id: this.form.find('[name="regency_id"]').val()
            };

            // Add user_id if available (admin only)
            const userIdField = this.form.find('[name="user_id"]');
            if (userIdField.length && userIdField.val()) {
                formData.user_id = userIdField.val();
            }

            this.setLoadingState(true);

            try {
                const response = await $.ajax({
                    url: wpCustomerData.ajaxUrl,
                    type: 'POST',
                    data: formData
                });

                if (response.success) {
                    CustomerToast.success('Customer berhasil ditambahkan');
                    this.hideModal();
                    $(document).trigger('customer:created', [response.data]);

                    if (window.CustomerDataTable) {
                        window.CustomerDataTable.refresh();
                    }
                } else {
                    CustomerToast.error(response.data?.message || 'Gagal menambah customer');
                }
            } catch (error) {
                console.error('Create customer error:', error);
                CustomerToast.error('Gagal menghubungi server. Silakan coba lagi.');
            } finally {
                this.setLoadingState(false);
            }
        },

        initializeValidation() {
            this.form.validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 100
                    },
                    provinsi_id: {
                        required: true
                    },
                    regency_id: {
                        required: true
                    },
                    user_id: {
                        required: this.form.find('#customer-owner').length > 0
                    }
                },
                messages: {
                    name: {
                        required: 'Nama customer wajib diisi',
                        minlength: 'Nama customer minimal 3 karakter',
                        maxlength: 'Nama customer maksimal 100 karakter'
                    },
                    provinsi_id: {
                        required: 'Provinsi wajib dipilih'
                    },
                    regency_id: {
                        required: 'Kabupaten/Kota wajib dipilih'
                    },
                    user_id: {
                        required: 'Admin wajib dipilih'
                    }
                }
            });
        },

        setLoadingState(loading) {
            const $submitBtn = this.form.find('[type="submit"]');
            const $spinner = this.form.find('.spinner');

            if (loading) {
                $submitBtn.prop('disabled', true);
                $spinner.addClass('is-active');
                this.form.addClass('loading');
            } else {
                $submitBtn.prop('disabled', false);
                $spinner.removeClass('is-active');
                this.form.removeClass('loading');
            }
        },

        showModal() {
            console.log('Show modal called');
            this.resetForm();
            this.modal.fadeIn(300, () => {
                console.log('Fade complete');
                this.form.find('[name="name"]').focus();
            });
        },
        
        hideModal() {
            this.modal.fadeOut(300, () => {
                this.resetForm();
            });
        },

        resetForm() {
            if (!this.form || !this.validator) return;

            this.form[0].reset();
            this.form.find('.form-error').remove();
            this.form.find('.error').removeClass('error');
            this.validator.resetForm();

            const $regencySelect = this.form.find('[name="regency_id"]');
            if ($regencySelect.length) {
                $regencySelect
                    .html('<option value="">Pilih Kabupaten/Kota</option>')
                    .prop('disabled', true);
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        window.CreateCustomerForm = CreateCustomerForm;
        CreateCustomerForm.init();
    });

})(jQuery);
