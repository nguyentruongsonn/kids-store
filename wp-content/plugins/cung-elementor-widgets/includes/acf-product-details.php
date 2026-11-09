<?php
/**
 * Cưng Store - Hiển thị các trường ACF bổ sung cho Sản phẩm (Hỗ trợ Đa ngôn ngữ Polylang)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_ACF_Product_Details {

    public static function init() {
        // Móc vào vị trí dưới giá / nút chọn size
        add_action('woocommerce_single_product_summary', [__CLASS__, 'render_product_acf_fields'], 26);
    }

    public static function render_product_acf_fields() {
        if (!function_exists('get_field')) {
            return;
        }

        $product_id = get_the_ID();
        $chat_lieu  = get_field('chat_lieu_vai', $product_id);
        $do_tuoi    = get_field('do_tuoi', $product_id);

        if (empty($chat_lieu) && empty($do_tuoi)) {
            return;
        }

        // Tự động nhận diện ngôn ngữ của bài viết hoặc toàn trang qua Polylang
        $lang = 'vi';
        if (function_exists('pll_get_post_language')) {
            $lang = pll_get_post_language($product_id) ?: 'vi';
        } elseif (function_exists('pll_current_language')) {
            $lang = pll_current_language() ?: 'vi';
        }

        $is_en = ($lang === 'en');
        $label_material = $is_en ? 'Material:' : 'Chất liệu:';
        $label_age      = $is_en ? 'Age Range:' : 'Độ tuổi phù hợp:';
        ?>
        <div class="cung-product-specs-clean" style="margin: 16px 0; padding: 12px 0; border-top: 1px solid #eef2f6; border-bottom: 1px solid #eef2f6; font-size: 13.5px; line-height: 1.5;">
            <div style="display: flex; flex-wrap: wrap; gap: 12px 28px; align-items: center;">
                <?php if (!empty($chat_lieu)): ?>
                    <div style="display: flex; align-items: baseline; gap: 6px;">
                        <span style="color: #64748b; font-weight: 400;"><?php echo esc_html($label_material); ?></span>
                        <span style="color: #0f172a; font-weight: 600;"><?php echo esc_html($chat_lieu); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($do_tuoi)): ?>
                    <div style="display: flex; align-items: baseline; gap: 6px;">
                        <span style="color: #64748b; font-weight: 400;"><?php echo esc_html($label_age); ?></span>
                        <span style="color: #0f172a; font-weight: 600;"><?php echo esc_html($do_tuoi); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

Cung_ACF_Product_Details::init();
