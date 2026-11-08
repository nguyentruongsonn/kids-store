<?php
/**
 * Cưng Store - Side-Cart Drawer & Navigation Dropdown Enhancements
 *
 * @package Cung_Widgets
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Side_Cart_And_Menu {

    public static function init() {
        add_action('wp_footer', [__CLASS__, 'render_side_cart_drawer']);
        add_filter('woocommerce_add_to_cart_fragments', [__CLASS__, 'add_cart_fragments']);
    }

    /**
     * Render Side-Cart Drawer HTML in wp_footer
     */
    public static function render_side_cart_drawer() {
        if (!function_exists('WC') || !WC()->cart) {
            return;
        }

        $cart_count = WC()->cart->get_cart_contents_count();
        $subtotal   = WC()->cart->get_subtotal();
        ?>
        <!-- CUNG STORE SIDE-CART DRAWER -->
        <div id="cung-cart-backdrop" class="cung-cart-backdrop" aria-hidden="true"></div>

        <div id="cung-side-cart-drawer" class="cung-side-cart-drawer" role="dialog" aria-modal="true" aria-label="Giỏ hàng của bạn">
            <div class="cung-side-cart-header">
                <div class="cung-side-cart-title">
                    <svg class="cung-cart-title-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>Giỏ Hàng Của Bạn</span>
                    <span class="cung-side-cart-count-badge"><?php echo esc_html($cart_count); ?></span>
                </div>
                <button type="button" id="cung-cart-close" class="cung-cart-close-btn" aria-label="Đóng giỏ hàng">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Free shipping banner -->
            <div class="cung-cart-free-shipping-notice">
                <div class="cung-shipping-text">
                    <span class="cung-shipping-icon">🚚</span>
                    <span class="cung-shipping-msg">Miễn phí đổi trả <strong>30 ngày</strong> & giao hàng nhanh toàn quốc</span>
                </div>
            </div>

            <div class="cung-side-cart-body widget_shopping_cart_content">
                <?php woocommerce_mini_cart(); ?>
            </div>
        </div>
        <!-- / CUNG STORE SIDE-CART DRAWER -->
        <?php
    }

    /**
     * Ensure count badge in header & drawer updates seamlessly via AJAX
     */
    public static function add_cart_fragments($fragments) {
        $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        
        // Update custom side-cart count badge
        $fragments['.cung-side-cart-count-badge'] = '<span class="cung-side-cart-count-badge">' . $count . '</span>';
        
        // Ensure Blocksy dynamic count badge updates
        $fragments['.ct-dynamic-count-cart'] = '<span class="ct-dynamic-count-cart" data-count="' . $count . '">' . $count . '</span>';

        return $fragments;
    }
}

Cung_Side_Cart_And_Menu::init();
