<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Assicuriamoci che l’autoloader sia caricato
if ( ! class_exists( \Amazon\ProductAdvertisingAPI\v1\Configuration::class )
     && file_exists( BL_PLUGIN_DIR . 'vendor/autoload.php' )
) {
    require_once BL_PLUGIN_DIR . 'vendor/autoload.php';
}

use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\Exception\PaapiClientException;

/**
 * Aggiunge il metabox “Book Details” al CPT 'book'
 */
add_action( 'add_meta_boxes', 'bl_add_book_metabox' );
function bl_add_book_metabox() {
    add_meta_box(
        'bl_book_details',
        __( 'Book Details', 'book-library' ),
        'bl_render_book_metabox',
        'book',
        'normal',
        'high'
    );
}

/**
 * Rendering del metabox HTML
 */
function bl_render_book_metabox( $post ) {
    wp_nonce_field( 'bl_save_book_details', 'bl_book_nonce' );
    $meta = get_post_meta( $post->ID );
    ?>
    <p>
        <label for="bl_book_search"><?php esc_html_e( 'Search Book', 'book-library' ); ?></label><br />
        <input type="text" id="bl_book_search" class="widefat" placeholder="<?php esc_attr_e( 'Title or ISBN', 'book-library' ); ?>" />
    </p>
    <div id="bl_search_results"></div>

    <p>
        <label for="bl_isbn"><?php esc_html_e( 'ISBN', 'book-library' ); ?></label><br />
        <input type="text" id="bl_isbn" name="_bl_isbn" value="<?php echo esc_attr( $meta['_bl_isbn'][0] ?? '' ); ?>" class="widefat" />
    </p>

    <p>
        <label for="bl_asin"><?php esc_html_e( 'ASIN', 'book-library' ); ?></label><br />
        <input type="text" id="bl_asin" name="_bl_asin" value="<?php echo esc_attr( $meta['_bl_asin'][0] ?? '' ); ?>" class="regular-text" />
        <button type="button" class="button" id="bl_fetch_asin"><?php esc_html_e( 'Fetch ASIN', 'book-library' ); ?></button>
    </p>

    <p>
        <label for="bl_author"><?php esc_html_e( 'Author', 'book-library' ); ?></label><br />
        <input type="text"
               id="bl_author"
               name="_bl_author"
               value="<?php echo esc_attr( $meta['_bl_author'][0] ?? '' ); ?>"
               class="widefat" />
    </p>

    <p>
        <label for="bl_publisher"><?php esc_html_e( 'Publisher', 'book-library' ); ?></label><br />
        <input type="text" id="bl_publisher" name="_bl_publisher" value="<?php echo esc_attr( $meta['_bl_publisher'][0] ?? '' ); ?>" class="widefat" />
    </p>

    <p>
        <label for="bl_year"><?php esc_html_e( 'Year', 'book-library' ); ?></label><br />
        <input type="number" id="bl_year" name="_bl_year" value="<?php echo esc_attr( $meta['_bl_year'][0] ?? '' ); ?>" class="small-text" />
    </p>

    <p>
        <label for="bl_cover"><?php esc_html_e( 'Cover URL', 'book-library' ); ?></label><br />
        <input type="text" id="bl_cover" name="_bl_cover" value="<?php echo esc_attr( $meta['_bl_cover'][0] ?? '' ); ?>" class="widefat" />
    </p>

    <p>
        <label>
            <input type="checkbox" name="_bl_recommended" <?php checked( $meta['_bl_recommended'][0] ?? '', 'on' ); ?> />
            <?php esc_html_e( 'Recommended', 'book-library' ); ?>
        </label>
    </p>

    <p>
        <label for="bl_category"><?php esc_html_e( 'Category', 'book-library' ); ?></label><br />
        <?php
        // Recupera il termine corrente assegnato a questo post
        $current_terms = wp_get_post_terms( $post->ID, 'book_category', [ 'fields' => 'ids' ] );
        $current_cat   = ! empty( $current_terms ) ? $current_terms[0] : 0;
        wp_dropdown_categories( [
            'taxonomy'     => 'book_category',
            'name'         => '_bl_category',
            'selected'     => $current_cat,
            'hide_empty'   => 0,
            'show_option_none' => __( '&mdash; Select Category &mdash;', 'book-library' ),
        ] );
        ?>
    </p>
    <?php
}

/**
 * Salvataggio dei dati del metabox
 */
add_action( 'save_post', 'bl_save_book_metabox_data' );
function bl_save_book_metabox_data( $post_id ) {
    if ( ! isset( $_POST['bl_book_nonce'] )
        || ! wp_verify_nonce( $_POST['bl_book_nonce'], 'bl_save_book_details' )
        || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        || get_post_type( $post_id ) !== 'book'
    ) {
        return;
    }

    // Campi testo
    foreach ( [ '_bl_isbn', '_bl_author', '_bl_asin', '_bl_publisher', '_bl_cover', '_bl_year' ] as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }

    // Checkbox Recommended
    $recommended = isset( $_POST['_bl_recommended'] ) ? 'on' : '';
    update_post_meta( $post_id, '_bl_recommended', $recommended );

    // Tassonomia Category
    if ( isset( $_POST['_bl_category'] ) ) {
        wp_set_post_terms( $post_id, (int) $_POST['_bl_category'], 'book_category' );
    }
}

/**
 * Enqueue admin-side JS
 */
add_action( 'admin_enqueue_scripts', 'bl_enqueue_admin_scripts' );
function bl_enqueue_admin_scripts( $hook ) {
    $screen = get_current_screen();
    // Carica SOLO sulle schermate del CPT "book"
    if ( isset( $screen->post_type ) && $screen->post_type === 'book' ) {

        // Forziamo una versione univoca per bustare la cache
        $ver = defined('BL_VERSION') ? BL_VERSION : time();

        // JS
        wp_enqueue_script(
            'bl-admin-js',
            BL_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            $ver,
            true
        );

        // DEBUG: verifica che lo script sia registrato
        // (Opzionale: rimuovi in produzione)
        error_log( 'Book Library: enqueued bl-admin-js at ' . BL_PLUGIN_URL . 'assets/js/admin.js?ver=' . $ver );

        wp_localize_script( 'bl-admin-js', 'bl_admin_ajax', [
            'nonce'             => wp_create_nonce( 'bl_book_search' ),
            'ajax_url'          => admin_url( 'admin-ajax.php' ),
            'limit'             => get_option( 'bl_search_limit', 10 ),
            'api_key'           => get_option( 'bl_google_books_api_key', '' ),
            'no_results'        => __( 'No results found', 'book-library' ),
            'invalid_isbn'      => __( 'Please enter a valid ISBN first', 'book-library' ),
            'error_fetch_asin'  => __( 'Error fetching ASIN, check console for details', 'book-library' ),
        ] );

        // CSS di styling admin (facoltativo)
        wp_enqueue_style(
            'bl-admin-css',
            BL_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $ver
        );
    }
}


/**
 * AJAX handler: cerca libri con Google Books
 */
add_action( 'wp_ajax_bl_search_books', 'bl_ajax_search_books' );
function bl_ajax_search_books() {
    if ( ! check_ajax_referer( 'bl_book_search', 'nonce', false ) ) {
        wp_send_json_error();
    }

    // Sanitize e identifica ISBN puro (10 o 13 cifre)
    $raw = $_GET['q'] ?? '';
    $q   = sanitize_text_field( $raw );
    if ( preg_match( '/^\d{10,13}$/', $q ) ) {
        $query = 'isbn:' . $q;
    } else {
        $query = $q;
    }

    $key = get_option( 'bl_google_books_api_key', '' );
    $max = get_option( 'bl_search_limit', 10 );

    $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode( $query ) . "&maxResults={$max}";
    if ( $key ) {
        $url .= "&key={$key}";
    }

    $response = wp_remote_get( $url );
    if ( is_wp_error( $response ) ) {
        wp_send_json_error();
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    $out  = [];

    if ( ! empty( $data['items'] ) ) {
        foreach ( $data['items'] as $item ) {
            $info      = $item['volumeInfo'];
            $isbn      = '';
            foreach ( $info['industryIdentifiers'] ?? [] as $id ) {
                if ( $id['type'] === 'ISBN_13' ) {
                    $isbn = $id['identifier'];
                    break;
                }
            }

            // ricava il publisher, se manca vai a selfLink
            $publisher = $info['publisher'] ?? '';
            if ( empty( $publisher ) && ! empty( $item['id'] ) ) {
                $detail_url = "https://www.googleapis.com/books/v1/volumes/{$item['id']}";
                if ( $key ) {
                    $detail_url .= "?key={$key}";
                }
                $detail_resp = wp_remote_get( $detail_url );
                if ( ! is_wp_error( $detail_resp ) ) {
                    $detail_data = json_decode( wp_remote_retrieve_body( $detail_resp ), true );
                    $publisher   = $detail_data['volumeInfo']['publisher'] ?? $publisher;
                }
            }

            $out[] = [
                'title'     => $info['title'] ?? '',
                'authors'   => implode( ', ', $info['authors'] ?? [] ),
                'publisher' => $publisher,
                'year'      => substr( $info['publishedDate'] ?? '', 0, 4 ),
                'cover'     => $info['imageLinks']['thumbnail'] ?? '',
                'isbn'      => $isbn,
            ];
        }
    }

    wp_send_json_success( $out );
}


/**
 * AJAX handler: recupera ASIN via Amazon PAAPI
 */
add_action( 'wp_ajax_bl_fetch_asin', 'bl_ajax_fetch_asin' );
function bl_ajax_fetch_asin() {
    try {
        if ( ! check_ajax_referer( 'bl_book_search', 'nonce', false ) ) {
            throw new Exception( __( 'Invalid nonce', 'book-library' ) );
        }

        $isbn = sanitize_text_field( $_GET['isbn'] ?? '' );
        if ( ! $isbn ) {
            throw new Exception( __( 'No ISBN provided', 'book-library' ) );
        }

        $access_key    = get_option( 'bl_amazon_access_key', '' );
        $secret_key    = get_option( 'bl_amazon_secret_key', '' );
        $associate_tag = get_option( 'bl_amazon_associate_tag', '' );
        if ( ! $access_key || ! $secret_key || ! $associate_tag ) {
            throw new Exception( __( 'PAAPI credentials missing', 'book-library' ) );
        }

        // Configurazione PAAPI
        $config = new Configuration();
        $config->setAccessKey( $access_key );
        $config->setSecretKey( $secret_key );
        $config->setHost( 'webservices.amazon.it' );
        $config->setRegion( 'eu-west-1' );

        // Istanza client
        $apiInstance = new DefaultApi( new \GuzzleHttp\Client(), $config );

        // SearchItemsRequest—cerchiamo per ISBN=
        $request = new SearchItemsRequest();
        $request->setKeywords( $isbn );
        $request->setSearchIndex( 'Books' );
        $request->setResources( [
            'ItemInfo.Title',
            'ItemInfo.ExternalIds',      // include ISBN e altri ID esterni
            'Images.Primary.Small',
        ] );
        $request->setPartnerTag( $associate_tag );
        $request->setPartnerType( 'Associates' );

        $response = $apiInstance->searchItems( $request );
        $items    = $response->getSearchResult()->getItems();

        if ( ! empty( $items ) ) {
            foreach ( $items as $item ) {
                // Estrai External IDs
                $externalIds = $item->getItemInfo()->getExternalIds();
                $isbnList = [];
                if ( $externalIds ) {
                    $isbnsAttr = $externalIds->getISBNs();
                    if ( is_array( $isbnsAttr ) ) {
                        $isbnList = $isbnsAttr;
                    } elseif ( is_object( $isbnsAttr ) && method_exists( $isbnsAttr, 'getValue' ) ) {
                        $isbnList = $isbnsAttr->getValue();
                    }
                }
                // Se troviamo l’ISBN corrispondente, restituiamo l’ASIN
                if ( in_array( $isbn, $isbnList, true ) ) {
                    wp_send_json_success( [ 'asin' => $item->getASIN() ] );
                }
            }
            // Fallback: primo ASIN se nessun ISBN combacia
            wp_send_json_success( [ 'asin' => $items[0]->getASIN() ] );
        }

        wp_send_json_error( [ 'error_message' => __( 'ASIN not found via search', 'book-library' ) ] );


        throw new Exception( __( 'ASIN not found via search', 'book-library' ) );

    } catch ( PaapiClientException $e ) {
        wp_send_json_error([
            'error_message' => sprintf(
                __( 'PAAPI request failed: %s', 'book-library' ),
                $e->getMessage()
            )
        ]);
    } catch ( Exception $e ) {
        wp_send_json_error([ 'error_message' => $e->getMessage() ]);
    }
}

// Hook per iniettare script inline nel footer dell’admin
add_action( 'admin_footer-post.php',    'bl_book_metabox_inline_script' );
add_action( 'admin_footer-post-new.php','bl_book_metabox_inline_script' );
function bl_book_metabox_inline_script() {
    $screen = get_current_screen();
    if ( isset( $screen->post_type ) && $screen->post_type === 'book' ) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            // Al click su un risultato di ricerca, popola anche #title
            $(document).on('click', '.bl-search-list li', function(){
                var bookTitle = $(this).data('title');
                $('#title').val( bookTitle );
            });
        });
        </script>
        <?php
    }
}