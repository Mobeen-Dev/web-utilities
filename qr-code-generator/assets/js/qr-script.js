/**
 * QR Code Generator JavaScript
 *
 * @package QR_Code_Generator
 */

(function ($) {
    'use strict';

    /**
     * QR Code Generator Object
     */
    const QRCodeGen = {
        
        // Current QR code data
        currentQRData: null,
        currentFormat: 'png',
        currentUrl: '',

        /**
         * Initialize
         */
        init: function () {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function () {
            // Generate button click
            $(document).on('click', '#qr-generate-btn', this.generateQRCode.bind(this));

            // Enter key on input
            $(document).on('keypress', '#qr-url-input', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    QRCodeGen.generateQRCode();
                }
            });

            // Download button click
            $(document).on('click', '#qr-download-btn', this.downloadQRCode.bind(this));

            // Generate new button click
            $(document).on('click', '#qr-generate-new', this.resetForm.bind(this));

            // Toggle options
            $(document).on('click', '.qr-toggle-options', function () {
                $('.qr-options-panel').slideToggle();
                $(this).toggleClass('active');
            });

            // Format change
            $(document).on('change', '#qr-format', function () {
                QRCodeGen.currentFormat = $(this).val();
            });
        },

        /**
         * Validate URL
         */
        validateUrl: function (url) {
            const pattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
            return pattern.test(url);
        },

        /**
         * Show message
         */
        showMessage: function (message, type = 'error') {
            const $message = $('.qr-message');
            $message
                .removeClass('qr-error qr-success qr-info')
                .addClass('qr-' + type)
                .html(message)
                .fadeIn();

            if (type === 'success') {
                setTimeout(function () {
                    $message.fadeOut();
                }, 3000);
            }
        },

        /**
         * Hide message
         */
        hideMessage: function () {
            $('.qr-message').fadeOut();
        },

        /**
         * Show loading state
         */
        showLoading: function () {
            const $btn = $('#qr-generate-btn');
            $btn.prop('disabled', true).addClass('loading');
            $btn.html('<span class="spinner"></span>' + qrCodeGenerator.strings.generating);
        },

        /**
         * Hide loading state
         */
        hideLoading: function () {
            const $btn = $('#qr-generate-btn');
            const originalText = $btn.data('original-text') || qrCodeGenerator.strings.generate || 'Generate QR Code';
            $btn.prop('disabled', false).removeClass('loading').text(originalText);
        },

        /**
         * Generate QR Code
         */
        generateQRCode: function () {
            const url = $('#qr-url-input').val().trim();

            // Validate URL
            if (!url) {
                this.showMessage(qrCodeGenerator.strings.invalidUrl, 'error');
                return;
            }

            if (!this.validateUrl(url)) {
                this.showMessage(qrCodeGenerator.strings.invalidUrl, 'error');
                return;
            }

            this.hideMessage();
            this.showLoading();

            // Get options
            const format = $('#qr-format').val() || 'png';
            const size = $('#qr-size').val() || 300;
            const margin = $('#qr-margin').val() || 0;
            const color = $('#qr-color').val() ? $('#qr-color').val().replace('#', '') : '000000';
            const bgcolor = $('#qr-bgcolor').val() ? $('#qr-bgcolor').val().replace('#', '') : 'ffffff';

            // Store current data
            this.currentUrl = url;
            this.currentFormat = format;

            // AJAX request
            $.ajax({
                url: qrCodeGenerator.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'generate_qr_code',
                    nonce: qrCodeGenerator.nonce,
                    url: url,
                    format: format,
                    size: size,
                    margin: margin,
                    color: color,
                    bgcolor: bgcolor
                },
                success: function (response) {
                    QRCodeGen.hideLoading();

                    if (response.success) {
                        QRCodeGen.displayQRCode(response.data);
                    } else {
                        QRCodeGen.showMessage(response.data.message || qrCodeGenerator.strings.error, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    QRCodeGen.hideLoading();
                    QRCodeGen.showMessage(qrCodeGenerator.strings.error, 'error');
                    console.error('QR Code generation error:', error);
                }
            });
        },

        /**
         * Display QR Code
         */
        displayQRCode: function (data) {
            this.currentQRData = data;
            
            const $display = $('#qr-code-display');
            $display.empty();

            if (data.format === 'svg') {
                // Display SVG
                $display.html(data.content);
            } else {
                // Display PNG
                const img = $('<img>')
                    .attr('src', data.dataUrl)
                    .attr('alt', 'QR Code')
                    .addClass('qr-code-image');
                $display.append(img);
            }

            // Show result container
            $('.qr-result-container').slideDown();
            
            // Scroll to result
            $('html, body').animate({
                scrollTop: $('.qr-result-container').offset().top - 100
            }, 500);
        },

        /**
         * Download QR Code
         */
        downloadQRCode: function () {
            if (!this.currentQRData) {
                this.showMessage(qrCodeGenerator.strings.error, 'error');
                return;
            }

            const format = this.currentQRData.format;
            const dataUrl = this.currentQRData.dataUrl;
            const filename = 'qrcode-' + Date.now() + '.' + format;

            // Create download link
            const link = document.createElement('a');
            link.href = dataUrl;
            link.download = filename;
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            this.showMessage(qrCodeGenerator.strings.downloadSuccess || 'Downloaded successfully!', 'success');
        },

        /**
         * Reset form
         */
        resetForm: function () {
            $('#qr-url-input').val('');
            $('.qr-result-container').slideUp();
            $('.qr-options-panel').slideUp();
            $('.qr-toggle-options').removeClass('active');
            this.hideMessage();
            this.currentQRData = null;
            $('#qr-url-input').focus();
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function () {
        QRCodeGen.init();
        
        // Store original button text
        $('#qr-generate-btn').data('original-text', $('#qr-generate-btn').text());
    });

})(jQuery);
