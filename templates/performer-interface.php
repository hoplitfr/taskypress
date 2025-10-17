<div class="taskypress-admin-wrap">
<h2><?php _e('Your Assigned Tasks', 'taskypress'); ?></h2>

<?php if ($tasks): ?>
    <div class="task-list-grid">
        <?php foreach ($tasks as $task): ?>
            <div class="task-card">
                <h3><?php echo esc_html($task->task_title); ?></h3>
                <p><?php echo esc_html($task->task_description); ?></p>
                <p><strong><?php _e('Status:', 'taskypress'); ?></strong> <?php echo esc_html($task->task_status); ?></p>
                <p><strong><?php _e('Progress:', 'taskypress'); ?></strong></p>
                <progress value="<?php echo esc_attr($task->task_progress); ?>" max="100"></progress>
                <br><span><?php echo esc_html($task->task_progress); ?>%</span>

                <?php if ($task->task_additional_info_requests): ?>
                    <p><strong><?php _e('Comments:', 'taskypress'); ?></strong></p>
                    <p><?php echo nl2br(esc_html(wp_unslash($task->task_additional_info_requests))); ?></p>
                <?php endif; ?>

                <!-- Form: Update task status -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="update_task_status">
                    <input type="hidden" name="task_id" value="<?php echo esc_attr($task->id); ?>">
                    <?php wp_nonce_field('update_task_status_action', 'update_task_status_nonce'); ?>

                    <label for="task_status_<?php echo esc_attr($task->id); ?>">
                        <?php _e('Update Status:', 'taskypress'); ?>
                    </label>
                    <select name="task_status" id="task_status_<?php echo esc_attr($task->id); ?>">
                        <option value="pending" <?php selected($task->task_status, 'pending'); ?>><?php _e('Pending', 'taskypress'); ?></option>
                        <option value="in_progress" <?php selected($task->task_status, 'in_progress'); ?>><?php _e('In Progress', 'taskypress'); ?></option>
                        <option value="completed" <?php selected($task->task_status, 'completed'); ?>><?php _e('Completed', 'taskypress'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php esc_attr_e('Update Status', 'taskypress'); ?>">
                </form>

                <!-- Form: Update task progress -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="update_task_progress">
                    <input type="hidden" name="task_id" value="<?php echo esc_attr($task->id); ?>">
                    <?php wp_nonce_field('update_task_progress_action', 'update_task_progress_nonce'); ?>

                    <label for="task_progress_<?php echo esc_attr($task->id); ?>">
                        <?php _e('Update Progress:', 'taskypress'); ?>
                    </label>
                    <input type="number"
                           name="task_progress"
                           id="task_progress_<?php echo esc_attr($task->id); ?>"
                           min="0" max="100"
                           value="<?php echo esc_attr($task->task_progress); ?>">
                    <input type="submit" class="button" value="<?php esc_attr_e('Update Progress', 'taskypress'); ?>">
                </form>

                <!-- Form: Add comment -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="add_task_comment">
                    <input type="hidden" name="task_id" value="<?php echo esc_attr($task->id); ?>">
                    <?php wp_nonce_field('add_task_comment_action', 'add_task_comment_nonce'); ?>

                    <label for="task_comment_<?php echo esc_attr($task->id); ?>">
                        <?php _e('Add Comment:', 'taskypress'); ?>
                    </label>
                    <textarea name="task_comment" id="task_comment_<?php echo esc_attr($task->id); ?>"></textarea>
                    <input type="submit" class="button" value="<?php esc_attr_e('Add Comment', 'taskypress'); ?>">
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p><?php _e('No tasks assigned yet.', 'taskypress'); ?></p>
<?php endif; ?>
</div>
