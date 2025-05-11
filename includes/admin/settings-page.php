<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'bl_add_settings_page' );
function bl_add_settings_page() {
    add_options_page(
        __( 'Book Library Settings', 'book-library' ),
        __( 'Book Library', 'book-library' ),
        'manage_options',
        'bl_settings_page',
        'bl_render_settings_page'
    );
}

add_action( 'admin_init', 'bl_register_settings' );
function bl_register_settings() {
    $option_group = 'bl_settings_group';
    $page         = 'bl_settings_page';

    // API Settings section
    add_settings_section(
        'bl_api_section',
        __( 'API Settings', 'book-library' ),
        'bl_api_section_cb',
        $page
    );

    // General Settings section
    add_settings_section(
        'bl_general_section',
        __( 'General Settings', 'book-library' ),
        'bl_general_section_cb',
        $page
    );

    // --- API SETTINGS FIELDS ---

    // (Rimosso bl_amazon_tracking_id)

    // Google Books API Key
    register_setting( $option_group, 'bl_google_books_api_key', 'sanitize_text_field' );
    add_settings_field(
        'bl_google_books_api_key',
        __( 'Google Books API Key', 'book-library' ),
        'bl_google_books_api_key_cb',
        $page,
        'bl_api_section'
    );

    // Amazon PAAPI Access Key
    register_setting( $option_group, 'bl_amazon_access_key', 'sanitize_text_field' );
    add_settings_field(
        'bl_amazon_access_key',
        __( 'Amazon PAAPI Access Key', 'book-library' ),
        'bl_amazon_access_key_cb',
        $page,
        'bl_api_section'
    );

    // Amazon PAAPI Secret Key
    register_setting( $option_group, 'bl_amazon_secret_key', 'sanitize_text_field' );
    add_settings_field(
        'bl_amazon_secret_key',
        __( 'Amazon PAAPI Secret Key', 'book-library' ),
        'bl_amazon_secret_key_cb',
        $page,
        'bl_api_section'
    );

    // **Amazon Associate Tag** (unico campo per il tag affiliato)
    register_setting( $option_group, 'bl_amazon_associate_tag', 'sanitize_text_field' );
    add_settings_field(
        'bl_amazon_associate_tag',
        __( 'Amazon Associate Tag', 'book-library' ),
        'bl_amazon_associate_tag_cb',
        $page,
        'bl_api_section'
    );

    // --- GENERAL SETTINGS FIELDS ---

    register_setting(
        $option_group,
        'bl_search_limit',
        [
            'sanitize_callback' => 'absint',
            'default'           => 10,
        ]
    );
    add_settings_field(
        'bl_search_limit',
        __( 'Searches per minute', 'book-library' ),
        'bl_search_limit_cb',
        $page,
        'bl_general_section'
    );
}

function bl_api_section_cb() {
    echo '<p>' . esc_html__( 'Configure your API credentials for Google Books and Amazon PAAPI.', 'book-library' ) . '</p>';
}
function bl_general_section_cb() {
    echo '<p>' . esc_html__( 'General plugin settings.', 'book-library' ) . '</p>';
}

function bl_google_books_api_key_cb() {
    printf(
        '<input type="text" name="bl_google_books_api_key" value="%s" class="regular-text" />',
        esc_attr( get_option( 'bl_google_books_api_key', '' ) )
    );
}
function bl_amazon_access_key_cb() {
    printf(
        '<input type="text" name="bl_amazon_access_key" value="%s" class="regular-text" />',
        esc_attr( get_option( 'bl_amazon_access_key', '' ) )
    );
}
function bl_amazon_secret_key_cb() {
    printf(
        '<input type="password" name="bl_amazon_secret_key" value="%s" class="regular-text" />',
        esc_attr( get_option( 'bl_amazon_secret_key', '' ) )
    );
}
function bl_amazon_associate_tag_cb() {
    printf(
        '<input type="text" name="bl_amazon_associate_tag" value="%s" class="regular-text" />',
        esc_attr( get_option( 'bl_amazon_associate_tag', '' ) )
    );
}
function bl_search_limit_cb() {
    printf(
        '<input type="number" name="bl_search_limit" value="%d" min="1" class="small-text" />',
        esc_attr( get_option( 'bl_search_limit', 10 ) )
    );
}

function bl_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Book Library Settings', 'book-library' ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'bl_settings_group' );
            do_settings_sections( 'bl_settings_page' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
