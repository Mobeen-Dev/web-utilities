<?php
/**
 * Integration Examples
 *
 * This file shows various ways to integrate and extend the QR Code Generator plugin.
 * DO NOT include this file in production - it's for reference only.
 *
 * @package QR_Code_Generator
 */

// ============================================================================
// Example 1: Programmatically Generate QR Code
// ============================================================================

/**
 * Generate a QR code programmatically.
 */
function my_custom_qr_generation() {
    if ( ! class_exists( 'QR_Generator' ) ) {
        return;
    }
    
    $generator = new QR_Generator();
    
    $url = 'https://example.com';
    $format = 'png'; // or 'svg'
    $options = array(
        'size'    => 400,
        'margin'  => 10,
        'color'   => 'FF0000', // Red
        'bgcolor' => 'FFFFFF', // White
    );
    
    $result = $generator->generate( $url, $format, $options );
    
    if ( is_wp_error( $result ) ) {
        echo 'Error: ' . $result->get_error_message();
        return;
    }
    
    // Use the result
    echo $result['content']; // For SVG
    // or
    // echo '<img src="data:' . $result['mime'] . ';base64,' . $result['content'] . '">';
}

// ============================================================================
// Example 2: Add Custom Shortcode Attributes
// ============================================================================

/**
 * Filter shortcode attributes.
 */
add_filter( 'shortcode_atts_qr_code_generator', 'my_custom_qr_shortcode_atts', 10, 3 );

function my_custom_qr_shortcode_atts( $out, $pairs, $atts ) {
    // Force a specific theme
    $out['theme'] = 'modern';
    
    // Set custom default size
    $out['default_size'] = '500';
    
    return $out;
}

// ============================================================================
// Example 3: Add QR Code to Post Content Automatically
// ============================================================================

/**
 * Automatically add QR code generator to specific posts.
 */
add_filter( 'the_content', 'my_auto_add_qr_generator' );

function my_auto_add_qr_generator( $content ) {
    // Only on single posts
    if ( ! is_single() ) {
        return $content;
    }
    
    // Only for specific categories
    if ( ! in_category( 'qr-enabled' ) ) {
        return $content;
    }
    
    // Add QR generator at the end of content
    $qr_code = do_shortcode( '[qr_code_generator title="Share This Post"]' );
    
    return $content . $qr_code;
}

// ============================================================================
// Example 4: Customize QR Code Options via Filter
// ============================================================================

/**
 * Modify QR code generation options.
 */
add_filter( 'qr_code_generator_options', 'my_custom_qr_options' );

function my_custom_qr_options( $options ) {
    // Always use high error correction
    $options['ecc'] = 'H';
    
    // Set minimum size
    if ( $options['size'] < 300 ) {
        $options['size'] = 300;
    }
    
    return $options;
}

// ============================================================================
// Example 5: Add QR Code Widget
// ============================================================================

/**
 * Custom QR Code Widget.
 */
class My_QR_Code_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'my_qr_code_widget',
            __( 'QR Code Generator Widget', 'text-domain' ),
            array( 'description' => __( 'Display QR code generator in sidebar', 'text-domain' ) )
        );
    }
    
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        
        echo do_shortcode( '[qr_code_generator theme="minimal"]' );
        
        echo $args['after_widget'];
    }
    
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'text-domain' ); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" 
                   value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }
    
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) 
            ? sanitize_text_field( $new_instance['title'] ) 
            : '';
        return $instance;
    }
}

// Register widget
add_action( 'widgets_init', function() {
    register_widget( 'My_QR_Code_Widget' );
});

// ============================================================================
// Example 6: REST API Endpoint
// ============================================================================

/**
 * Add custom REST API endpoint for QR generation.
 */
add_action( 'rest_api_init', function() {
    register_rest_route( 'qr-generator/v1', '/generate', array(
        'methods'  => 'POST',
        'callback' => 'my_qr_rest_generate',
        'permission_callback' => '__return_true', // Adjust as needed
    ));
});

function my_qr_rest_generate( WP_REST_Request $request ) {
    $url = $request->get_param( 'url' );
    $format = $request->get_param( 'format' ) ?: 'png';
    
    if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return new WP_Error( 'invalid_url', 'Invalid URL provided', array( 'status' => 400 ) );
    }
    
    $generator = new QR_Generator();
    $result = $generator->generate( $url, $format );
    
    if ( is_wp_error( $result ) ) {
        return $result;
    }
    
    return rest_ensure_response( $result );
}

// ============================================================================
// Example 7: Admin Page Integration
// ============================================================================

/**
 * Add QR generator to admin menu.
 */
add_action( 'admin_menu', 'my_qr_admin_page' );

function my_qr_admin_page() {
    add_menu_page(
        __( 'QR Code Generator', 'text-domain' ),
        __( 'QR Codes', 'text-domain' ),
        'manage_options',
        'qr-code-generator',
        'my_qr_admin_page_content',
        'dashicons-grid-view'
    );
}

function my_qr_admin_page_content() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <?php echo do_shortcode( '[qr_code_generator]' ); ?>
    </div>
    <?php
}

// ============================================================================
// Example 8: Generate QR for Current Page
// ============================================================================

/**
 * Add "Share via QR" button to posts.
 */
add_filter( 'the_content', 'my_add_qr_share_button' );

function my_add_qr_share_button( $content ) {
    if ( ! is_single() ) {
        return $content;
    }
    
    $current_url = get_permalink();
    
    $button = '<div class="qr-share-wrapper">';
    $button .= '<button id="show-qr-modal" class="qr-share-btn">Share via QR Code</button>';
    $button .= '<div id="qr-modal" style="display:none;">';
    $button .= do_shortcode( '[qr_code_generator]' );
    $button .= '</div>';
    $button .= '</div>';
    
    // Add JavaScript to pre-fill URL
    $button .= '<script>
        jQuery(document).ready(function($) {
            $("#show-qr-modal").click(function() {
                $("#qr-modal").slideToggle();
                $("#qr-url-input").val("' . esc_js( $current_url ) . '");
            });
        });
    </script>';
    
    return $content . $button;
}

// ============================================================================
// Example 9: Email Integration
// ============================================================================

/**
 * Add QR code to email notifications.
 */
add_filter( 'wp_mail', 'my_add_qr_to_email' );

function my_add_qr_to_email( $args ) {
    // Only modify specific emails
    if ( strpos( $args['subject'], 'Your Access Link' ) === false ) {
        return $args;
    }
    
    $generator = new QR_Generator();
    $url = 'https://example.com/access-link';
    
    $result = $generator->generate( $url, 'png', array( 'size' => 200 ) );
    
    if ( ! is_wp_error( $result ) ) {
        $qr_image = '<img src="data:' . $result['mime'] . ';base64,' . $result['content'] . '" alt="QR Code">';
        $args['message'] .= '<br><br><p>Scan this QR code for quick access:</p>' . $qr_image;
    }
    
    return $args;
}

// ============================================================================
// Example 10: WooCommerce Integration
// ============================================================================

/**
 * Add QR code to WooCommerce product pages.
 */
add_action( 'woocommerce_after_add_to_cart_button', 'my_woo_product_qr' );

function my_woo_product_qr() {
    global $product;
    
    $product_url = get_permalink( $product->get_id() );
    
    echo '<div class="product-qr-code" style="margin-top: 20px;">';
    echo '<h3>Share this Product</h3>';
    echo do_shortcode( '[qr_code_generator show_options="no"]' );
    echo '<script>
        jQuery(document).ready(function($) {
            $(".product-qr-code #qr-url-input").val("' . esc_js( $product_url ) . '");
        });
    </script>';
    echo '</div>';
}
