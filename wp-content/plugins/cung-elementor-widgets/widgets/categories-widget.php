<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

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
        // CONTENT SECTION
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Tiêu Đề & Cấu Hình', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'source_type',
            [
                'label' => esc_html__('Nguồn dữ liệu danh mục', 'cung-widgets'),
                'type' => Controls_Manager::SELECT,
                'default' => 'woocommerce',
                'options' => [
                    'woocommerce' => esc_html__('Tự động lấy từ WooCommerce (Chức năng thật)', 'cung-widgets'),
                    'manual'      => esc_html__('Tùy chỉnh thủ công', 'cung-widgets'),
                ],
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
                'default' => esc_html__('Khám phá các bộ sưu tập quần áo được mẹ và bé yêu thích nhất', 'cung-widgets'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'view_all_text',
            [
                'label' => esc_html__('Chữ nút "Xem tất cả"', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Xem tất cả danh mục', 'cung-widgets'),
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

        // MANUAL CATEGORIES LIST SECTION (WHEN MANUAL IS SELECTED)
        $this->start_controls_section(
            'section_categories',
            [
                'label' => esc_html__('Danh Sách Thủ Công', 'cung-widgets'),
                'condition' => [
                    'source_type' => 'manual',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'cat_name',
            [
                'label' => esc_html__('Tên danh mục', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Đầm Bé Gái', 'cung-widgets'),
            ]
        );

        $repeater->add_control(
            'cat_count',
            [
                'label' => esc_html__('Ghi chú số mẫu', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('24 mẫu', 'cung-widgets'),
            ]
        );

        $repeater->add_control(
            'cat_image',
            [
                'label' => esc_html__('Hình ảnh danh mục', 'cung-widgets'),
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => home_url('/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png'),
                ],
            ]
        );

        $repeater->add_control(
            'cat_link',
            [
                'label' => esc_html__('Đường dẫn', 'cung-widgets'),
                'type' => Controls_Manager::URL,
                'default' => [
                    'url' => home_url('/san-pham/'),
                ],
            ]
        );

        $this->add_control(
            'categories',
            [
                'label' => esc_html__('Các ô danh mục', 'cung-widgets'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'cat_name' => 'Đầm Bé Gái',
                        'cat_count' => '24 mẫu',
                        'cat_image' => ['url' => home_url('/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png')],
                        'cat_link' => ['url' => home_url('/san-pham/')],
                    ],
                    [
                        'cat_name' => 'Bộ Bé Trai',
                        'cat_count' => '18 mẫu',
                        'cat_image' => ['url' => home_url('/wp-content/uploads/2026/09/08d91db3-9019-4aae-aadb-33c40f102c0c.png')],
                        'cat_link' => ['url' => home_url('/san-pham/')],
                    ],
                ],
                'title_field' => '{{{ cat_name }}}',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $source = $settings['source_type'] ?? 'woocommerce';
        $categories_data = [];

        if ($source === 'woocommerce' && taxonomy_exists('product_cat')) {
            $terms = get_terms([
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'exclude'    => [18, 27], // Bỏ Chưa phân loại & Uncategorized
            ]);

            if (!empty($terms) && !is_wp_error($terms)) {
                $fallback_imgs = [
                    home_url('/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png'),
                    home_url('/wp-content/uploads/2026/09/08d91db3-9019-4aae-aadb-33c40f102c0c.png'),
                ];
                $i = 0;
                foreach ($terms as $term) {
                    $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    $img_url = $thumb_id ? wp_get_attachment_url($thumb_id) : $fallback_imgs[$i % count($fallback_imgs)];
                    
                    $categories_data[] = [
                        'name'  => $term->name,
                        'count' => $term->count . ' mẫu',
                        'link'  => get_term_link($term),
                        'image' => $img_url,
                    ];
                    $i++;
                }
            }
        }

        // Fallback to manual if WooCommerce empty or manual chosen
        if (empty($categories_data) && !empty($settings['categories'])) {
            foreach ($settings['categories'] as $cat) {
                $categories_data[] = [
                    'name'  => $cat['cat_name'],
                    'count' => $cat['cat_count'],
                    'link'  => !empty($cat['cat_link']['url']) ? $cat['cat_link']['url'] : '#',
                    'image' => !empty($cat['cat_image']['url']) ? $cat['cat_image']['url'] : '',
                ];
            }
        }
        ?>
        <div class="cung-widget-wrapper cung-categories-section">
            <div class="cung-section-header">
                <div>
                    <?php if (!empty($settings['title'])) : ?>
                        <h2 class="cung-section-title"><?php echo esc_html($settings['title']); ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($settings['subtitle'])) : ?>
                        <p class="cung-section-subtitle"><?php echo esc_html($settings['subtitle']); ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($settings['view_all_text'])) : 
                    $view_url = !empty($settings['view_all_link']['url']) ? $settings['view_all_link']['url'] : home_url('/san-pham/');
                ?>
                    <a href="<?php echo esc_url($view_url); ?>" class="cung-view-all-link">
                        <?php echo esc_html($settings['view_all_text']); ?>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                <?php endif; ?>
            </div>

            <div class="cung-categories-grid">
                <?php foreach ($categories_data as $cat) : ?>
                    <a href="<?php echo esc_url($cat['link']); ?>" class="cung-cat-card">
                        <div class="cung-cat-thumb">
                            <?php if (!empty($cat['image'])) : ?>
                                <img src="<?php echo esc_url($cat['image']); ?>" alt="<?php echo esc_attr($cat['name']); ?>" loading="lazy">
                            <?php else : ?>
                                <span style="font-size:24px;">👗</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="cung-cat-name"><?php echo esc_html($cat['name']); ?></h3>
                        <?php if (!empty($cat['count'])) : ?>
                            <span class="cung-cat-count"><?php echo esc_html($cat['count']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
