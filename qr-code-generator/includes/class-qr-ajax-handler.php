<?php
/**
 * AJAX Handler Class
 *
 * Handles AJAX requests for QR code generation.
 *
 * @package QR_Code_Generator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class QR_Ajax_Handler
 */
class QR_Ajax_Handler {

    /**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;

    /**
     * QR Generator instance.
     *
     * @var QR_Generator
     */
    private $generator;

    /**
     * Get instance of the class.
     *
     * @return QR_Ajax_Handler
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->generator = new QR_Generator();
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        // AJAX actions for logged-in users
        add_action( 'wp_ajax_generate_qr_code', array( $this, 'handle_generate_qr_code' ) );
        
        // AJAX actions for non-logged-in users
        add_action( 'wp_ajax_nopriv_generate_qr_code', array( $this, 'handle_generate_qr_code' ) );
    }

    /**
     * Handle QR code generation AJAX request.
     */
    public function handle_generate_qr_code() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'qr_code_nonce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security verification failed.', 'qr-code-generator' ),
            ), 403 );
        }

        // Get and sanitize input
        $url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';
        $format = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'png';

        // Validate URL
        if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( array(
                'message' => __( 'Please provide a valid URL.', 'qr-code-generator' ),
            ), 400 );
        }

        // Get optional parameters
        $options = array();
        if ( isset( $_POST['size'] ) ) {
            $options['size'] = absint( $_POST['size'] );
        }
        if ( isset( $_POST['margin'] ) ) {
            $options['margin'] = absint( $_POST['margin'] );
        }
        if ( isset( $_POST['color'] ) ) {
            $options['color'] = sanitize_hex_color_no_hash( $_POST['color'] );
        }
        if ( isset( $_POST['bgcolor'] ) ) {
            $options['bgcolor'] = sanitize_hex_color_no_hash( $_POST['bgcolor'] );
        }
        if ( isset( $_POST['ecc'] ) ) {
            $options['ecc'] = sanitize_text_field( $_POST['ecc'] );
        }

        // Sanitize options
        $options = $this->generator->sanitize_options( $options );

        // Generate QR code
        $result = $this->generator->generate( $url, $format, $options );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
            ), 500 );
        }

        // Prepare response based on format
        if ( 'svg' === $format ) {
            wp_send_json_success( array(
                'format'  => 'svg',
                'content' => $result['content'],
                'dataUrl' => 'data:' . $result['mime'] . ';base64,' . base64_encode( $result['content'] ),
            ) );
        } else {
            wp_send_json_success( array(
                'format'  => 'png',
                'dataUrl' => 'data:' . $result['mime'] . ';base64,' . $result['content'],
            ) );
        }
    }

    /**
     * Handle QR code download AJAX request.
     *
     * This method would be called for direct downloads if needed.
     */
    public function handle_download_qr_code() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'qr_code_nonce' ) ) {
            wp_die( __( 'Security verification failed.', 'qr-code-generator' ), 403 );
        }

        // Get parameters
        $url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';
        $format = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'png';

        if ( empty( $url ) ) {
            wp_die( __( 'Invalid URL.', 'qr-code-generator' ), 400 );
        }

        // Get options
        $options = array();
        if ( isset( $_POST['size'] ) ) {
            $options['size'] = absint( $_POST['size'] );
        }

        $options = $this->generator->sanitize_options( $options );

        // Generate QR code
        $result = $this->generator->generate( $url, $format, $options );

        if ( is_wp_error( $result ) ) {
            wp_die( $result->get_error_message(), 500 );
        }

        // Set headers for download
        header( 'Content-Type: ' . $result['mime'] );
        header( 'Content-Disposition: attachment; filename="qrcode.' . $format . '"' );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: 0' );

        // Output content
        if ( 'svg' === $format ) {
            echo $result['content'];
        } else {
            echo base64_decode( $result['content'] );
        }

        exit;
    }
}
