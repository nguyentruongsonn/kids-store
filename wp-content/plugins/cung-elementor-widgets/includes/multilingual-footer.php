<?php
/**
 * Cưng Store - Tự động dịch chân trang (Footer Widgets & Blocks) & Tối ưu Đa ngôn ngữ
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Multilingual_Footer {

    public static function init() {
        add_filter('render_block', [__CLASS__, 'translate_footer_block'], 20, 2);
        add_filter('theme_mod_breadcrumb_home_text', [__CLASS__, 'translate_breadcrumb_home']);
    }

    public static function is_english() {
        if (function_exists('pll_current_language')) {
            return pll_current_language() === 'en';
        }
        if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/en/')) {
            return true;
        }
        return false;
    }

    public static function translate_breadcrumb_home($val) {
        if (self::is_english()) {
            return 'Home';
        }
        return $val;
    }

    public static function translate_footer_block($block_content, $block) {
        if (!self::is_english()) {
            return $block_content;
        }

        $dictionary = [
            // Headings
            'VỀ CHÚNG TÔI' => 'ABOUT US',
            'CHÍNH SÁCH'   => 'OUR POLICIES',

            // Brand & Address
            'SHOP CƯNG' => 'CUNG KIDS',
            'Thời trang dành cho trẻ em' => "Children's Safe & Premium Fashion",
            '18/10/8 đường 53, phường An Hội Tây, TP. Hồ Chí Minh' => '18/10/8 Street 53, An Hoi Tay, Ho Chi Minh City',

            // About Links
            'Giới thiệu Cưng Store' => 'About Cung Kids',
            'http://localhost/cung/gioi-thieu/' => 'http://localhost/cung/en/about-us/',

            'Bộ sưu tập sản phẩm' => 'Product Collection',
            'http://localhost/cung/san-pham/' => 'http://localhost/cung/en/shop/',

            'Tin tức &amp; Mẹo cho bé' => 'News & Parenting Tips',
            'Tin tức & Mẹo cho bé'     => 'News & Parenting Tips',
            'http://localhost/cung/tin-tuc/' => 'http://localhost/cung/en/news-blog/',

            'Liên hệ &amp; Bản đồ' => 'Contact & Location',
            'Liên hệ & Bản đồ'     => 'Contact & Location',
            'http://localhost/cung/lien-he/' => 'http://localhost/cung/en/contact/',

            // Policy Links
            'Chính sách bảo mật' => 'Privacy Policy',
            'http://localhost/cung/chinh-sach-bao-mat/' => 'http://localhost/cung/en/privacy-policy-cung-store/',

            'Chính sách vận hành' => 'Shipping & Delivery',
            'http://localhost/cung/chinh-sach-van-hanh/' => 'http://localhost/cung/en/shipping-delivery-policy-cung-store/',

            'Chính sách đổi trả' => 'Return & Exchange',
            'http://localhost/cung/chinh-sach-doi-tra/' => 'http://localhost/cung/en/return-exchange-policy/',

            'Chính sách mua hàng' => 'Purchase Terms',
            'http://localhost/cung/chinh-sach-mua-hang/' => 'http://localhost/cung/en/terms-purchase-conditions-cung-store/',

            'Chính sách bảo hành' => 'Warranty & Guarantee',
            'http://localhost/cung/chinh-sach-bao-hanh/' => 'http://localhost/cung/en/warranty-quality-guarantee-cung-store/',

            'Hướng dẫn mua hàng' => 'Shopping & Size Guide',
            'http://localhost/cung/huong-dan-mua-hang/' => 'http://localhost/cung/en/shopping-baby-size-guide-cung-store/',
        ];

        return strtr($block_content, $dictionary);
    }
}

Cung_Multilingual_Footer::init();
