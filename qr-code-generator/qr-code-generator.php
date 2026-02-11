<?php
/**
 * Plugin Name: QR Code Generator
 * Plugin URI: https://yourwebsite.com/qr-code-generator
 * Description: Generate QR codes from URLs in SVG and PNG formats with easy download options.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: qr-code-generator
 * Domain Path: /languages
 *
 * @package QR_Code_Generator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'QR_CODE_GENERATOR_VERSION', '1.0.0' );
define( 'QR_CODE_GENERATOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'QR_CODE_GENERATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'QR_CODE_GENERATOR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class.
 */
class QR_Code_Generator {

    /**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Get instance of the class.
     *
     * @return QR_Code_Generator
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
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required dependencies.
     */
    private function load_dependencies() {
        require_once QR_CODE_GENERATOR_PLUGIN_DIR . 'includes/class-qr-generator.php';
        require_once QR_CODE_GENERATOR_PLUGIN_DIR . 'includes/class-qr-ajax-handler.php';
        require_once QR_CODE_GENERATOR_PLUGIN_DIR . 'includes/class-qr-shortcode.php';
    }

    /**
     * Initialize WordPress hooks.
     */
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        // Initialize AJAX handler
        QR_Ajax_Handler::get_instance();
        
        // Initialize shortcode
        QR_Shortcode::get_instance();
    }

    /**
     * Load plugin textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'qr-code-generator',
            false,
            dirname( QR_CODE_GENERATOR_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Enqueue frontend scripts and styles.
     */
    public function enqueue_scripts() {
        // Enqueue styles
        wp_enqueue_style(
            'qr-code-generator-style',
            QR_CODE_GENERATOR_PLUGIN_URL . 'assets/css/qr-style.css',
            array(),
            QR_CODE_GENERATOR_VERSION
        );

        // Enqueue scripts
        wp_enqueue_script(
            'qr-code-generator-script',
            QR_CODE_GENERATOR_PLUGIN_URL . 'assets/js/qr-script.js',
            array( 'jquery' ),
            QR_CODE_GENERATOR_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            'qr-code-generator-script',
            'qrCodeGenerator',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'qr_code_nonce' ),
                'strings' => array(
                    'error'          => __( 'An error occurred. Please try again.', 'qr-code-generator' ),
                    'invalidUrl'     => __( 'Please enter a valid URL.', 'qr-code-generator' ),
                    'generating'     => __( 'Generating QR Code...', 'qr-code-generator' ),
                    'downloadSuccess' => __( 'QR Code downloaded successfully!', 'qr-code-generator' ),
                    'urlTooLong'     => __( 'URL is too long (max 2048 characters).', 'qr-code-generator' ),
                    'invalidSize'    => __( 'Size must be between 100 and 1000.', 'qr-code-generator' ),
                    'invalidMargin'  => __( 'Margin must be between 0 and 50.', 'qr-code-generator' ),
                    'invalidColor'   => __( 'Please enter valid hex colors.', 'qr-code-generator' ),
                    'lowContrast'    => __( 'QR and background colors are too similar. Increase contrast.', 'qr-code-generator' ),
                    'success'        => __( 'QR Code ready.', 'qr-code-generator' ),
                    'shareText'      => __( 'Check out this QR code generator!', 'qr-code-generator' ),
                    'shareSuccess'   => __( 'Thanks for sharing!', 'qr-code-generator' ),
                    'shareCanceled'  => __( 'Share canceled.', 'qr-code-generator' ),
                    'copySuccess'    => __( 'Link copied to clipboard.', 'qr-code-generator' ),
                    'copyError'      => __( 'Unable to copy link.', 'qr-code-generator' ),
                ),
            )
        );
    }
}

/**
 * Initialize the plugin.
 */
function qr_code_generator_init() {
    return QR_Code_Generator::get_instance();
}

// Start the plugin.
qr_code_generator_init();
