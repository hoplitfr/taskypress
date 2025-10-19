<?php

/**
 * Plugin Name: TaskyPress
 * Description: A WordPress plugin to assign and track tasks
 * Version: 1.0.0
 * Text Domain: taskypress
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Plugin URI: https://www.hoplit.fr/developpement-de-plugins-sur-mesure/
 * Author: Hoplitfr
 * Author URI: https://www.hoplit.fr/
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('TASKYPRESS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TASKYPRESS_PLUGIN_URL', plugin_dir_url(__FILE__));

require TASKYPRESS_PLUGIN_PATH . 'classes/TaskyPress.php';
require TASKYPRESS_PLUGIN_PATH . 'classes/TaskyInterface.php';

new TaskyPress();
new TaskyInterface();
