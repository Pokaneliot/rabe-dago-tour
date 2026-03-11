<?php
/**
 * Enqueue - Scripts & Styles
 */
function rdt_enqueue_assets() {
    wp_enqueue_style(
        'rdt-style',
        get_stylesheet_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'rdt_enqueue_assets');