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

        if ( strlen( $url ) > 2048 ) {
            wp_send_json_error( array(
                'message' => __( 'URL is too long (max 2048 characters).', 'qr-code-generator' ),
            ), 400 );
        }

        // Get optional parameters
        $options = array();
        if ( isset( $_POST['size'] ) ) {
            $size = absint( $_POST['size'] );
            if ( $size < 100 || $size > 1000 ) {
                wp_send_json_error( array(
                    'message' => __( 'Size must be between 100 and 1000.', 'qr-code-generator' ),
                ), 400 );
            }
            $options['size'] = $size;
        }
        if ( isset( $_POST['margin'] ) ) {
            $margin = absint( $_POST['margin'] );
            if ( $margin < 0 || $margin > 50 ) {
                wp_send_json_error( array(
                    'message' => __( 'Margin must be between 0 and 50.', 'qr-code-generator' ),
                ), 400 );
            }
            $options['margin'] = $margin;
        }
        if ( isset( $_POST['color'] ) ) {
            $color = sanitize_hex_color_no_hash( $_POST['color'] );
            if ( empty( $color ) && ! empty( $_POST['color'] ) ) {
                wp_send_json_error( array(
                    'message' => __( 'Please provide a valid hex color.', 'qr-code-generator' ),
                ), 400 );
            }
            $options['color'] = $color;
        }
        if ( isset( $_POST['bgcolor'] ) ) {
            $bgcolor = sanitize_hex_color_no_hash( $_POST['bgcolor'] );
            if ( empty( $bgcolor ) && ! empty( $_POST['bgcolor'] ) ) {
                wp_send_json_error( array(
                    'message' => __( 'Please provide a valid background hex color.', 'qr-code-generator' ),
                ), 400 );
            }
            $options['bgcolor'] = $bgcolor;
        }
        if ( isset( $_POST['ecc'] ) ) {
            $options['ecc'] = sanitize_text_field( $_POST['ecc'] );
        }

        // Sanitize options
        $options = $this->generator->sanitize_options( $options );
        $options = wp_parse_args( $options, array(
            'size'    => 300,
            'margin'  => 0,
            'color'   => '000000',
            'bgcolor' => 'ffffff',
            'ecc'     => 'M',
        ) );

        $contrast = QR_Generator::calculate_contrast_ratio( $options['color'], $options['bgcolor'] );
        if ( $contrast < 2.5 ) {
            wp_send_json_error( array(
                'message' => __( 'QR and background colors are too similar. Increase contrast.', 'qr-code-generator' ),
            ), 400 );
        }

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
