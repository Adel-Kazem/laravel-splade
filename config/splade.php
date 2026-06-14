<?php

return [
    /**
     * The URI to handle Event Redirects.
     *
     * @see https://splade.dev/docs/x-event
     */
    'event_redirect_route' => '/_splade/eventRedirect/{uuid}',

    /**
     * URI to handle Table Bulk Actions.
     *
     * @see https://splade.dev/docs/table-bulk-actions
     */
    'table_bulk_action_route' => '/_splade/table/action/{table}/{action}/{slug}',

    /**
     * The URI to handle Table Export Actions.
     *
     * @see https://splade.dev/docs/table-exports
     */
    'table_export_route' => '/_splade/table/export/{table}/{export}/{slug}',

    /**
     * The URI to handle password confirmation.
     *
     * @see https://splade.dev/docs/x-confirm
     */
    'confirm_password_route' => '/_splade/confirmPassword',

    /**
     * The URI to handle the interactive component.
     */
    'with_vue_bridge_route' => '/_splade/withVueBridge',

    /**
     * Name of the guard which is used for the password confirmation.
     * Leave empty to use the default guard.
     *
     * @see https://splade.dev/docs/x-confirm
     */
    'confirm_password_guard' => null,

    /**
     * Share all flash data to the Vue front-end app.
     *
     * @see https://splade.dev/docs/x-flash
     */
    'share_session_flash_data' => true,

    /**
     * Temporary directory for file uploads with Filepond.
     *
     * @see https://splade.dev/docs/form-file
     */
    'file_uploads' => [
        'disk'                    => '',
        'temporary_file_lifetime' => 60 * 60, // 1 hour
        'route'                   => '/_splade/fileUpload',
    ],

    /**
     * Settings for the Splade Blade components.
     *
     * @see https://splade.dev/docs/form-overview
     */
    'blade' => [
        'component_prefix'                   => 'splade',
        'table_cell_directive'               => 'cell',
        'asterisk_on_required_form_elements' => false,
        'escape_validation_messages'         => true,
        'seo_title_directive'                => 'seoTitle',
        'seo_description_directive'          => 'seoDescription',
        'seo_keywords_directive'             => 'seoKeywords',
    ],

    /**
     * Server-side rendering (SSR) settings.
     *
     * @see https://splade.dev/docs/ssr
     */
    'ssr' => [
        'enabled'        => env('SPLADE_SSR_ENABLED', false),
        'server'         => 'http://127.0.0.1:9000/',
        'blade_fallback' => true,
    ],

    /**
     * Names of the Dusk macro to interact with Choices.js instances.
     *
     * @see https://splade.dev/docs/form-select
     */
    'dusk' => [
        'choices_select_macro'      => 'choicesSelect',
        'choices_remove_item_macro' => 'choicesRemoveItem',
    ],

    /**
     * In-app navigation history ("back" button).
     *
     * Splade keeps a server-side stack of the real pages a user visits so the
     * <x-splade-back-link> component (route: splade.navBack) can return them to
     * the previous page. Modal/slideover loads and partial reloads are overlays,
     * not pages, and are never recorded.
     */
    'navigation' => [
        // Master switch. When false, no tracking happens and the back route
        // is not registered.
        'history' => true,

        // URI for the back endpoint, registered with the web + splade middleware.
        'route' => '/_splade/nav-back',

        // Session key holding the history stack.
        'session_key' => 'splade_nav_history',

        // Where to send the user when the stack is empty.
        'fallback' => '/',

        // Max entries kept on the stack (oldest are trimmed).
        'max_depth' => 30,

        // Routes never recorded as "back" targets. Each entry is matched against
        // BOTH the route name (Route::is wildcards, e.g. 'password.*') AND the
        // URI path (glob, e.g. 'api/*'), so either form works. Splade's own
        // internal endpoints are excluded via '_splade/*'.
        'except' => [
            '_splade/*',
            'login',
            'logout',
            'register',
            'password.*',
            'verification.*',
        ],
    ],
];
