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
        // CONTENT SECTION
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Tiêu Đề & Cấu Hình', 'cung-widgets'),
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
            'products_per_tab',
            [
                'label' => esc_html__('Số sản phẩm mỗi Tab', 'cung-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 2,
                'max' => 24,
            ]
        );

        $this->add_control(
            'bottom_btn_text',
            [
                'label' => esc_html__('Chữ nút dưới cùng', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Xem tất cả sản phẩm thời trang bé →', 'cung-widgets'),
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
        $widget_id = $this->get_id();

        // 4 Real WooCommerce Tabs
        $tabs_config = [
            [
                'id'    => 'all',
                'title' => 'TẤT CẢ SẢN PHẨM',
                'args'  => ['limit' => $limit, 'status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC'],
            ],
            [
                'id'    => 'new',
                'title' => 'HÀNG MỚI VỀ',
                'args'  => ['limit' => $limit, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC'],
            ],
            [
                'id'    => 'dam',
                'title' => 'ĐẦM BÉ GÁI',
                'args'  => ['limit' => $limit, 'status' => 'publish', 'category' => ['dam-be-gai', 'dam']],
            ],
            [
                'id'    => 'trai',
                'title' => 'BỘ BÉ TRAI',
                'args'  => ['limit' => $limit, 'status' => 'publish', 'category' => ['bo-be-trai']],
            ],
        ];
        ?>
        <div class="cung-widget-wrapper cung-product-tabs-section">
            <div class="cung-tabs-header-bar">
                <?php if (!empty($settings['title'])) : ?>
                    <h2 class="cung-section-title" style="margin-bottom:0;"><?php echo esc_html($settings['title']); ?></h2>
                <?php endif; ?>

                <div class="cung-tabs-nav">
                    <?php foreach ($tabs_config as $index => $tab) : 
                        $is_active = ($index === 0) ? 'is-active' : '';
                        $tab_target_id = 'cung-tab-' . $widget_id . '-' . $tab['id'];
                    ?>
                        <button type="button" class="cung-tab-btn <?php echo esc_attr($is_active); ?>" data-target="<?php echo esc_attr($tab_target_id); ?>">
                            <?php echo esc_html($tab['title']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab Panels with Real WooCommerce Queries -->
            <?php 
            foreach ($tabs_config as $index => $tab) : 
                $is_active = ($index === 0) ? 'is-active' : '';
                $tab_target_id = 'cung-tab-' . $widget_id . '-' . $tab['id'];
                $products = function_exists('wc_get_products') ? wc_get_products($tab['args']) : [];

                // If specific category query is empty, fallback to recent products so tab is never blank
                if (empty($products) && function_exists('wc_get_products')) {
                    $products = wc_get_products(['limit' => $limit, 'status' => 'publish']);
                }
            ?>
                <div class="cung-tab-panel <?php echo esc_attr($is_active); ?>" id="<?php echo esc_attr($tab_target_id); ?>">
                    <div class="cung-tab-grid">
                        <?php 
                        if (!empty($products)) :
                            foreach ($products as $product) :
                                $p_id = $product->get_id();
                                $terms = wp_get_post_terms($p_id, 'product_cat');
                                $cat_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : 'THỜI TRANG BÉ';
                                $image_html = $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']);
                                if (empty($image_html)) {
                                    $image_html = '<img src="' . esc_url(home_url('/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png')) . '" alt="' . esc_attr($product->get_name()) . '">';
                                }
                        ?>
                                <div class="cung-tab-card">
                                    <div>
                                        <div class="cung-product-thumb cung-aspect-3-4">
                                            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                                <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            </a>
                                        </div>

                                        <span class="cung-tab-card-category"><?php echo esc_html($cat_name); ?></span>

                                        <h3 class="cung-product-title">
                                            <a href="<?php echo esc_url($product->get_permalink()); ?>" style="color:inherit; text-decoration:none;">
                                                <?php echo esc_html($product->get_name()); ?>
                                            </a>
                                        </h3>

                                        <p class="cung-product-size-hint">
                                            <?php echo ($product->get_manage_stock() && $product->get_stock_quantity()) ? 'Còn ' . $product->get_stock_quantity() . ' sản phẩm' : 'Chất liệu vải mềm dịu'; ?>
                                        </p>
                                    </div>

                                    <div class="cung-tab-card-footer">
                                        <span class="cung-price-current"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                        <?php 
                                        echo sprintf(
                                            '<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
                                            esc_url($product->add_to_cart_url()),
                                            esc_attr('cung-btn-choose button add_to_cart_button ajax_add_to_cart'),
                                            'data-product_id="' . esc_attr($p_id) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" aria-label="Thêm ' . esc_attr($product->get_name()) . ' vào giỏ"',
                                            esc_html($product->add_to_cart_text())
                                        );
                                        ?>
                                    </div>
                                </div>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($settings['bottom_btn_text'])) : 
                $bottom_url = !empty($settings['bottom_btn_link']['url']) ? $settings['bottom_btn_link']['url'] : home_url('/san-pham/');
            ?>
                <div style="text-align: center; margin-top: 32px;">
                    <a href="<?php echo esc_url($bottom_url); ?>" class="cung-view-all-link" style="border: 1.5px solid var(--cung-primary); padding: 10px 24px; border-radius: 5px; font-weight: 700;">
                        <?php echo esc_html($settings['bottom_btn_text']); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
