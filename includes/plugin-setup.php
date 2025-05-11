<?php
// File: includes/plugin-setup.php
// ------------------------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Custom Post Type 'book' and Taxonomy 'book_category'.
 */
function bl_register_cpt_and_tax() {
    // CPT 'book'
    $labels = array(
        'name'                  => __( 'Books', 'book-library' ),
        'singular_name'         => __( 'Book', 'book-library' ),
        'menu_name'             => __( 'Books', 'book-library' ),
        'name_admin_bar'        => __( 'Book', 'book-library' ),
        'add_new'               => __( 'Add New', 'book-library' ),
        'add_new_item'          => __( 'Add New Book', 'book-library' ),
        'new_item'              => __( 'New Book', 'book-library' ),
        'edit_item'             => __( 'Edit Book', 'book-library' ),
        'view_item'             => __( 'View Book', 'book-library' ),
        'all_items'             => __( 'All Books', 'book-library' ),
        'search_items'          => __( 'Search Books', 'book-library' ),
        'not_found'             => __( 'No books found.', 'book-library' ),
        'not_found_in_trash'    => __( 'No books found in Trash.', 'book-library' ),
    );
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'library' ),
        'supports'           => array( 'title', 'editor' ),
        'show_in_rest'       => true,
    );
    register_post_type( 'book', $args );

    // Taxonomy 'book_category'
    $tax_labels = array(
        'name'                       => __( 'Book Categories', 'book-library' ),
        'singular_name'              => __( 'Book Category', 'book-library' ),
        'search_items'               => __( 'Search Categories', 'book-library' ),
        'all_items'                  => __( 'All Categories', 'book-library' ),
        'parent_item'                => __( 'Parent Category', 'book-library' ),
        'parent_item_colon'          => __( 'Parent Category:', 'book-library' ),
        'edit_item'                  => __( 'Edit Category', 'book-library' ),
        'update_item'                => __( 'Update Category', 'book-library' ),
        'add_new_item'               => __( 'Add New Category', 'book-library' ),
        'new_item_name'              => __( 'New Category Name', 'book-library' ),
        'menu_name'                  => __( 'Categories', 'book-library' ),
    );
    $tax_args = array(
        'hierarchical'               => true,
        'labels'                     => $tax_labels,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'rewrite'                    => array( 'slug' => 'book-category' ),
        'show_in_rest'               => true,
    );
    register_taxonomy( 'book_category', array( 'book' ), $tax_args );

    // Default terms
    $default_terms = array(
        __( 'Reading', 'book-library' ),
        __( 'Just Finished', 'book-library' ),
    );
    foreach ( $default_terms as $term ) {
        if ( ! term_exists( $term, 'book_category' ) ) {
            wp_insert_term( $term, 'book_category' );
        }
    }
}
add_action( 'init', 'bl_register_cpt_and_tax' );
