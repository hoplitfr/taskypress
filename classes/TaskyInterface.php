<?php

class TaskyInterface
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
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

}
