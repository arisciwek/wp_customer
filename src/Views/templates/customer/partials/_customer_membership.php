<?php
/**
 * Customer Membership Tab Template
 *
 * @package     WP_Customer
 * @subpackage  Views/Templates/Customer/Partials
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /wp-customer/src/Views/templates/customer/partials/_customer_membership.php
 *
 * Description: Template untuk menampilkan informasi membership customer
 *              Menampilkan status membership aktif, penggunaan staff,
 *              fitur yang tersedia, dan opsi upgrade ke level yang
 *              lebih tinggi. Template ini bersifat read-only dengan
 *              opsi aksi upgrade membership.
 *
 * Components:
 * - Membership status card
 * - Staff usage progress bar
 * - Active capabilities list
 * - Upgrade plan cards (Regular/Priority/Utama)
 * 
 * Dependencies:
 * - wp-customer-membership.css
 * - wp-customer-membership.js
 * - WP_Customer_Settings class
 * - membership-settings.php
 *
 * Changelog:
 * v1.0.0 - 2024-01-10
 * - Initial version
 * - Added membership status display
 * - Added staff usage visualization
 * - Added capabilities list
 * - Added upgrade plan options
 * - Integrated with membership settings
 */

defined('ABSPATH') || exit;

// Pastikan data membership tersedia
$membership = $membership ?? [];
$staff_count = $membership['staff_count'] ?? 0;
$max_staff = $membership['max_staff'] ?? 2;
$level = $membership['level'] ?? 'regular';
$capabilities = $membership['capabilities'] ?? [];


/*
$active_tab = $_GET['tab'] ?? 'customer-details';

error_log('Active Tab: ' . ($active_tab ?? 'undefined'));

if ($active_tab !== 'membership-info') {
    ?>
    <div id="membership-info" class="tab-content">
        <div class="loading-placeholder">
            <span class="spinner is-active"></span>
            <p>Memuat data membership...</p>
        </div>
    </div>
    <?php
    return;
}
*/

?>

<div id="membership-info" class="tab-content">
    <div class="membership-status-card">
        <h3><?php _e('Status Membership Saat Ini', 'wp-customer'); ?></h3>
        <div class="membership-content">
            <!-- Staff Usage -->
            <div class="staff-usage-section">
                <h4><?php _e('Penggunaan Staff', 'wp-customer'); ?></h4>
                <div class="staff-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="staff-usage-bar" 
                             style="width: <?php echo $max_staff > 0 ? ($staff_count / $max_staff * 100) : 0; ?>%">
                        </div>
                    </div>
                    <div class="usage-text">
                        <span id="staff-usage-count"><?php echo esc_html($staff_count); ?></span> / 
                        <span id="staff-usage-limit"><?php echo $max_staff === -1 ? 'Unlimited' : esc_html($max_staff); ?></span> staff
                    </div>
                </div>
            </div>

            <!-- Capabilities -->
            <div class="capabilities-section">
                <h4><?php _e('Fitur Aktif', 'wp-customer'); ?></h4>
                <ul class="capability-list" id="active-capabilities">
                    <?php foreach ($capabilities as $cap => $enabled): 
                        if ($enabled): ?>
                            <li><?php echo esc_html($this->getCapabilityLabel($cap)); ?></li>
                        <?php endif;
                    endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <?php if ($access['access_type'] === 'admin' || $access['access_type'] === 'owner'): ?>
        <div class="upgrade-cards-container">
            <?php foreach (['regular', 'priority', 'utama'] as $plan_level):
                if ($this->shouldShowUpgradeOption($level, $plan_level)): ?>
                    <div class="upgrade-card">
                        <h4><?php echo esc_html(ucfirst($plan_level)); ?></h4>
                        <?php $this->renderPlanFeatures($plan_level); ?>
                        <button type="button" class="button upgrade-button" data-plan="<?php echo esc_attr($plan_level); ?>">
                            <?php printf(__('Upgrade ke %s', 'wp-customer'), ucfirst($plan_level)); ?>
                        </button>
                    </div>
                <?php endif;
            endforeach; ?>
        </div>
    <?php endif; ?>
</div>