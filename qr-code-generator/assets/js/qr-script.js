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
        maxUrlLength: 2048,

        /**
         * Initialize
         */
        init: function () {
            this.bindEvents();
            this.syncHexInputs();
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

            // Color sync: picker -> hex
            $(document).on('input', '#qr-color, #qr-bgcolor', function () {
                QRCodeGen.syncPickerToHex($(this));
            });

            // Color sync: hex -> picker
            $(document).on('input', '#qr-color-hex, #qr-bgcolor-hex', function () {
                QRCodeGen.syncHexToPicker($(this));
            });

            // Share button
            $(document).on('click', '#qr-share-btn', this.sharePage.bind(this));
        },

        /**
         * Validate URL
         */
        validateUrl: function (url) {
            const pattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
            return pattern.test(url);
        },

        /**
         * Validate hex color
         */
        validateHex: function (value) {
            return /^#?[A-Fa-f0-9]{6}$/.test(value);
        },

        /**
         * Normalize hex to #rrggbb
         */
        normalizeHex: function (value, fallback = '#000000') {
            if (!value) return fallback;
            const trimmed = value.trim().replace('#', '');
            if (trimmed.length === 3) {
                return (
                    '#' +
                    trimmed
                        .split('')
                        .map((c) => c + c)
                        .join('')
                );
            }
            if (this.validateHex(trimmed)) {
                return '#' + trimmed.toLowerCase();
            }
            return fallback;
        },

        /**
         * Color picker -> hex input sync
         */
        syncPickerToHex: function ($picker) {
            const id = $picker.attr('id');
            const hexInputId = id === 'qr-color' ? '#qr-color-hex' : '#qr-bgcolor-hex';
            const normalized = this.normalizeHex($picker.val());
            $(hexInputId).val(normalized);
        },

        /**
         * Hex input -> color picker sync
         */
        syncHexToPicker: function ($hex) {
            const id = $hex.attr('id');
            const pickerId = id === 'qr-color-hex' ? '#qr-color' : '#qr-bgcolor';
            const normalized = this.normalizeHex($hex.val());
            $(pickerId).val(normalized);
        },

        /**
         * Initial sync
         */
        syncHexInputs: function () {
            this.syncPickerToHex($('#qr-color'));
            this.syncPickerToHex($('#qr-bgcolor'));
        },

        /**
         * Contrast ratio
         */
        contrastRatio: function (hex1, hex2) {
            const toRgb = (hex) => {
                const h = hex.replace('#', '');
                return [
                    parseInt(h.substring(0, 2), 16),
                    parseInt(h.substring(2, 4), 16),
                    parseInt(h.substring(4, 6), 16),
                ];
            };
            const luminance = (rgb) => {
                const [r, g, b] = rgb.map((v) => {
                    const c = v / 255;
                    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                });
                return 0.2126 * r + 0.7152 * g + 0.0722 * b;
            };
            const l1 = luminance(toRgb(hex1));
            const l2 = luminance(toRgb(hex2));
            const bright = Math.max(l1, l2);
            const dark = Math.min(l1, l2);
            return (bright + 0.05) / (dark + 0.05);
        },

        /**
         * Set status text
         */
        setStatus: function (text) {
            $('.qr-status').text(text || '');
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
            if ($btn.find('.spinner').length === 0) {
                $btn.prepend('<span class="spinner" aria-hidden="true"></span>');
            }
            this.setStatus(qrCodeGenerator.strings.generating || 'Generating QR Code...');
        },

        /**
         * Hide loading state
         */
        hideLoading: function () {
            const $btn = $('#qr-generate-btn');
            const originalText = $btn.data('original-text') || qrCodeGenerator.strings.generate || 'Generate QR Code';
            $btn.prop('disabled', false).removeClass('loading');
            $btn.find('.spinner').remove();
            $btn.text(originalText);
            this.setStatus('');
        },

        /**
         * Generate QR Code
         */
        generateQRCode: function () {
            const url = $('#qr-url-input').val().trim();
            const size = parseInt($('#qr-size').val(), 10) || 300;
            const margin = parseInt($('#qr-margin').val(), 10) || 0;
            const ecc = $('#qr-ecc').val() || 'M';
            const color = this.normalizeHex($('#qr-color-hex').val(), '#000000');
            const bgcolor = this.normalizeHex($('#qr-bgcolor-hex').val(), '#ffffff');

            // Validate URL
            if (!url) {
                this.showMessage(qrCodeGenerator.strings.invalidUrl, 'error');
                return;
            }

            if (!this.validateUrl(url)) {
                this.showMessage(qrCodeGenerator.strings.invalidUrl, 'error');
                return;
            }

            if (url.length > this.maxUrlLength) {
                this.showMessage(qrCodeGenerator.strings.urlTooLong || 'URL is too long (max 2048 characters).', 'error');
                return;
            }

            if (size < 100 || size > 1000) {
                this.showMessage(qrCodeGenerator.strings.invalidSize || 'Size must be between 100 and 1000.', 'error');
                return;
            }

            if (margin < 0 || margin > 50) {
                this.showMessage(qrCodeGenerator.strings.invalidMargin || 'Margin must be between 0 and 50.', 'error');
                return;
            }

            if (!this.validateHex(color) || !this.validateHex(bgcolor)) {
                this.showMessage(qrCodeGenerator.strings.invalidColor || 'Please enter valid hex colors.', 'error');
                return;
            }

            const contrast = this.contrastRatio(color.replace('#', ''), bgcolor.replace('#', ''));
            if (contrast < 2.5) {
                this.showMessage(qrCodeGenerator.strings.lowContrast || 'QR and background colors are too similar. Please increase contrast.', 'error');
                return;
            }

            this.hideMessage();
            this.showLoading();
            this.setStatus(qrCodeGenerator.strings.generating || 'Generating QR Code...');

            // Get options
            const format = $('#qr-format').val() || 'png';
            const colorVal = color.replace('#', '');
            const bgVal = bgcolor.replace('#', '');

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
                    color: colorVal,
                    bgcolor: bgVal,
                    ecc: ecc
                },
                success: function (response) {
                    QRCodeGen.hideLoading();

                    if (response.success) {
                        QRCodeGen.displayQRCode(response.data);
                        QRCodeGen.setStatus(qrCodeGenerator.strings.success || 'QR Code ready.');
                    } else {
                        QRCodeGen.showMessage(response.data.message || qrCodeGenerator.strings.error, 'error');
                        QRCodeGen.setStatus('');
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
                const $svg = $display.find('svg').first();
                if ($svg.length) {
                    const width = $svg.attr('width');
                    const height = $svg.attr('height');
                    if (!$svg.attr('viewBox') && width && height) {
                        const w = parseInt(width, 10) || 1200;
                        const h = parseInt(height, 10) || 1200;
                        $svg.attr('viewBox', `0 0 ${w} ${h}`);
                    }
                    $svg.removeAttr('width').removeAttr('height');
                    $svg.attr('preserveAspectRatio', 'xMidYMid meet');
                    $svg.css({ width: '100%', height: 'auto', display: 'block' });
                }
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
            this.setStatus('');
        },

        /**
         * Share page
         */
        sharePage: function () {
            const shareData = {
                title: document.title || 'QR Code Generator',
                text: qrCodeGenerator.strings.shareText || 'Check out this QR code generator!',
                url: window.location.href,
            };

            if (navigator.share) {
                navigator.share(shareData).then(() => {
                    this.showMessage(qrCodeGenerator.strings.shareSuccess || 'Thanks for sharing!', 'success');
                }).catch(() => {
                    this.showMessage(qrCodeGenerator.strings.shareCanceled || 'Share canceled.', 'info');
                });
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareData.url).then(() => {
                    this.showMessage(qrCodeGenerator.strings.copySuccess || 'Link copied to clipboard.', 'success');
                }).catch(() => {
                    this.showMessage(qrCodeGenerator.strings.copyError || 'Unable to copy link.', 'error');
                });
            } else {
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(shareData.url).select();
                const success = document.execCommand('copy');
                $temp.remove();
                if (success) {
                    this.showMessage(qrCodeGenerator.strings.copySuccess || 'Link copied to clipboard.', 'success');
                } else {
                    this.showMessage(qrCodeGenerator.strings.copyError || 'Unable to copy link.', 'error');
                }
            }
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
