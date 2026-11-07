<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Cung_Product_Tabs_Widget extends Widget_Base {

    public function get_name() {
        return 'cung_product_tabs';
    }

    public function get_title() {
        return esc_html__('Cưng - Lưới Sản Phẩm Đa Năng (Tabs)', 'cung-widgets');
    }

    public function get_icon() {
        return 'eicon-tabs';
    }

    public function get_categories() {
        return ['cung-store'];
    }

    public function get_keywords() {
        return ['products', 'tabs', 'tabbed', 'cung', 'sản phẩm', 'lưới sản phẩm'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Cấu Hình Lưới Sản Phẩm', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__('Tiêu đề chính', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Sản Phẩm Đáng Yêu Cho Bé', 'cung-widgets'),
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
            'products_per_tab',
            [
                'label' => esc_html__('Số sản phẩm mỗi Tab', 'cung-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 4,
                'max' => 16,
            ]
        );

        $this->add_control(
            'bottom_btn_text',
            [
                'label' => esc_html__('Chữ nút dưới cùng', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Xem tất cả sản phẩm', 'cung-widgets'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'bottom_btn_link',
            [
                'label' => esc_html__('Đường dẫn nút dưới cùng', 'cung-widgets'),
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
        $limit = !empty($settings['products_per_tab']) ? intval($settings['products_per_tab']) : 8;
        $bottom_url = !empty($settings['bottom_btn_link']['url']) ? $settings['bottom_btn_link']['url'] : home_url('/san-pham/');
        $widget_id = $this->get_id();

        $tabs = [
            [
                'id'    => 'tab-all-' . $widget_id,
                'label' => 'Tất Cả',
                'args'  => [
                    'limit'   => $limit,
                    'status'  => 'publish',
                    'orderby' => 'date',
                    'order'   => 'DESC',
                ],
            ],
            [
                'id'    => 'tab-dam-' . $widget_id,
                'label' => 'Váy Đầm Bé Gái',
                'args'  => [
                    'limit'    => $limit,
                    'status'   => 'publish',
                    'category' => ['dam-be-gai'],
                ],
            ],
            [
                'id'    => 'tab-trai-' . $widget_id,
                'label' => 'Set Bộ Bé Trai',
                'args'  => [
                    'limit'    => $limit,
                    'status'   => 'publish',
                    'category' => ['bo-be-trai', 'do-be-trai'],
                ],
            ],
            [
                'id'    => 'tab-linen-' . $widget_id,
                'label' => 'Thời Trang Linen',
                'args'  => [
                    'limit'    => $limit,
                    'status'   => 'publish',
                    'category' => ['thoi-trang-linen'],
                ],
            ],
            [
                'id'    => 'tab-home-' . $widget_id,
                'label' => 'Đồ Mặc Nhà',
                'args'  => [
                    'limit'    => $limit,
                    'status'   => 'publish',
                    'category' => ['do-mac-nha', 'do-so-sinh'],
                ],
            ],
        ];
        ?>
        <div class="cung-widget-wrapper cung-product-tabs-wrapper">
            <div class="cung-tabs-header">
                <div class="cung-tabs-heading-group">
                    <?php if (!empty($settings['title'])) : ?>
                        <h2 class="cung-section-title"><?php echo esc_html($settings['title']); ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($settings['subtitle'])) : ?>
                        <p class="cung-section-subtitle"><?php echo esc_html($settings['subtitle']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="cung-tabs-nav" role="tablist">
                    <?php foreach ($tabs as $index => $tab) : ?>
                        <button type="button" 
                                class="cung-tab-btn <?php echo ($index === 0) ? 'is-active' : ''; ?>" 
                                data-target="<?php echo esc_attr($tab['id']); ?>" 
                                role="tab" 
                                aria-selected="<?php echo ($index === 0) ? 'true' : 'false'; ?>">
                            <?php echo esc_html($tab['label']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="cung-tabs-content">
                <?php foreach ($tabs as $index => $tab) : 
                    $products = function_exists('wc_get_products') ? wc_get_products($tab['args']) : [];
                ?>
                    <div id="<?php echo esc_attr($tab['id']); ?>" class="cung-tab-panel <?php echo ($index === 0) ? 'is-active' : ''; ?>" role="tabpanel">
                        <?php if (!empty($products)) : ?>
                            <?php woocommerce_product_loop_start(); ?>
                            <?php 
                            foreach ($products as $p) {
                                $post_object = get_post($p->get_id());
                                setup_postdata($GLOBALS['post'] =& $post_object);
                                wc_get_template_part('content', 'product');
                            }
                            wp_reset_postdata();
                            ?>
                            <?php woocommerce_product_loop_end(); ?>
                        <?php else : ?>
                            <p class="cung-no-products">Hiện chưa có sản phẩm nào trong danh mục này.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($settings['bottom_btn_text'])) : ?>
                <div class="cung-tabs-bottom-action">
                    <a href="<?php echo esc_url($bottom_url); ?>" class="cung-bottom-btn">
                        <?php echo esc_html($settings['bottom_btn_text']); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
