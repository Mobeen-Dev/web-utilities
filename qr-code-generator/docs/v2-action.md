# Action Plan: QR Code Generator Issues

This document lists actionable fixes for the reported UX and QR-generation issues. Each item maps to current files and proposes concrete changes and acceptance checks.

## Scope
- Frontend UI/UX (HTML/CSS/JS in shortcode, assets)
- Client-side validation and feedback
- Server-side validation and QR generation options

## Assumptions
- URL length limit: 2048 characters.
- Share button uses Web Share API with a copy-link fallback.

---

## 1) Responsive design breaks on mobile
**Problem:** Layout breaks on small screens (form, options panel, result actions).  
**Current state:** Single breakpoint at 768px; large paddings and fixed sizing.  
**Actions:**
- Add a smaller breakpoint (<= 480px) to reduce padding and font sizes.
- Ensure `.qr-code-generator-wrapper` and `.qr-generator-container` are full width on mobile.
- Make `.qr-options-panel` and `.qr-download-actions` use full-width, stacked controls.
- Add `box-sizing: border-box` for form inputs to avoid overflow.
**Files:**  
- `assets/css/qr-style.css`  
- `includes/class-qr-shortcode.php` (if structure adjustments needed)
**Acceptance checks:**  
- No horizontal scroll at 360px width.  
- Buttons and inputs remain full width and readable.  
- Result actions stack cleanly with consistent spacing.

---

## 2) SVG preview is cropped (only partial SVG visible)
**Problem:** SVG preview appears clipped in the UI, though download is correct.  
**Likely cause:** Raw SVG is injected with fixed width/height and lacks responsive scaling.  
**Actions:**
- After injecting SVG, remove fixed `width`/`height` attributes and ensure `viewBox` exists; set `width="100%"` and `height="auto"` or apply inline style for responsiveness.
- Set `.qr-code-display` to `width: 100%` and ensure `svg` is `display: block` with `max-width: 100%`.
- If needed, wrap SVG in a container with responsive sizing and `overflow: visible`.
**Files:**  
- `assets/js/qr-script.js`  
- `assets/css/qr-style.css`
**Acceptance checks:**  
- SVG preview scales down to fit container without clipping at sizes up to 1200px.  
- PNG and SVG previews look consistent in size.

---

## 3) Input limits for size and user inputs (URL length, margin, etc.)
**Problem:** No explicit limits or feedback for URL length and other inputs.  
**Actions:**
- Add `maxlength="2048"` to the URL input and a helper message for limits.
- Client-side validation for size (100-1000) and margin (0-50) with inline error message.
- Server-side checks for URL length and out-of-range inputs; return actionable errors.
**Files:**  
- `includes/class-qr-shortcode.php`  
- `assets/js/qr-script.js`  
- `includes/class-qr-ajax-handler.php`  
- `includes/class-qr-generator.php` (if default limits need tightening)
**Acceptance checks:**  
- Over-limit URL is rejected with a clear message before submit.  
- Server rejects invalid sizes/margins with a 400 error and readable text.

---

## 4) Color input UX (Hex + picker, accessible and self-explanatory)
**Problem:** Current color inputs are not user-friendly or self-explanatory.  
**Actions:**
- Replace single color input with a paired control: Hex text input + color picker, synchronized.
- Add clear labels, example placeholder (e.g., `#000000`), and helper text.
- Add `aria-describedby` for instructions and ensure sufficient contrast for labels.
**Files:**  
- `includes/class-qr-shortcode.php`  
- `assets/js/qr-script.js`  
- `assets/css/qr-style.css`
**Acceptance checks:**  
- Users can type hex or use the picker and see instant sync.  
- Non-technical users can understand the color fields without trial-and-error.

---

## 5) Loader/feedback UI is confusing (material-style feedback)
**Problem:** Loader text and styling overlap, making states unclear.  
**Actions:**
- Keep button label visible; show spinner to the left (no text masking).
- Add a small status line under the button with `aria-live="polite"`.
- Disable inputs during generation and re-enable after completion.
- Ensure only one visual loading indicator is shown at a time.
**Files:**  
- `assets/js/qr-script.js`  
- `assets/css/qr-style.css`
**Acceptance checks:**  
- Only one loader is visible, with clear progress state.  
- Button state remains readable and consistent.

---

## 6) Share button for site promotion
**Problem:** No sharing action available to promote the site.  
**Actions:**
- Add "Share this page" button next to download actions.
- Use Web Share API (`navigator.share`) with fallback to copy the current page URL.
- Show success message when link is copied or share completes.
**Files:**  
- `includes/class-qr-shortcode.php`  
- `assets/js/qr-script.js`  
- `assets/css/qr-style.css`  
- `qr-code-generator.php` (localize new strings)
**Acceptance checks:**  
- On supported devices, native share sheet opens.  
- On desktop, link is copied with a success message.

---

## 7) Dark color combinations produce unreadable QR codes
**Problem:** Dark foreground + dark background makes QR code invalid or unreadable.  
**Likely cause:** Low contrast + low error correction (default L) + zero margin.  
**Actions:**
- Add contrast validation between `color` and `bgcolor`; block or warn if below threshold.
- Consider bumping default error correction to M or Q and/or add ECC selector to options.
- Enforce a minimum margin (e.g., 2-4) when contrast is low or for dark backgrounds.
**Files:**  
- `assets/js/qr-script.js`  
- `includes/class-qr-ajax-handler.php`  
- `includes/class-qr-generator.php`  
- `includes/class-qr-shortcode.php` (ECC selector if added)
**Acceptance checks:**  
- Low-contrast combinations show a clear warning or are blocked.  
- Generated QR codes remain scannable with dark palettes.

---

## Deliverables
- Updated UI/validation logic and styling per issues above.
- Clear, user-friendly feedback and accessibility improvements.
