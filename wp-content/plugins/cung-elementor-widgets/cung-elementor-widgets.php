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
        wp_enqueue_style(
            'cung-widgets-style',
            plugins_url('assets/css/cung-widgets.css', __FILE__),
            [],
            self::VERSION
        );

        wp_enqueue_script(
            'cung-widgets-script',
            plugins_url('assets/js/cung-widgets.js', __FILE__),
            ['jquery'],
            self::VERSION,
            true
        );
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
