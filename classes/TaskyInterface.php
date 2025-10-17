<?php

class TaskyInterface
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_assign_task', array($this, 'handle_assign_task'));
        add_action('admin_post_update_task_status', array($this, 'handle_update_task_status'));
        add_action('admin_post_add_task_comment', array($this, 'handle_add_task_comment'));
        add_action('admin_post_delete_task', array($this, 'handle_delete_task'));
        add_action('admin_post_update_task_progress', [$this, 'handle_update_task_progress']);
    }

    /**
     * Add a custom menu page for TaskyPress.
     *
     * @return void
     */
    public function add_admin_menu(): void
    {
        add_menu_page(
            __('TaskyPress', 'taskypress'),
            __('TaskyPress', 'taskypress'),
            'read',
            'taskypress',
            array($this, 'admin_page_content'),
            'dashicons-clipboard',
            6
        );
    }

    /**
     * Render the content of the admin page.
     *
     * @return void
     */
    public function admin_page_content(): void
    {
        $current_user = wp_get_current_user();

        if (in_array('task_provider', $current_user->roles)) {
            $this->render_provider_interface();
        } elseif (in_array('task_performer', $current_user->roles)) {
            $this->render_performer_interface();
        } else {
            echo '<p>' . __('You do not have permission to view this page.', 'taskypress') . '</p>';
        }
    }

    /**
     * Render the interface for task providers.
     *
     * @return void
     */
    private function render_provider_interface(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'taskypress_tasks';
        $current_user = wp_get_current_user();

        // Fetch task performers
        $performers = get_users(array('role' => 'task_performer'));

        // Fetch tasks assigned by the current provider
        $assigned_tasks = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE task_provider_id = %d",
            $current_user->ID
        ));

        $template = TASKYPRESS_PLUGIN_PATH . 'templates/provider-interface.php';
        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Handle the task assignment form submission.
     *
     * @return void
     */
    public function handle_assign_task(): void
    {
        if (!isset($_POST['assign_task_nonce']) || !wp_verify_nonce($_POST['assign_task_nonce'], 'assign_task_action')) {
            wp_die(__('Security check failed.', 'taskypress'));
        }

        $provider_id = get_current_user_id();
        $performer_id = intval($_POST['task_performer_id']);
        $title = sanitize_text_field($_POST['task_title']);
        $description = sanitize_textarea_field($_POST['task_description']);

        $taskypress = new TaskyPress();
        if ($taskypress->insert_task($provider_id, $performer_id, $title, $description)) {
            wp_redirect(admin_url('admin.php?page=taskypress&task_assigned=true'));
            exit;
        } else {
            wp_die(__('Failed to assign task.', 'taskypress'));
        }
    }

    /**
     * Handle the task status update form submission.
     *
     * @return void
     */
    public function handle_update_task_status(): void
    {
        if (!isset($_POST['update_task_status_nonce']) || !wp_verify_nonce($_POST['update_task_status_nonce'], 'update_task_status_action')) {
            wp_die(__('Security check failed.', 'taskypress'));
        }

        $task_id = intval($_POST['task_id']);
        $status = sanitize_text_field($_POST['task_status']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'taskypress_tasks';

        $wpdb->update(
            $table_name,
            array('task_status' => $status),
            array('id' => $task_id),
            array('%s'),
            array('%d')
        );

        wp_redirect(admin_url('admin.php?page=taskypress&task_status_updated=true'));
        exit;
    }

    /**
     * Handle the task comment addition form submission.
     *
     * @return void
     */
    public function handle_add_task_comment(): void
    {
        if (!isset($_POST['add_task_comment_nonce']) || !wp_verify_nonce($_POST['add_task_comment_nonce'], 'add_task_comment_action')) {
            wp_die(__('Security check failed.', 'taskypress'));
        }

        $task_id = intval($_POST['task_id']);
        $comment = sanitize_textarea_field($_POST['task_comment']);
        $current_user = wp_get_current_user();
        $user_comment = $current_user->display_name . ': ' . $comment;

        global $wpdb;
        $table_name = $wpdb->prefix . 'taskypress_tasks';

        $current_comments = $wpdb->get_var($wpdb->prepare("SELECT task_additional_info_requests FROM $table_name WHERE id = %d", $task_id));
        $new_comments = $current_comments ? $current_comments . "\n" . $user_comment : $user_comment;

        $wpdb->update(
            $table_name,
            array('task_additional_info_requests' => $new_comments),
            array('id' => $task_id),
            array('%s'),
            array('%d')
        );

        wp_redirect(admin_url('admin.php?page=taskypress&task_comment_added=true'));
        exit;
    }

    /**
     * Handle the task deletion form submission.
     *
     * @return void
     */
    public function handle_delete_task(): void
    {
        if (!isset($_POST['delete_task_nonce']) || !wp_verify_nonce($_POST['delete_task_nonce'], 'delete_task_action')) {
            wp_die(__('Security check failed.', 'taskypress'));
        }

        $task_id = intval($_POST['task_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'taskypress_tasks';

        $wpdb->delete($table_name, array('id' => $task_id), array('%d'));

        wp_redirect(admin_url('admin.php?page=taskypress&task_deleted=true'));
        exit;
    }

    /**
     * Render the interface for task performers.
     *
     * @return void
     */
    private function render_performer_interface(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'taskypress_tasks';
        $current_user = wp_get_current_user();

        // Fetch tasks assigned to the current performer
        $tasks = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE task_performer_id = %d",
            $current_user->ID
        ));

        $template = TASKYPRESS_PLUGIN_PATH . 'templates/performer-interface.php';
        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Handle the task progress update form submission.
     *
     * @return void
     */
    public function handle_update_task_progress(): void
    {
        if (!isset($_POST['update_task_progress_nonce']) || !wp_verify_nonce($_POST['update_task_progress_nonce'], 'update_task_progress_action')) {
            wp_die(__('Security check failed.', 'taskypress'));
        }

        if (!isset($_POST['task_id']) || !isset($_POST['task_progress'])) {
            wp_die(__('Missing required fields.', 'taskypress'));
        }

        $task_id = intval($_POST['task_id']);
        $progress = intval($_POST['task_progress']);

        if ($progress < 0 || $progress > 100) {
            wp_die(__('Progress must be between 0 and 100.', 'taskypress'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'taskypress_tasks';

        $wpdb->update(
            $table_name,
            array('task_progress' => $progress),
            array('id' => $task_id),
            array('%d'),
            array('%d')
        );

        wp_redirect(admin_url('admin.php?page=taskypress&task_progress_updated=true'));
        exit;
    }
}
