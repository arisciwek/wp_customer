<?php
/**
 * Customer Validator Class
 *
 * @package     WP_Customer
 * @subpackage  Validators
 * @version     2.0.0
 * @author      arisciwek
 *
 * Path: src/Validators/Customer/CustomerValidator.php
 *
 * Description: Validator untuk memvalidasi operasi CRUD Customer.
 *              Menangani validasi form dan permission check.
 *              Mendukung multiple user roles: admin, owner, employee.
 *              Terintegrasi dengan WP Capability System.
 *
 * Dependencies:
 * - WPCustomer\Models\CustomerModel untuk data checks
 * - WordPress Capability API
 * 
 * Changelog:
 * 2.0.0 - 2024-01-20
 * - Separated form and permission validation
 * - Added role-based permission system
 * - Added relation caching for better performance
 * - Added support for multiple user types (admin, owner, employee)
 * - Improved error handling and messages
 *
 * 1.0.0 - 2024-12-02
 * - Initial release
 */

namespace WPCustomer\Validators\Customer;

use WPCustomer\Models\Customer\CustomerModel;

class CustomerValidator {
    private CustomerModel $model;
    private array $relationCache = [];

    private array $action_capabilities = [
        'create' => 'add_customer',
        'update' => ['edit_all_customers', 'edit_own_customer'],
        'view' => ['view_customer_detail', 'view_own_customer'],
        'delete' => 'delete_customer',
        'list' => 'view_customer_list'
    ];

    public function __construct() {
        $this->model = new CustomerModel();
    }

    /**
     * Validasi form input
     *
     * @param array $data Data yang akan divalidasi
     * @param int|null $id ID customer untuk update (optional)
     * @return array Array of errors, empty jika valid
     */
    public function validateForm(array $data, ?int $id = null): array {
        $errors = [];

        // Name validation
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $errors['name'] = __('Nama customer wajib diisi.', 'wp-customer');
        } 
        elseif (mb_strlen($name) > 100) {
            $errors['name'] = __('Nama customer maksimal 100 karakter.', 'wp-customer');
        }
        elseif ($this->model->existsByName($name, $id)) {
            $errors['name'] = __('Nama customer sudah ada.', 'wp-customer');
        }

        // NPWP validation (optional)
        if (!empty($data['npwp'])) {
            $npwp = trim($data['npwp']);
            if (!preg_match('/^\d{2}\.\d{3}\.\d{3}\.\d{1}-\d{3}\.\d{3}$/', $npwp)) {
                $errors['npwp'] = __('Format NPWP tidak valid.', 'wp-customer');
            }
        }

        // NIB validation (optional)
        if (!empty($data['nib'])) {
            $nib = trim($data['nib']);
            if (!preg_match('/^\d{13}$/', $nib)) {
                $errors['nib'] = __('Format NIB tidak valid.', 'wp-customer');
            }
        }

        return $errors;
    }

    /**
     * Validasi permission untuk suatu action
     *
     * @param string $action Action yang akan divalidasi (create|update|view|delete|list)
     * @param int|null $id ID customer (optional)
     * @return array Array of errors, empty jika valid
     * @throws \Exception Jika action tidak valid
     */
    public function validatePermission(string $action, ?int $id = null): array {
        $errors = [];

        if (!$id) {
            // Untuk action yang tidak memerlukan ID (misal: create)
            return $this->validateBasicPermission($action);
        }

        // Dapatkan relasi user dengan customer
        $relation = $this->getUserRelation($id);
        
        // Validasi berdasarkan relasi dan action
        switch ($action) {
            case 'view':
                if (!$this->canView($relation)) {
                    $errors['permission'] = __('Anda tidak memiliki akses untuk melihat customer ini.', 'wp-customer');
                }
                break;

            case 'update':
                if (!$this->canUpdate($relation)) {
                    $errors['permission'] = __('Anda tidak memiliki akses untuk mengubah customer ini.', 'wp-customer');
                }
                break;

            case 'delete':
                if (!$this->canDelete($relation)) {
                    $errors['permission'] = __('Anda tidak memiliki akses untuk menghapus customer ini.', 'wp-customer');
                }
                break;

            default:
                throw new \Exception('Invalid action specified');
        }

        return $errors;
    }

    /**
     * Get user relation with customer
     *
     * @param int $customer_id
     * @return array Array containing is_admin, is_owner, is_employee flags
     */
    public function getUserRelation(int $customer_id): array {
        global $wpdb;
        $current_user_id = get_current_user_id();

        // Check cache first
        if (isset($this->relationCache[$customer_id])) {
            return $this->relationCache[$customer_id];
        }

        $relation = [
            'is_admin' => current_user_can('edit_all_customers'),
            'is_owner' => false,
            'is_employee' => false,
            'is_branch_admin' => false
        ];

        // Get customer data
        $customer = $this->model->find($customer_id);
        if ($customer) {
            $relation['is_owner'] = ((int)$customer->user_id === $current_user_id);
        }

        // Check if employee
        $is_employee = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}app_customer_employees 
             WHERE customer_id = %d AND user_id = %d AND status = 'active'",
            $customer_id,
            $current_user_id
        ));
        $relation['is_employee'] = (bool)$is_employee;

        // Check if branch admin
        $is_branch_admin = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}app_branches 
             WHERE customer_id = %d AND user_id = %d",
            $customer_id,
            $current_user_id
        ));
        $relation['is_branch_admin'] = (bool)$is_branch_admin;

        // Cache the result
        $this->relationCache[$customer_id] = $relation;

        return $relation;
    }

    public function canView(array $relation): bool {
        return $relation['is_admin'] || 
               ($relation['is_owner'] && current_user_can('view_own_customer')) ||
               ($relation['is_employee'] && current_user_can('view_own_customer')) ||
               ($relation['is_branch_admin'] && current_user_can('view_own_customer'));
    }

    public function canUpdate(array $relation): bool {
        return $relation['is_admin'] || 
               ($relation['is_owner'] && current_user_can('edit_own_customer')) ||
               ($relation['is_branch_admin'] && current_user_can('edit_own_customer'));
    }

    public function canDelete(array $relation): bool {
        return ($relation['is_admin'] && current_user_can('delete_customer')) ||
               ($relation['is_owner'] && current_user_can('delete_own_customer'));
    }

    private function getAccessType(array $relation): string {
        if ($relation['is_admin']) return 'admin';
        if ($relation['is_owner']) return 'owner';  
        if ($relation['is_branch_admin']) return 'branch_admin';
        if ($relation['is_employee']) return 'employee';
        return 'none';
    }


    public function validateAccess(int $customer_id): array {
        $relation = $this->getUserRelation($customer_id);
        
        return [
            'has_access' => $this->canView($relation),
            'access_type' => $this->getAccessType($relation),
            'relation' => $relation,
            'customer_id' => $customer_id // Tambahkan ini
        ];
    }
    
    /**
     * Validate basic permissions that don't require customer ID
     */
    private function validateBasicPermission(string $action): array {
        $errors = [];
        $required_cap = $this->action_capabilities[$action] ?? null;

        if (!$required_cap) {
            throw new \Exception('Invalid action specified');
        }

        if (!current_user_can($required_cap)) {
            $errors['permission'] = __('Anda tidak memiliki izin untuk operasi ini.', 'wp-customer');
        }

        return $errors;
    }

    /**
     * Clear relation cache
     *
     * @param int|null $customer_id If provided, only clear cache for specific customer
     */
    public function clearCache(?int $customer_id = null): void {
        if ($customer_id) {
            unset($this->relationCache[$customer_id]);
        } else {
            $this->relationCache = [];
        }
    }


    public function validateDelete(int $id): array {
        $errors = [];

        // 1. Validasi permission dasar
        if (!current_user_can('delete_customer')) {
            $errors[] = __('Anda tidak memiliki izin untuk menghapus customer', 'wp-customer');
            return $errors;
        }

        // 2. Cek apakah customer ada
        $customer = $this->model->find($id);
        if (!$customer) {
            $errors[] = __('Customer tidak ditemukan', 'wp-customer');
            return $errors;
        }

        // 3. Cek relasi dengan User
        if (!$this->canDelete($this->getUserRelation($id))) {
            $errors[] = __('Anda tidak memiliki izin untuk menghapus customer ini', 'wp-customer');
            return $errors;
        }

        // 4. Cek apakah customer memiliki branch
        $branch_count = $this->model->getBranchCount($id);
        if ($branch_count > 0) {
            $errors[] = sprintf(
                __('Customer tidak dapat dihapus karena masih memiliki %d cabang', 'wp-customer'),
                $branch_count
            );
        }

        // 5. Cek apakah customer memiliki employee
        global $wpdb;
        $employee_count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}app_customer_employees WHERE customer_id = %d",
            $id
        ));

        if ($employee_count > 0) {
            $errors[] = sprintf(
                __('Customer tidak dapat dihapus karena masih memiliki %d karyawan', 'wp-customer'),
                $employee_count
            );
        }

        return $errors;
    }    
}
