<?php

/**
 * Plugin Name: TaskyPress
 * Description: A WordPress plugin to assign and track tasks
 * Version: 1.0
 * Author: Hoplitfr
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
