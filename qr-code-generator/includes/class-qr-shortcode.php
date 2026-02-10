<?php
/**
 * Shortcode Class
 *
 * Handles shortcode registration and rendering.
 *
 * @package QR_Code_Generator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class QR_Shortcode
 */
class QR_Shortcode {

    /**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Get instance of the class.
     *
     * @return QR_Shortcode
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
        add_shortcode( 'qr_code_generator', array( $this, 'render_shortcode' ) );
    }

    /**
     * Render shortcode.
     *
     * Usage: [qr_code_generator]
     * With attributes: [qr_code_generator title="Get Your QR Code" button_text="Generate"]
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_shortcode( $atts ) {
        // Parse attributes
        $atts = shortcode_atts( array(
            'title'            => __( 'Free QR Code Generator', 'qr-code-generator' ),
            'subtitle'         => __( 'Generate QR codes from your URLs instantly', 'qr-code-generator' ),
            'button_text'      => __( 'Generate QR Code', 'qr-code-generator' ),
            'placeholder'      => __( 'Enter your URL here...', 'qr-code-generator' ),
            'show_options'     => 'yes',
            'default_size'     => '300',
            'default_format'   => 'png',
            'theme'            => 'default', // default, minimal, modern
        ), $atts, 'qr_code_generator' );

        // Start output buffering
        ob_start();
        ?>
        <div class="qr-code-generator-wrapper qr-theme-<?php echo esc_attr( $atts['theme'] ); ?>">
            <div class="qr-generator-container">
                
                <?php if ( ! empty( $atts['title'] ) ) : ?>
                    <h2 class="qr-generator-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                <?php endif; ?>
                
                <?php if ( ! empty( $atts['subtitle'] ) ) : ?>
                    <p class="qr-generator-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
                <?php endif; ?>

                <div class="qr-generator-form">
                    <div class="qr-input-group">
                        <input 
                            type="url" 
                            id="qr-url-input" 
                            class="qr-url-input" 
                            placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
                            required
                        />
                        <button 
                            type="button" 
                            id="qr-generate-btn" 
                            class="qr-generate-btn"
                        >
                            <?php echo esc_html( $atts['button_text'] ); ?>
                        </button>
                    </div>

                    <?php if ( 'yes' === $atts['show_options'] ) : ?>
                        <div class="qr-options-toggle">
                            <button type="button" class="qr-toggle-options">
                                <span class="dashicons dashicons-admin-generic"></span>
                                <?php esc_html_e( 'Customize Options', 'qr-code-generator' ); ?>
                            </button>
                        </div>

                        <div class="qr-options-panel" style="display: none;">
                            <div class="qr-option-group">
                                <label for="qr-format">
                                    <?php esc_html_e( 'Format:', 'qr-code-generator' ); ?>
                                </label>
                                <select id="qr-format" class="qr-format-select">
                                    <option value="png" <?php selected( $atts['default_format'], 'png' ); ?>>PNG</option>
                                    <option value="svg" <?php selected( $atts['default_format'], 'svg' ); ?>>SVG</option>
                                </select>
                            </div>

                            <div class="qr-option-group">
                                <label for="qr-size">
                                    <?php esc_html_e( 'Size (px):', 'qr-code-generator' ); ?>
                                </label>
                                <input 
                                    type="number" 
                                    id="qr-size" 
                                    class="qr-size-input" 
                                    min="100" 
                                    max="1000" 
                                    value="<?php echo esc_attr( $atts['default_size'] ); ?>"
                                />
                            </div>

                            <div class="qr-option-group">
                                <label for="qr-margin">
                                    <?php esc_html_e( 'Margin:', 'qr-code-generator' ); ?>
                                </label>
                                <input 
                                    type="number" 
                                    id="qr-margin" 
                                    class="qr-margin-input" 
                                    min="0" 
                                    max="50" 
                                    value="0"
                                />
                            </div>

                            <div class="qr-option-group">
                                <label for="qr-color">
                                    <?php esc_html_e( 'Color:', 'qr-code-generator' ); ?>
                                </label>
                                <input 
                                    type="color" 
                                    id="qr-color" 
                                    class="qr-color-input" 
                                    value="#000000"
                                />
                            </div>

                            <div class="qr-option-group">
                                <label for="qr-bgcolor">
                                    <?php esc_html_e( 'Background:', 'qr-code-generator' ); ?>
                                </label>
                                <input 
                                    type="color" 
                                    id="qr-bgcolor" 
                                    class="qr-bgcolor-input" 
                                    value="#ffffff"
                                />
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="qr-message" style="display: none;"></div>
                </div>

                <div class="qr-result-container" style="display: none;">
                    <div class="qr-preview">
                        <h3><?php esc_html_e( 'Your QR Code', 'qr-code-generator' ); ?></h3>
                        <div id="qr-code-display" class="qr-code-display"></div>
                    </div>

                    <div class="qr-download-actions">
                        <button type="button" id="qr-download-btn" class="qr-download-btn">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Download QR Code', 'qr-code-generator' ); ?>
                        </button>
                        <button type="button" id="qr-generate-new" class="qr-generate-new">
                            <?php esc_html_e( 'Generate New', 'qr-code-generator' ); ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}
