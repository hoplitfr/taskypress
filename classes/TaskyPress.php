<?php

class TaskyPress
{
    public function __construct()
    {

        register_activation_hook(TASKYPRESS_PLUGIN_PATH . 'index.php', array($this, 'activate_plugin'));

        register_deactivation_hook(TASKYPRESS_PLUGIN_PATH . 'index.php', array($this, 'deactivate_plugin'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

        load_plugin_textdomain('taskypress', false, dirname(plugin_basename(__FILE__)) . '/languages');

    }

    /**
     * Create custom roles and table when activating the plugin.
     *
     * @return void
     */
    public function activate_plugin(): void
    {
        if (!add_role('task_performer', 'Task Performer', array('read' => true))) {
            error_log('Failed to add task_performer role.');
        }

        if (!add_role('task_provider', 'Task Provider', array('read' => true))) {
            error_log('Failed to add task_provider role.');
        }

        if (!$this->create_tasks_table()) {
            error_log('Failed to create tasks table.');
        }
    }

    /**
     * Remove custom roles and table when deactivating the plugin.
     *
     * @return void
     */
    public function deactivate_plugin(): void
    {
        remove_role('task_performer');
        remove_role('task_provider');

        global $wpdb;

        $table_name = $wpdb->prefix . 'taskypress_tasks';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    /**
     * Enqueue custom styles for the plugin.
     * Loads the CSS files required.
     *
     * @return void
     */
    public function enqueue_styles(): void
    {
        wp_enqueue_style('taskypress-main-style', TASKYPRESS_PLUGIN_URL . 'css/main.css');
    }

    /**
     * Create SQL table for the tasks.
     *
     * @return void
     */
    private function create_tasks_table(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'taskypress_tasks';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        task_provider_id BIGINT(20) UNSIGNED NOT NULL,
        task_performer_id BIGINT(20) UNSIGNED NOT NULL,
        task_title VARCHAR(255) NOT NULL,
        task_description TEXT NOT NULL,
        task_status VARCHAR(50) NOT NULL DEFAULT 'pending',
        task_progress TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
        task_additional_info_requests TEXT DEFAULT NULL,
        task_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        task_updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            dbDelta($sql);
        }
    }

    /**
     * Insert a new task into the database.
     *
     * This function inserts a new task with the specified provider ID, performer ID,
     * title, and description into the custom tasks table.
     *
     * @param int    $provider_id   The ID of the task provider.
     * @param int    $performer_id  The ID of the task performer.
     * @param string $title         The title of the task.
     * @param string $description   The description of the task.
     *
     * @return bool True if the task was successfully inserted, false otherwise.
     */
    public function insert_task($provider_id, $performer_id, $title, $description): bool
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'taskypress_tasks';

        $sql = $wpdb->prepare(
            "INSERT INTO $table_name (task_provider_id, task_performer_id, task_title, task_description)
        VALUES (%d, %d, %s, %s)",
            $provider_id,
            $performer_id,
            $title,
            $description
        );

        return $wpdb->query($sql) !== false;
    }

}
