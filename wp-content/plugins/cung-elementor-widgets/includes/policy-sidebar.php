<?php
/**
 * Cưng Store - Policy Sidebar Module
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Policy_Sidebar {

    public static $policy_slugs = [
        'chinh-sach-bao-mat'  => 'Chính sách bảo mật',
        'chinh-sach-van-hanh' => 'Chính sách vận hành',
        'chinh-sach-doi-tra'  => 'Chính sách đổi trả',
        'chinh-sach-mua-hang' => 'Chính sách mua hàng',
        'chinh-sach-bao-hanh' => 'Chính sách bảo hành',
        'huong-dan-mua-hang'  => 'Hướng dẫn mua hàng',
    ];

    public static function init() {
        add_action('widgets_init', [__CLASS__, 'register_widget']);
        add_filter('blocksy:general:sidebar-position', [__CLASS__, 'filter_sidebar_position']);
        add_filter('blocksy:global:page_structure', [__CLASS__, 'filter_page_structure']);
        add_filter('sidebars_widgets', [__CLASS__, 'filter_sidebars_widgets']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_styles']);
    }

    public static function register_widget() {
        wp_register_sidebar_widget(
            'cung_policy_nav_block',
            'Cưng Store - Danh Mục Chính Sách',
            [__CLASS__, 'render_widget_callback']
        );
    }

    public static function render_widget_callback() {
        echo self::render_policy_nav_html();
    }

    public static function is_policy_page() {
        if (!is_page()) {
            return false;
        }
        $post = get_queried_object();
        if (!$post || !isset($post->post_name)) {
            return false;
        }
        return array_key_exists($post->post_name, self::$policy_slugs);
    }

    public static function filter_sidebar_position($position) {
        if (self::is_policy_page()) {
            return 'left';
        }
        return $position;
    }

    public static function filter_page_structure($structure) {
        if (self::is_policy_page()) {
            return 'type-2'; // type-2 = left sidebar in Blocksy
        }
        return $structure;
    }

    public static function filter_sidebars_widgets($widgets) {
        if (is_admin()) {
            return $widgets;
        }

        if (self::is_policy_page()) {
            $widgets['sidebar-1'] = ['cung_policy_nav_block'];
        }

        return $widgets;
    }

    public static function enqueue_styles() {
        if (self::is_policy_page()) {
            wp_add_inline_style('ct-main-styles', self::get_custom_css());
        }
    }

    public static function render_policy_nav_html() {
        $current_post = get_queried_object();
        $current_slug = $current_post ? $current_post->post_name : '';

        ob_start();
        ?>
        <div class="cung-policy-nav-card">
            <div class="cung-policy-header">
                <h3 class="cung-policy-header-title">Danh mục</h3>
            </div>

            <ul class="cung-policy-links">
                <?php foreach (self::$policy_slugs as $slug => $title): 
                    $is_active = ($current_slug === $slug);
                    $url = home_url('/' . $slug . '/');
                ?>
                    <li class="cung-policy-link-item <?php echo $is_active ? 'is-active' : ''; ?>">
                        <a href="<?php echo esc_url($url); ?>">
                            <span class="cung-policy-name"><?php echo esc_html($title); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function get_custom_css() {
        return "
        .cung-policy-nav-card {
            background: #FFFFFF;
            border-radius: 8px;
            padding: 20px 18px;
            border: 1px solid #ECE6E4;
            margin-bottom: 24px;
        }
        .cung-policy-header {
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #F0EAE8;
        }
        .cung-policy-header-title {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #3C2D31;
            line-height: 1.3;
            letter-spacing: -0.01em;
        }
        .cung-policy-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .cung-policy-link-item a {
            display: block;
            padding: 10px 14px;
            border-radius: 6px;
            color: #4A3E41;
            font-size: 14.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            background: transparent;
        }
        .cung-policy-link-item a:hover {
            background: #F9F5F5;
            color: #A94F65;
            padding-left: 17px;
        }
        .cung-policy-link-item.is-active a {
            background: #A94F65;
            color: #FFFDF9 !important;
            font-weight: 600;
        }
        .cung-policy-name {
            display: inline-block;
        }
        ";
    }
}
Cung_Policy_Sidebar::init();
