<?php

class TaskyPress
{
    public function __construct()
    {

        register_activation_hook(TASKYPRESS_PLUGIN_PATH . 'index.php', array($this, 'activate_plugin'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

    }

    /**
     * Create custom roles when activating the plugin.
     *
     * @return void
     */
    public function activate_plugin(): void
    {
        add_role('task_performer', 'Task Performer', array('read' => true));
        add_role('task_provider', 'Task Provider', array('read' => true));
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

}
