<?php
/**
 * QR Code Generator Class
 *
 * Handles QR code generation in SVG and PNG formats.
 *
 * @package QR_Code_Generator
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class QR_Generator
 */
class QR_Generator {

    /**
     * QR Code API endpoint.
     *
     * @var string
     */
    private $api_endpoint = 'https://api.qrserver.com/v1/create-qr-code/';

    /**
     * Generate QR code.
     *
     * @param string $url    URL to encode.
     * @param string $format Format (svg or png).
     * @param array  $args   Additional arguments.
     * @return array|WP_Error
     */
    public function generate( $url, $format = 'png', $args = array() ) {
        // Validate URL
        if ( ! $this->validate_url( $url ) ) {
            return new WP_Error( 'invalid_url', __( 'Invalid URL provided.', 'qr-code-generator' ) );
        }

        // Sanitize format
        $format = in_array( $format, array( 'svg', 'png' ), true ) ? $format : 'png';

        // Default arguments
        $defaults = array(
            'size'       => 300,
            'margin'     => 0,
            'color'      => '000000',
            'bgcolor'    => 'ffffff',
            'ecc'        => 'M', // Error correction level: L, M, Q, H
        );

        $args = wp_parse_args( $args, $defaults );

        // Generate QR code based on format
        if ( 'svg' === $format ) {
            return $this->generate_svg( $url, $args );
        } else {
            return $this->generate_png( $url, $args );
        }
    }

    /**
     * Generate SVG QR code.
     *
     * @param string $url  URL to encode.
     * @param array  $args Arguments.
     * @return array|WP_Error
     */
    private function generate_svg( $url, $args ) {
        $params = array(
            'data'   => $url,
            'size'   => $args['size'] . 'x' . $args['size'],
            'format' => 'svg',
            'margin' => $args['margin'],
            'color'  => $args['color'],
            'bgcolor' => $args['bgcolor'],
            'ecc'    => $args['ecc'],
        );

        $api_url = add_query_arg( $params, $this->api_endpoint );

        $response = wp_remote_get( $api_url, array(
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );

        if ( 200 !== $code ) {
            return new WP_Error( 'api_error', __( 'Failed to generate QR code.', 'qr-code-generator' ) );
        }

        return array(
            'format'  => 'svg',
            'content' => $body,
            'mime'    => 'image/svg+xml',
        );
    }

    /**
     * Generate PNG QR code.
     *
     * @param string $url  URL to encode.
     * @param array  $args Arguments.
     * @return array|WP_Error
     */
    private function generate_png( $url, $args ) {
        $params = array(
            'data'   => $url,
            'size'   => $args['size'] . 'x' . $args['size'],
            'format' => 'png',
            'margin' => $args['margin'],
            'color'  => $args['color'],
            'bgcolor' => $args['bgcolor'],
            'ecc'    => $args['ecc'],
        );

        $api_url = add_query_arg( $params, $this->api_endpoint );

        $response = wp_remote_get( $api_url, array(
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );

        if ( 200 !== $code ) {
            return new WP_Error( 'api_error', __( 'Failed to generate QR code.', 'qr-code-generator' ) );
        }

        return array(
            'format'  => 'png',
            'content' => base64_encode( $body ),
            'mime'    => 'image/png',
        );
    }

    /**
     * Validate URL.
     *
     * @param string $url URL to validate.
     * @return bool
     */
    private function validate_url( $url ) {
        // Basic validation
        if ( empty( $url ) ) {
            return false;
        }

        // WordPress URL validation
        return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
    }

    /**
     * Sanitize QR code options.
     *
     * @param array $options Options to sanitize.
     * @return array
     */
    public function sanitize_options( $options ) {
        $sanitized = array();

        if ( isset( $options['size'] ) ) {
            $sanitized['size'] = absint( $options['size'] );
            $sanitized['size'] = min( max( $sanitized['size'], 100 ), 1000 ); // Between 100-1000
        }

        if ( isset( $options['margin'] ) ) {
            $sanitized['margin'] = absint( $options['margin'] );
            $sanitized['margin'] = min( $sanitized['margin'], 50 ); // Max 50
        }

        if ( isset( $options['color'] ) ) {
            $sanitized['color'] = sanitize_hex_color_no_hash( $options['color'] );
        }

        if ( isset( $options['bgcolor'] ) ) {
            $sanitized['bgcolor'] = sanitize_hex_color_no_hash( $options['bgcolor'] );
        }

        if ( isset( $options['ecc'] ) ) {
            $sanitized['ecc'] = in_array( $options['ecc'], array( 'L', 'M', 'Q', 'H' ), true ) 
                ? $options['ecc'] 
                : 'M';
        }

        return $sanitized;
    }

    /**
     * Calculate contrast ratio between two hex colors (without #).
     *
     * @param string $hex1 Hex color.
     * @param string $hex2 Hex color.
     * @return float
     */
    public static function calculate_contrast_ratio( $hex1, $hex2 ) {
        $hex1 = ltrim( $hex1, '#' );
        $hex2 = ltrim( $hex2, '#' );

        $rgb1 = self::hex_to_rgb( $hex1 );
        $rgb2 = self::hex_to_rgb( $hex2 );

        $l1 = self::relative_luminance( $rgb1 );
        $l2 = self::relative_luminance( $rgb2 );

        $bright = max( $l1, $l2 );
        $dark   = min( $l1, $l2 );

        return ( $bright + 0.05 ) / ( $dark + 0.05 );
    }

    /**
     * Convert hex to RGB.
     *
     * @param string $hex Hex string.
     * @return array
     */
    private static function hex_to_rgb( $hex ) {
        $hex = str_pad( $hex, 6, '0' );
        return array(
            hexdec( substr( $hex, 0, 2 ) ),
            hexdec( substr( $hex, 2, 2 ) ),
            hexdec( substr( $hex, 4, 2 ) ),
        );
    }

    /**
     * Calculate relative luminance.
     *
     * @param array $rgb RGB array.
     * @return float
     */
    private static function relative_luminance( $rgb ) {
        list( $r, $g, $b ) = $rgb;
        $map = array( $r, $g, $b );
        foreach ( $map as &$c ) {
            $c = $c / 255;
            $c = ( $c <= 0.03928 ) ? ( $c / 12.92 ) : pow( ( $c + 0.055 ) / 1.055, 2.4 );
        }
        return 0.2126 * $map[0] + 0.7152 * $map[1] + 0.0722 * $map[2];
    }
}
