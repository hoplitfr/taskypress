<?php

class TaskyInterface
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_assign_task', array($this, 'handle_assign_task'));
        add_action('admin_post_update_task_status', array($this, 'handle_update_task_status'));
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

        echo '<h2>' . __('Assign Task', 'taskypress') . '</h2>';
        echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
        echo '<input type="hidden" name="action" value="assign_task">';
        wp_nonce_field('assign_task_action','assign_task_nonce');

        echo '<label for="task_performer_id">' . __('Select Task Performer:', 'taskypress') . '</label>';
        echo '<select name="task_performer_id" id="task_performer_id">';
        foreach ($performers as $performer) {
            echo '<option value="' . esc_attr($performer->ID) . '">' . esc_html($performer->display_name) . '</option>';
        }
        echo '</select>';

        echo '<label for="task_title">' . __('Task Title:', 'taskypress') . '</label>';
        echo '<input type="text" name="task_title" id="task_title" required>';

        echo '<label for="task_description">' . __('Task Description:', 'taskypress') . '</label>';
        echo '<textarea name="task_description" id="task_description" required></textarea>';

        echo '<input type="submit" value="' . __('Assign Task', 'taskypress') . '">';
        echo '</form>';

        // Display assigned tasks
        echo '<h2>' . __('Assigned Tasks', 'taskypress') . '</h2>';
        if ($assigned_tasks) {
            echo '<ul>';
            foreach ($assigned_tasks as $task) {
                echo '<li>';
                echo '<h3>' . esc_html($task->task_title) . '</h3>';
                echo '<p>' . __('Assigned to: ', 'taskypress') . esc_html(get_userdata($task->task_performer_id)->display_name) . '</p>';
                echo '<p>' . esc_html($task->task_description) . '</p>';
                echo '<p>' . __('Status: ', 'taskypress') . esc_html($task->task_status) . '</p>';

                // Form to update task status
                echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
                echo '<input type="hidden" name="action" value="update_task_status">';
                echo '<input type="hidden" name="task_id" value="' . esc_attr($task->id) . '">';
                wp_nonce_field('update_task_status_action', 'update_task_status_nonce');
                echo '<label for="task_status">' . __('Update Status:', 'taskypress') . '</label>';
                echo '<select name="task_status" id="task_status">';
                echo '<option value="pending" ' . selected($task->task_status, 'pending', false) . '>Pending</option>';
                echo '<option value="in_progress" ' . selected($task->task_status, 'in_progress', false) . '>In Progress</option>';
                echo '<option value="completed" ' . selected($task->task_status, 'completed', false) . '>Completed</option>';
                echo '</select>';
                echo '<input type="submit" value="' . __('Update Status', 'taskypress') . '">';
                echo '</form>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No tasks assigned yet.', 'taskypress') . '</p>';
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

        echo '<h2>' . __('Your Assigned Tasks', 'taskypress') . '</h2>';
        if ($tasks) {
            echo '<ul>';
            foreach ($tasks as $task) {
                echo '<li>';
                echo '<h3>' . esc_html($task->task_title) . '</h3>';
                echo '<p>' . esc_html($task->task_description) . '</p>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No tasks assigned yet.', 'taskypress') . '</p>';
        }
    }
}
