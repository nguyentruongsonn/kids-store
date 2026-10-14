<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Cung_Categories_Widget extends Widget_Base {

    public function get_name() {
        return 'cung_categories';
    }

    public function get_title() {
        return esc_html__('Cưng - Danh Mục Nổi Bật', 'cung-widgets');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['cung-store'];
    }

    public function get_keywords() {
        return ['category', 'categories', 'cung', 'danh mục', 'shop'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Cấu Hình Danh Mục', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__('Tiêu đề chính', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Danh Mục Nổi Bật', 'cung-widgets'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'subtitle',
            [
                'label' => esc_html__('Mô tả phụ', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'view_all_text',
            [
                'label' => esc_html__('Chữ nút "Xem tất cả"', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Tất cả danh mục', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'view_all_link',
            [
                'label' => esc_html__('Đường dẫn "Xem tất cả"', 'cung-widgets'),
                'type' => Controls_Manager::URL,
                'default' => [
                    'url' => home_url('/san-pham/'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'exclude'    => [18, 27], // Exclude uncategorized
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => 6,
        ]);

        if (empty($terms) || is_wp_error($terms)) {
            return;
        }

        $fallback_img = home_url('/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png');
        $view_url = !empty($settings['view_all_link']['url']) ? $settings['view_all_link']['url'] : home_url('/san-pham/');
        ?>
        <div class="cung-widget-wrapper cung-categories-wrapper">
            <div class="cung-section-header">
                <div class="cung-section-title-wrap">
                    <?php if (!empty($settings['title'])) : ?>
                        <h2 class="cung-section-title"><?php echo esc_html($settings['title']); ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($settings['subtitle'])) : ?>
                        <p class="cung-section-subtitle"><?php echo esc_html($settings['subtitle']); ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($settings['view_all_text'])) : ?>
                    <a href="<?php echo esc_url($view_url); ?>" class="cung-view-all-link">
                        <?php echo esc_html($settings['view_all_text']); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="cung-categories-grid">
                <?php foreach ($terms as $term) :
                    $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    $img_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : $fallback_img;
                    $term_link = get_term_link($term);
                    $count_label = sprintf(_n('%s sản phẩm', '%s sản phẩm', $term->count, 'cung-widgets'), number_format_i18n($term->count));
                ?>
                    <a href="<?php echo esc_url($term_link); ?>" class="cung-category-card">
                        <div class="cung-category-thumb">
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($term->name); ?>" loading="lazy" />
                        </div>
                        <h3 class="cung-category-name"><?php echo esc_html($term->name); ?></h3>
                        <span class="cung-category-count"><?php echo esc_html($count_label); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
