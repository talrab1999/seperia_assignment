<?php
/**
 * Plugin Name: Compare Assignment
 * Description: Creates a Compare Assignment page and renders a DummyJSON product table with search, pagination, and galleries.
 * Version: 1.0.0
 * Author: Seperia Assignment
 */

if (!defined('ABSPATH')) {
    exit;
}

define('COMPARE_ASSIGNMENT_VERSION', '1.0.0');
define('COMPARE_ASSIGNMENT_PLUGIN_FILE', __FILE__);
define('COMPARE_ASSIGNMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COMPARE_ASSIGNMENT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once COMPARE_ASSIGNMENT_PLUGIN_DIR . 'includes/helpers.php';
require_once COMPARE_ASSIGNMENT_PLUGIN_DIR . 'includes/class-products-api.php';
require_once COMPARE_ASSIGNMENT_PLUGIN_DIR . 'includes/class-renderer.php';
require_once COMPARE_ASSIGNMENT_PLUGIN_DIR . 'includes/class-plugin.php';

Compare_Assignment_Plugin::init();
register_activation_hook(__FILE__, [Compare_Assignment_Plugin::class, 'activate']);
