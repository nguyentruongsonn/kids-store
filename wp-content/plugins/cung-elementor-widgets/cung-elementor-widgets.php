<?php
/**
 * Plugin Name: Cưng Store - Custom Elementor Widgets
 * Plugin URI:  https://cungstore.vn
 * Description: Bộ 3 Widget tuỳ biến chuyên biệt cho Cưng Store: Danh mục nổi bật, Flash Sale có đồng hồ đếm ngược, và Lưới sản phẩm đa năng có bộ lọc Tab.
 * Version:     1.0.0
 * Author:      Senior Frontend Developer
 * Text Domain: cung-widgets
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Cung_Elementor_Widgets_Extension {

    const VERSION = '1.0.0';
    const MINIMUM_ELEMENTOR_VERSION = '3.5.0';
    const MINIMUM_PHP_VERSION = '7.4';

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        require_once __DIR__ . '/admin/hero-slideshow-admin.php';
        new \Cung_Hero_Slideshow_Admin();

        require_once __DIR__ . '/admin/contact-settings-admin.php';
        new \Cung_Contact_Settings_Admin();

        require_once __DIR__ . '/includes/telegram-notifier.php';
        new \Cung_Telegram_Notifier();

        require_once __DIR__ . '/admin/telegram-admin.php';
        new \Cung_Telegram_Admin();

        require_once __DIR__ . '/side-cart-and-menu.php';
        require_once __DIR__ . '/size-guide.php';
        require_once __DIR__ . '/floating-contact.php';
        require_once __DIR__ . '/includes/policy-sidebar.php';
        require_once __DIR__ . '/includes/news-sidebar.php';
        require_once __DIR__ . '/includes/acf-product-details.php';
        require_once __DIR__ . '/includes/multilingual-footer.php';

        add_action('init', [$this, 'i18n']);
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function i18n() {
        load_plugin_textdomain('cung-widgets');
    }

    public function init() {
        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Add Category
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_category']);

        // Register Widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);

        // Register Scripts & Styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function admin_notice_missing_main_plugin() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" yêu cầu plugin "%2$s" phải được kích hoạt để hoạt động.', 'cung-widgets'),
            '<strong>' . esc_html__('Cưng Store Elementor Widgets', 'cung-widgets') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'cung-widgets') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function add_elementor_category($elements_manager) {
        $elements_manager->add_category(
            'cung-store',
            [
                'title' => esc_html__('Cưng Store Elements', 'cung-widgets'),
                'icon'  => 'fa fa-child',
            ]
        );
    }

    public function enqueue_scripts() {
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/cung-widgets.css';
        $js_file  = plugin_dir_path(__FILE__) . 'assets/js/cung-widgets.js';
        $css_ver  = file_exists($css_file) ? filemtime($css_file) : self::VERSION;
        $js_ver   = file_exists($js_file) ? filemtime($js_file) : self::VERSION;

        wp_enqueue_style(
            'cung-widgets-style',
            plugins_url('assets/css/cung-widgets.css', __FILE__),
            [],
            $css_ver
        );

        wp_enqueue_script(
            'cung-widgets-script',
            plugins_url('assets/js/cung-widgets.js', __FILE__),
            ['jquery'],
            $js_ver,
            true
        );

        $slides_data = get_option(\Cung_Hero_Slideshow_Admin::OPTION_SLIDES, []);
        $settings_data = get_option(\Cung_Hero_Slideshow_Admin::OPTION_SETTINGS, [
            'interval' => 5000,
            'fit'      => 'cover'
        ]);

        wp_localize_script('cung-widgets-script', 'cungHeroConfig', [
            'slides'   => !empty($slides_data) ? array_values($slides_data) : [
                [
                    'src'   => 'http://localhost/cung/wp-content/uploads/2026/09/08d91db3-9019-4aae-aadb-33c40f102c0c.png',
                    'title' => 'Đầm Hoa Thêu Cổ Sen',
                    'link'  => 'http://localhost/cung/san-pham/'
                ],
                [
                    'src'   => 'http://localhost/cung/wp-content/uploads/2026/09/410483d4-1bf3-46ec-b12a-ccf899600ba9-1.png',
                    'title' => 'Áo Dệt Kim Kẻ Sọc',
                    'link'  => 'http://localhost/cung/san-pham/'
                ],
                [
                    'src'   => 'http://localhost/cung/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png',
                    'title' => 'Đầm Đũi Mộc Tự Nhiên',
                    'link'  => 'http://localhost/cung/san-pham/'
                ]
            ],
            'interval' => intval($settings_data['interval'] ?? 5000),
            'fit'      => sanitize_text_field($settings_data['fit'] ?? 'cover'),
        ]);
    }

    public function register_widgets($widgets_manager) {
        require_once __DIR__ . '/widgets/categories-widget.php';
        require_once __DIR__ . '/widgets/flash-sale-widget.php';
        require_once __DIR__ . '/widgets/product-tabs-widget.php';

        $widgets_manager->register(new \Cung_Categories_Widget());
        $widgets_manager->register(new \Cung_Flash_Sale_Widget());
        $widgets_manager->register(new \Cung_Product_Tabs_Widget());
    }
}

Cung_Elementor_Widgets_Extension::instance();
