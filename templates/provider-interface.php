<h2><?php _e('Assign Task', 'taskypress'); ?></h2>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="assign_task">
    <?php wp_nonce_field('assign_task_action', 'assign_task_nonce'); ?>

    <label for="task_performer_id"><?php _e('Select Task Performer:', 'taskypress'); ?></label>
    <select name="task_performer_id" id="task_performer_id">
        <?php foreach ($performers as $performer): ?>
            <option value="<?php echo esc_attr($performer->ID); ?>">
                <?php echo esc_html($performer->display_name); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="task_title"><?php _e('Task Title:', 'taskypress'); ?></label>
    <input type="text" name="task_title" id="task_title" required>

    <label for="task_description"><?php _e('Task Description:', 'taskypress'); ?></label>
    <textarea name="task_description" id="task_description" required></textarea>

    <input type="submit" class="button button-primary" value="<?php esc_attr_e('Assign Task', 'taskypress'); ?>">
</form>

<h2><?php _e('Assigned Tasks', 'taskypress'); ?></h2>

<?php if ($assigned_tasks): ?>
    <ul>
        <?php foreach ($assigned_tasks as $task): ?>
            <li>
                <h3><?php echo esc_html($task->task_title); ?></h3>
                <p><?php _e('Assigned to:', 'taskypress'); ?>
                    <?php echo esc_html(get_userdata($task->task_performer_id)->display_name); ?></p>
                <p><?php echo esc_html($task->task_description); ?></p>
                <p><strong><?php _e('Status:', 'taskypress'); ?></strong> <?php echo esc_html($task->task_status); ?></p>
                <p><strong><?php _e('Progress:', 'taskypress'); ?></strong> <?php echo esc_html($task->task_progress); ?>%</p>

                <?php if ($task->task_additional_info_requests): ?>
                    <p><strong><?php _e('Comments:', 'taskypress'); ?></strong></p>
                    <p><?php echo nl2br(esc_html(wp_unslash($task->task_additional_info_requests))); ?></p>
                <?php endif; ?>

                <!-- Form: Update task status -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="update_task_status">
                    <input type="hidden" name="task_id" value="<?php echo esc_attr($task->id); ?>">
                    <?php wp_nonce_field('update_task_status_action', 'update_task_status_nonce'); ?>

                    <label for="task_status"><?php _e('Update Status:', 'taskypress'); ?></label>
                    <select name="task_status" id="task_status">
                        <option value="pending" <?php selected($task->task_status, 'pending'); ?>><?php _e('Pending', 'taskypress'); ?></option>
                        <option value="in_progress" <?php selected($task->task_status, 'in_progress'); ?>><?php _e('In Progress', 'taskypress'); ?></option>
                        <option value="completed" <?php selected($task->task_status, 'completed'); ?>><?php _e('Completed', 'taskypress'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php esc_attr_e('Update Status', 'taskypress'); ?>">
                </form>

                <!-- Form: Add comment -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="add_task_comment">
                    <input type="hidden" name="task_id" value="<?php echo esc_attr($task->id); ?>">
                    <?php wp_nonce_field('add_task_comment_action', 'add_task_comment_nonce'); ?>

                    <label for="task_comment"><?php _e('Add Comment:', 'taskypress'); ?></label>
                    <textarea name="task_comment" id="task_comment"></textarea>
                    <input type="submit" class="button" value="<?php esc_attr_e('Add Comment', 'taskypress'); ?>">
                </form>

                <!-- Form: Delete task -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                      onsubmit="return confirm('<?php echo esc_js(__('Are you sure you want to delete this task?', 'taskypress')); ?>');">
                    <input type="hidden" name="action" value="delete_task">
                    <input type="hidden" name="task_id" value="<?php echo esc_attr($task->id); ?>">
                    <?php wp_nonce_field('delete_task_action', 'delete_task_nonce'); ?>
                    <input type="submit" class="button button-secondary" value="<?php esc_attr_e('Delete Task', 'taskypress'); ?>">
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p><?php _e('No tasks assigned yet.', 'taskypress'); ?></p>
<?php endif; ?>
