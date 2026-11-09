<?php
/**
 * Cưng Store - Floating Contact Widget (Zalo, Hotline, Messenger)
 *
 * @package Cung_Widgets
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Floating_Contact {

    public static function init() {
        add_action('wp_footer', [__CLASS__, 'render_floating_widget']);
    }

    public static function render_floating_widget() {
        $hotline_disp = get_option('cung_contact_hotline', '093 144 23 24');
        $hotline_raw  = preg_replace('/[^0-9]/', '', $hotline_disp);
        if (empty($hotline_raw)) {
            $hotline_raw = '0931442324';
        }

        $zalo_raw   = get_option('cung_contact_zalo', '0931442324');
        $zalo_clean = preg_replace('/[^0-9]/', '', $zalo_raw);
        if (empty($zalo_clean)) {
            $zalo_clean = '0931442324';
        }
        $zalo_link = 'https://zalo.me/' . $zalo_clean;

        $messenger_url = get_option('cung_contact_messenger', 'https://m.me/sontruongnguyenn');
        ?>
        <!-- CUNG STORE FLOATING CONTACT BUTTONS -->
        <div id="cung-floating-contact" class="cung-floating-contact" aria-label="Kênh liên hệ nhanh Cưng Store">
            
            <!-- 1. ZALO BUTTON -->
            <a href="<?php echo esc_url($zalo_link); ?>" target="_blank" rel="noopener noreferrer" class="cung-contact-item cung-contact-zalo" aria-label="Chat Zalo tư vấn">
                <span class="cung-contact-pulse"></span>
                <span class="cung-contact-icon">
                    <svg viewBox="0 0 48 48" width="28" height="28" fill="none">
                        <circle cx="24" cy="24" r="24" fill="#0068FF"/>
                        <path d="M24 10C16.27 10 10 15.6 10 22.5c0 3.96 2.05 7.48 5.25 9.77l-1.37 5.09c-.19.7.46 1.28 1.12.99l5.96-2.61c.97.17 1.98.26 3.04.26 7.73 0 14-5.6 14-12.5S31.73 10 24 10z" fill="#FFF"/>
                        <path d="M19.2 25.5h-3.6c-.44 0-.8-.36-.8-.8v-5.4c0-.44.36-.8.8-.8h3.6c.44 0 .8.36.8.8v5.4c0 .44-.36.8-.8.8zm8.6-4.9l-2.7 3.9h2.7c.44 0 .8.36.8.8s-.36.8-.8.8h-4.6c-.36 0-.68-.24-.77-.59-.09-.35.04-.72.33-.94l3.1-4.4h-2.5c-.44 0-.8-.36-.8-.8s.36-.8.8-.8h4.4c.36 0 .68.24.77.59.09.35-.04.72-.33.94z" fill="#0068FF"/>
                    </svg>
                </span>
                <span class="cung-contact-label">Chat Zalo</span>
            </a>

            <!-- 2. HOTLINE BUTTON -->
            <a href="tel:<?php echo esc_attr($hotline_raw); ?>" class="cung-contact-item cung-contact-phone" aria-label="Gọi ngay <?php echo esc_attr($hotline_disp); ?>">
                <span class="cung-contact-pulse"></span>
                <span class="cung-contact-icon cung-phone-ringing">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </span>
                <span class="cung-contact-label"><?php echo esc_html($hotline_disp); ?></span>
            </a>

            <!-- 3. MESSENGER BUTTON -->
            <a href="<?php echo esc_url($messenger_url); ?>" target="_blank" rel="noopener noreferrer" class="cung-contact-item cung-contact-messenger" aria-label="Chat Facebook Messenger">
                <span class="cung-contact-pulse"></span>
                <span class="cung-contact-icon">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor">
                        <path d="M12 2C6.477 2 2 6.145 2 11.258c0 2.914 1.455 5.518 3.735 7.205V22l3.371-1.85c.918.254 1.89.39 2.894.39 5.523 0 10-4.145 10-9.258C22 6.145 17.523 2 12 2zm1.045 12.443l-2.614-2.788-5.1 2.788 5.61-5.955 2.68 2.788 5.034-2.788-5.61 5.955z"/>
                    </svg>
                </span>
                <span class="cung-contact-label">Messenger</span>
            </a>

        </div>
        <!-- / CUNG STORE FLOATING CONTACT BUTTONS -->
        <?php
    }
}

Cung_Floating_Contact::init();
