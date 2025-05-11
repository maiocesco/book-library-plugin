<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue frontend CSS and JS assets.
 */
add_action( 'wp_enqueue_scripts', 'bl_enqueue_frontend_assets' );
function bl_enqueue_frontend_assets() {
    wp_enqueue_style(
        'bl-frontend-css',
        BL_PLUGIN_URL . 'assets/css/frontend.css',
        [],
        BL_VERSION
    );
    wp_enqueue_script(
        'bl-frontend-js',
        BL_PLUGIN_URL . 'assets/js/frontend.js',
        [ 'jquery' ],
        BL_VERSION,
        true
    );
}

/**
 * Shortcode [book_library] to render the book library.
 */
add_shortcode( 'book_library', 'bl_render_book_library' );
function bl_render_book_library( $atts ) {
    // Get term IDs for “In lettura” and “Appena terminati”
    $reading_term  = get_term_by( 'slug', 'in-lettura', 'book_category' );
    $finished_term = get_term_by( 'slug', 'appena-terminati', 'book_category' );
    $reading_id    = $reading_term  ? $reading_term->term_id  : 0;
    $finished_id   = $finished_term ? $finished_term->term_id : 0;

    // 1) Books “In lettura”
    $reading_q = new WP_Query( [
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'tax_query'      => [
            [
                'taxonomy' => 'book_category',
                'field'    => 'term_id',
                'terms'    => $reading_id,
            ],
        ],
    ] );

    // 2) Books “Appena terminati”
    $finished_q = new WP_Query( [
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'tax_query'      => [
            [
                'taxonomy' => 'book_category',
                'field'    => 'term_id',
                'terms'    => $finished_id,
            ],
        ],
    ] );

    // 3) Other custom categories
    $all_terms = get_terms( [
        'taxonomy'   => 'book_category',
        'hide_empty' => true,
        'exclude'    => [ $reading_id, $finished_id ],
    ] );

    ob_start();
    ?>
    <div class="bl-reading-section">
        <h2><?php esc_html_e( 'Sto leggendo', 'book-library' ); ?></h2>
        <?php if ( $reading_q->have_posts() ) : ?>
            <?php while ( $reading_q->have_posts() ) : $reading_q->the_post(); ?>
                <div class="bl-reading-item">
                    <div class="cover">
                        <?php
                        $cover = get_post_meta( get_the_ID(), '_bl_cover', true );
                        if ( $cover ) {
                            echo '<img src="' . esc_url( $cover ) . '" alt="' . esc_attr( get_the_title() ) . '" />';
                        }
                        ?>
                    </div>
                    <div class="details">
                        <?php
                        $title_class = get_post_meta( get_the_ID(), '_bl_recommended', true ) === 'on'
                                     ? 'title recommended'
                                     : 'title';
                        echo '<h3 class="' . esc_attr( $title_class ) . '">';
                            // Affiliate link uses ASIN fallback to ISBN
                            $asin = get_post_meta( get_the_ID(), '_bl_asin', true );
                            if ( ! $asin ) {
                                $asin = get_post_meta( get_the_ID(), '_bl_isbn', true );
                            }
                            $tag = get_option( 'bl_amazon_associate_tag', '' );
                            $url = sprintf(
                                'https://www.amazon.it/dp/%s?tag=%s',
                                esc_attr( $asin ),
                                esc_attr( $tag )
                            );
                            echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow noopener">'
                                 . esc_html( get_the_title() )
                                 . '</a>';
                        echo '</h3>';
                        echo '<p class="meta">';
                        // Autore – Editore – Anno
                        $author = get_post_meta( get_the_ID(), '_bl_author', true );
                        if ( $author ) {
                            echo esc_html( $author ) . '<br />';
                        }
                        echo esc_html( get_post_meta( get_the_ID(), '_bl_publisher', true ) );                        
                        echo ' – ';
                        echo esc_html( get_post_meta( get_the_ID(), '_bl_year', true ) );
                        echo '</p>';
                        ?>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'Nessun libro in lettura al momento.', 'book-library' ); ?></p>
        <?php endif; ?>
    </div>

    <div class="bl-just-finished-section">
        <h2><?php esc_html_e( 'Appena finiti', 'book-library' ); ?></h2>
        <?php if ( $finished_q->have_posts() ) : ?>
            <?php while ( $finished_q->have_posts() ) : $finished_q->the_post(); ?>
                <div class="bl-item">
                    <?php
                    $title_class = get_post_meta( get_the_ID(), '_bl_recommended', true ) === 'on'
                                 ? 'title recommended'
                                 : 'title';
                    echo '<h3 class="' . esc_attr( $title_class ) . '">';
                        $asin = get_post_meta( get_the_ID(), '_bl_asin', true );
                        if ( ! $asin ) {
                            $asin = get_post_meta( get_the_ID(), '_bl_isbn', true );
                        }
                        $tag = get_option( 'bl_amazon_associate_tag', '' );
                        $url = sprintf(
                            'https://www.amazon.it/dp/%s?tag=%s',
                            esc_attr( $asin ),
                            esc_attr( $tag )
                        );
                        echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow noopener">'
                             . esc_html( get_the_title() )
                             . '</a>';
                        // Autore – Editore – Anno
                            $author = get_post_meta( get_the_ID(), '_bl_author', true );
                            if ( $author ) {
                                echo ' (' . esc_html( $author ) . ') ';
                            }
                    echo '</h3>';
                    // overlay cover
                    $cover = get_post_meta( get_the_ID(), '_bl_cover', true );
                    if ( $cover ) {
                        echo '<div class="cover-overlay"><img src="' . esc_url( $cover ) . '" alt="" /></div>';
                    }
                    ?>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'Nessun libro appena finito.', 'book-library' ); ?></p>
        <?php endif; ?>
    </div>

    <?php foreach ( $all_terms as $term ) : ?>
        <?php
        $cat_q = new WP_Query( [
            'post_type'      => 'book',
            'posts_per_page' => -1,
            'tax_query'      => [
                [
                    'taxonomy' => 'book_category',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ],
            ],
        ] );
        if ( ! $cat_q->have_posts() ) {
            continue;
        }
        ?>
        <div class="bl-category-section">
            <h2><?php echo esc_html( $term->name ); ?></h2>
            <?php while ( $cat_q->have_posts() ) : $cat_q->the_post(); ?>
                <div class="bl-category-item">
                    <?php
                    $title_class = get_post_meta( get_the_ID(), '_bl_recommended', true ) === 'on'
                                 ? 'title recommended'
                                 : 'title';
                    echo '<h3 class="' . esc_attr( $title_class ) . '">';
                        $asin = get_post_meta( get_the_ID(), '_bl_asin', true );
                        if ( ! $asin ) {
                            $asin = get_post_meta( get_the_ID(), '_bl_isbn', true );
                        }
                        $tag = get_option( 'bl_amazon_associate_tag', '' );
                        $url = sprintf(
                            'https://www.amazon.it/dp/%s?tag=%s',
                            esc_attr( $asin ),
                            esc_attr( $tag )
                        );
                        echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow noopener">'
                             . esc_html( get_the_title() )
                             . '</a>';
                            // Autore – Editore – Anno
                        $author = get_post_meta( get_the_ID(), '_bl_author', true );
                        if ( $author ) {
                            echo ' (' . esc_html( $author ) . ') ';
                        }
                    echo '</h3>';
                    ?>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php endforeach;

    return ob_get_clean();
}
