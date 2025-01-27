/**
 * Customer Management Interface
 *
 * @package     WP_Customer
 * @subpackage  Assets/JS
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-customer/assets/js/customer/customer-script.js
 *
 * Description: Main JavaScript handler untuk halaman customer.
 *              Mengatur interaksi antar komponen seperti DataTable,
 *              form, panel kanan, dan notifikasi.
 *              Includes state management dan event handling.
 *              Terintegrasi dengan WordPress AJAX API.
 *
 * Dependencies:
 * - jQuery
 * - CustomerDataTable
 * - CustomerForm
 * - CustomerToast
 * - WordPress AJAX
 *
 * Changelog:
 * 1.0.0 - 2024-12-03
 * - Added proper jQuery no-conflict handling
 * - Added panel kanan integration
 * - Added CRUD event handlers
 * - Added toast notifications
 * - Improved error handling
 * - Added loading states
 *
 * Last modified: 2025-01-12 16:45:00
 */
 (function($) {
     'use strict';

     const Customer = {
         currentId: null,
         isLoading: false,
         components: {
             container: null,
             rightPanel: null,
             detailsPanel: null,
             stats: {
                 totalCustomers: null,
                 totalBranches: null
             }
         },

         init() {
             this.components = {
                 container: $('.wp-customer-container'),
                 rightPanel: $('.wp-customer-right-panel'),
                 detailsPanel: $('#customer-details'),
                 stats: {
                     totalCustomers: $('#total-customers'),
                     totalBranches: $('#total-branches')
                 }
             };

             this.bindEvents();
             this.handleInitialState();
             // Tambahkan load stats saat inisialisasi
             this.loadStats();

             // Update stats setelah operasi CRUD
            $(document)
                .on('customer:created.Customer', () => this.loadStats())
                .on('customer:deleted.Customer', () => this.loadStats())
                .on('branch:created.Customer', () => this.loadStats())
                .on('branch:deleted.Customer', () => this.loadStats())
                .on('employee:created.Customer', () => this.loadStats())
                .on('employee:deleted.Customer', () => this.loadStats());

         },

         bindEvents() {
             // Unbind existing events first to prevent duplicates
             $(document)
                 .off('.Customer')
                 .on('customer:created.Customer', (e, data) => this.handleCreated(data))
                 .on('customer:updated.Customer', (e, data) => this.handleUpdated(data))
                 .on('customer:deleted.Customer', () => this.handleDeleted())
                 //.on('customer:display.Customer', (e, data) => this.displayData(data))
                 .on('customer:loading.Customer', () => this.showLoading())
                 .on('customer:loaded.Customer', () => this.hideLoading());

             // Window events
             $(window).off('hashchange.Customer').on('hashchange.Customer', () => this.handleHashChange());
         },

            validateCustomerAccess(customerId, onSuccess, onError) {
                $.ajax({
                    url: wpCustomerData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'validate_customer_access',
                        id: customerId,
                        nonce: wpCustomerData.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            if (onSuccess) onSuccess(response.data);
                        } else {
                            if (onError) onError(response.data);
                        }
                    },
                    error: (xhr) => {
                        if (onError) onError({
                            message: 'Terjadi kesalahan saat validasi akses',
                            code: 'server_error'
                        });
                    }
                });
            },

            handleInitialState() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#')) {
                const customerId = parseInt(hash.substring(1));
                if (customerId) {
                    this.validateCustomerAccess(
                        customerId,
                        (data) => {
                            // On successful validation
                            this.load_customer_preview(customerId);
                            this.components.container.addClass('with-right-panel');
                            this.components.rightPanel.addClass('visible');
                        },
                        (error) => {
                            window.location.href = 'admin.php?page=wp-customer';
                            CustomerToast.error(error.message);
                        }
                    );
                }
            }
        },

        handleHashChange() {
            const hash = window.location.hash;
            if (!hash) {
                this.closePanel();
                return;
            }

            const id = hash.substring(1);
            if (id) {
                this.load_customer_preview(id);
                $('.tab-content').removeClass('active');
                $('#customer-details').addClass('active');
                $('.nav-tab').removeClass('nav-tab-active');
                $('.nav-tab[data-tab="customer-details"]').addClass('nav-tab-active');
            }
        },


        load_customer_preview(id) {
            if (this.isLoading) return;
            this.isLoading = true;

            $.ajax({
                url: wpCustomerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_customer_data_ajax',
                    id: id,
                    nonce: wpCustomerData.nonce
                },
                success: (response) => {
                    $('#wp-customer-right-panel').html(response);
                    this.currentId = id;
                    this.components.container.addClass('with-right-panel');
                    this.components.rightPanel.addClass('visible');

                    // Reinitialize components after content update
                    if (window.BranchDataTable) {
                        window.BranchDataTable.init(id);
                    }
                    if (window.CreateBranchForm) {
                        window.CreateBranchForm.init();
                    }
                    if (window.EditBranchForm) {
                        window.EditBranchForm.init(); 
                    }

                    if (window.CreateEmployeeForm) {
                        window.CreateEmployeeForm.init();
                    }
                    if (window.EditEmployeeForm) {
                        window.EditEmployeeForm.init();
                    }
                    if (window.EmployeeDataTable) {
                        window.EmployeeDataTable.init(id);
                    }

                    // Bind new events setelah content diupdate
                    this.bindEvents();
                },
                error: (xhr) => {
                    CustomerToast.error('Gagal memuat data customer');
                },
                complete: () => {
                    this.isLoading = false;
                }
            });
        },

         async loadCustomerData(id) {
             if (!id || this.isLoading) return;

             this.isLoading = true;
             this.showLoading();
        
            console.log('loadCustomerData called for ID:', id);  // Debug load call

             try {
                 const response = await $.ajax({
                     url: wpCustomerData.ajaxUrl,
                     type: 'POST',
                     data: {
                         action: 'get_customer',
                         id: id,
                         nonce: wpCustomerData.nonce,
                         _: new Date().getTime() // Cache busting
                     }
                 });
        
                    console.log('Load customer response:', response);  // Debug load response

                 if (response.success) {
                     //this.displayData(response.data);
                     this.currentId = id;
                 } else {
                     CustomerToast.error(response.data?.message || 'Gagal memuat data customer');
                 }
             } catch (error) {
                 console.error('Load customer error:', error);
                 if (this.isLoading) {
                     CustomerToast.error('Gagal menghubungi server');
                 }
             } finally {
                 this.isLoading = false;
                 this.hideLoading();
             }
         },
/*
         displayData(data) {
             if (!data || !data.customer) {
                 CustomerToast.error('Data customer tidak valid');
                 return;
             }
            
            console.log('Initial data received:', data);
            console.log('Current customer ID:', data.customer.id);

            // Dapatkan URL dan parameter saat ini
            const currentUrl = new URL(window.location.href);
            const currentId = currentUrl.searchParams.get('id');
            console.log('Current URL param id:', currentId);
            console.log('New customer id:', data.customer.id);


             $('.tab-content').removeClass('active');
             $('#customer-details').addClass('active');
             $('.nav-tab').removeClass('nav-tab-active');
             $('.nav-tab[data-tab="customer-details"]').addClass('nav-tab-active');

             this.components.container.addClass('with-right-panel');
             this.components.rightPanel.addClass('visible');

             const createdAt = new Date(data.customer.created_at).toLocaleString('id-ID');
             const updatedAt = new Date(data.customer.updated_at).toLocaleString('id-ID');

             $('#customer-header-name').text(data.customer.name);
             $('#customer-name').text(data.customer.name);
             $('#customer-branch-count').text(data.branch_count);
             $('#customer-employee-count').text(data.employee_count);
             $('#customer-created-at').text(createdAt);
             $('#customer-updated-at').text(updatedAt);

             if (window.CustomerDataTable) {
                 window.CustomerDataTable.highlightRow(data.customer.id);
             }

            // Tambahkan handling untuk membership data
            if (data.customer.membership) {
                // Update membership badge
                $('#current-level-badge').text(data.customer.membership.level);
                
                // Update staff usage
                const staffUsage = data.customer.staff_count || 0;
                const staffLimit = data.customer.membership.max_staff;
                $('#staff-usage-count').text(staffUsage);
                $('#staff-usage-limit').text(staffLimit === -1 ? 'Unlimited' : staffLimit);
                
                // Calculate progress bar percentage
                if (staffLimit !== -1) {
                    const percentage = (staffUsage / staffLimit) * 100;
                    $('#staff-usage-bar').css('width', `${percentage}%`);
                }

                // Update capabilities list
                const $capList = $('#active-capabilities').empty();
                Object.entries(data.customer.membership.capabilities).forEach(([cap, enabled]) => {
                    if (enabled) {
                        $capList.append(`<li>${this.getCapabilityLabel(cap)}</li>`);
                    }
                });

                // Show/hide upgrade buttons based on current level
                const currentLevel = data.customer.membership.level;
                $('.upgrade-card').each(function() {
                    const cardLevel = $(this).attr('id').replace('-plan', '');
                    $(this).toggle(this.shouldShowUpgradeOption(currentLevel, cardLevel));
                });
            }
         },
*/
            // Helper function untuk label capability
            getCapabilityLabel(cap) {
                const labels = {
                    'can_add_staff': 'Dapat menambah staff',
                    'can_export': 'Dapat export data',
                    'can_bulk_import': 'Dapat bulk import'
                };
                return labels[cap] || cap;
            },

            // Helper function untuk logika tampilan tombol upgrade
            shouldShowUpgradeOption(currentLevel, targetLevel) {
                const levels = ['regular', 'priority', 'utama'];
                const currentIdx = levels.indexOf(currentLevel);
                const targetIdx = levels.indexOf(targetLevel);
                return targetIdx > currentIdx;
            },

            switchTab(tabId) {
                // Pastikan kita punya currentId sebelum switch tab
                if (!this.currentId) {
                    return;
                }

                // Update URL tanpa reload halaman
                const currentHash = window.location.hash;
                if (!currentHash || !currentHash.includes(this.currentId)) {
                    // Gunakan replaceState untuk update URL tanpa trigger hashchange
                    window.history.replaceState(null, '', `#${this.currentId}`);
                }

                // Update UI
                $('.nav-tab').removeClass('nav-tab-active');
                $(`.nav-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');

                $('.tab-content').removeClass('active');
                $(`#${tabId}`).addClass('active');

                // Load content jika diperlukan
                if (tabId === 'branch-list') {
                    this.initializeBranchTable();
                } 
                else if (tabId === 'employee-list') {
                    this.initializeEmployeeTable();
                }
            },

            initializeBranchTable() {
                const checkAndInit = () => {
                    if (window.BranchDataTable) {
                        window.BranchDataTable.init(this.currentId);
                        $(`#branch-list .loading-placeholder`).hide();
                    } else {
                        setTimeout(checkAndInit, 100);
                    }
                };
                checkAndInit();
            },

            initializeEmployeeTable() {
                const checkAndInit = () => {
                    if (window.EmployeeDataTable) {
                        window.EmployeeDataTable.init(this.currentId);
                        $(`#employee-list .loading-placeholder`).hide();
                    } else {
                        setTimeout(checkAndInit, 100);
                    }
                };
                checkAndInit();
            },

         closePanel() {
             this.components.container.removeClass('with-right-panel');
             this.components.rightPanel.removeClass('visible');
             this.currentId = null;
             window.location.hash = '';
             $(document).trigger('panel:closed');
         },

         showLoading() {
             this.components.rightPanel.addClass('loading');
         },

         hideLoading() {
             this.components.rightPanel.removeClass('loading');
         },

         handleCreated(data) {
             if (data && data.id) {
                     window.location.hash = data.id;
             }

             if (window.CustomerDataTable) {
                 window.CustomerDataTable.refresh();
             }

             if (window.Dashboard) {
                 window.Dashboard.refreshStats();
             }
         },
         
        handleUpdated(response) {
            if (response && response.data && response.data.customer) {
                const editedCustomerId = response.data.customer.id;
                
                if (editedCustomerId === parseInt(window.location.hash.substring(1))) {
                    // Jika customer yang diedit sama dengan yang sedang dilihat
                    // Langsung update panel tanpa mengubah hash
                    this.displayData(response.data);
                } else {
                    // Jika berbeda, ubah hash ke customer yang diedit
                    window.location.hash = editedCustomerId;
                }
                
                // Refresh DataTable
                if (window.CustomerDataTable) {
                    window.CustomerDataTable.refresh();
                }
            }
        },
        

         handleDeleted() {
             this.closePanel();
             if (window.CustomerDataTable) {
                 window.CustomerDataTable.refresh();
             }
             if (window.Dashboard) {
                window.Dashboard.loadStats(); // Gunakan loadStats() langsung
             }
         },

        /**
         * Load customer statistics including total customers and branches.
         * Uses getCurrentCustomerId() to determine which customer's stats to load.
         * Updates stats display via updateStats() when data is received.
         * 
         * @async
         * @fires customer:loading When stats loading begins
         * @fires customer:loaded When stats are successfully loaded
         * @see getCurrentCustomerId
         * @see updateStats
         * 
         * @example
         * // Load stats on page load 
         * Customer.loadStats();
         * 
         * // Load stats after customer creation
         * $(document).on('customer:created', () => Customer.loadStats());
         */
        async loadStats() {
            const hash = window.location.hash;
            const customerId = hash ? parseInt(hash.substring(1)) : 0;
            
            $.ajax({
                url: wpCustomerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_customer_stats',
                    nonce: wpCustomerData.nonce,
                    id: customerId
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStats(response.data);
                    }
                }
            });
        },

        updateStats(stats) {
            $('#total-customers').text(stats.total_customers);
            $('#total-branches').text(stats.total_branches);
            $('#total-employees').text(stats.total_employees);
        }

     };
    
    $(document).on('click', '.nav-tab', function(e) {
        e.preventDefault();
        const tabId = $(this).data('tab');
        if (Customer.currentId) {
            Customer.switchTab(tabId);
        }
    });

     // Initialize when document is ready
     $(document).ready(() => {
         window.Customer = Customer;
         Customer.init();
     });

 })(jQuery);
 