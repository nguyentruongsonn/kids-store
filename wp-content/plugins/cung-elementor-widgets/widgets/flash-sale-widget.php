<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Cung_Flash_Sale_Widget extends Widget_Base {

    public function get_name() {
        return 'cung_flash_sale';
    }

    public function get_title() {
        return esc_html__('Cưng - Flash Sale Giờ Vàng', 'cung-widgets');
    }

    public function get_icon() {
        return 'eicon-countdown';
    }

    public function get_categories() {
        return ['cung-store'];
    }

    public function get_keywords() {
        return ['flash sale', 'sale', 'giá sốc', 'cung', 'countdown', 'đếm ngược'];
    }

    protected function register_controls() {
        // CONTENT SECTION
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Cấu Hình Flash Sale', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'badge_text',
            [
                'label' => esc_html__('Huy hiệu', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('FLASH SALE', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => esc_html__('Tiêu đề', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('GIỜ VÀNG GIÁ SỐC', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'end_time',
            [
                'label' => esc_html__('Thời gian kết thúc đếm ngược', 'cung-widgets'),
                'type' => Controls_Manager::DATE_TIME,
                'default' => date('Y-m-d H:i', strtotime('+12 hours')),
                'description' => esc_html__('Đồng hồ sẽ đếm ngược đến mốc thời gian này.', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'products_limit',
            [
                'label' => esc_html__('Số sản phẩm hiển thị', 'cung-widgets'),
                'type' => Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 2,
                'max' => 12,
            ]
        );

        $this->add_control(
            'view_all_text',
            [
                'label' => esc_html__('Chữ nút xem tất cả', 'cung-widgets'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Xem tất cả Flash Sale →', 'cung-widgets'),
            ]
        );

        $this->add_control(
            'view_all_link',
            [
                'label' => esc_html__('Đường dẫn xem tất cả', 'cung-widgets'),
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
        $limit = !empty($settings['products_limit']) ? intval($settings['products_limit']) : 4;
        $end_time = !empty($settings['end_time']) ? $settings['end_time'] : date('Y-m-d H:i', strtotime('+12 hours'));

        // Real WooCommerce Product Query
        $products = [];
        if (function_exists('wc_get_products')) {
            $products = wc_get_products([
                'limit'   => $limit,
                'status'  => 'publish',
                'orderby' => 'date',
                'order'   => 'DESC',
            ]);
        }
        ?>
        <div class="cung-widget-wrapper cung-flashsale-box">
            <div class="cung-flashsale-header">
                <div class="cung-flashsale-title-group">
                    <?php if (!empty($settings['badge_text'])) : ?>
                        <span class="cung-flashsale-badge"><?php echo esc_html($settings['badge_text']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($settings['title'])) : ?>
                        <h3 class="cung-flashsale-title"><?php echo esc_html($settings['title']); ?></h3>
                    <?php endif; ?>

                    <div class="cung-countdown-wrap" data-end-time="<?php echo esc_attr($end_time); ?>">
                        <span class="cung-countdown-unit cung-hours">00</span> :
                        <span class="cung-countdown-unit cung-minutes">00</span> :
                        <span class="cung-countdown-unit cung-seconds">00</span>
                    </div>
                </div>

                <?php if (!empty($settings['view_all_text'])) : 
                    $view_url = !empty($settings['view_all_link']['url']) ? $settings['view_all_link']['url'] : home_url('/san-pham/');
                ?>
                    <a href="<?php echo esc_url($view_url); ?>" class="cung-view-all-link">
                        <?php echo esc_html($settings['view_all_text']); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="cung-flashsale-grid">
                <?php 
                if (!empty($products)) :
                    foreach ($products as $product) :
                        $p_id = $product->get_id();
                        $regular_price = floatval($product->get_regular_price());
                        $sale_price = floatval($product->get_sale_price());
                        $discount_text = '';
                        if ($regular_price > 0 && $sale_price > 0 && $regular_price > $sale_price) {
                            $discount_text = '-' . round((($regular_price - $sale_price) / $regular_price) * 100) . '%';
                        } elseif ($regular_price > 0) {
                            $discount_text = 'HOT';
                        }

                        $stock_qty = $product->get_stock_quantity();
                        $sold_count = intval(get_post_meta($p_id, 'total_sales', true));
                        if ($sold_count <= 0) $sold_count = ($p_id % 20) + 5;
                        $total_stock = $stock_qty ? ($sold_count + $stock_qty) : ($sold_count + 10);
                        $percent = min(100, round(($sold_count / $total_stock) * 100));
                        $image_html = $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']);
                        if (empty($image_html)) {
                            $image_html = '<img src="' . esc_url(home_url('/wp-content/uploads/2026/09/dam-be-gai-soc-1081x980-1.png')) . '" alt="' . esc_attr($product->get_name()) . '">';
                        }
                ?>
                        <div class="cung-flash-card">
                            <?php if (!empty($discount_text)) : ?>
                                <span class="cung-discount-tag"><?php echo esc_html($discount_text); ?></span>
                            <?php endif; ?>

                            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="cung-product-thumb">
                                <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </a>

                            <div>
                                <h4 class="cung-product-title">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>" style="color:inherit; text-decoration:none;">
                                        <?php echo esc_html($product->get_name()); ?>
                                    </a>
                                </h4>

                                <div class="cung-price-wrap">
                                    <span class="cung-price-current"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                </div>

                                <div class="cung-progress-wrap">
                                    <div class="cung-progress-info">
                                        <span>Đã bán <?php echo esc_html($sold_count); ?></span>
                                        <span class="cung-stock-urgent"><?php echo ($stock_qty && $stock_qty < 10) ? 'Chỉ còn ' . esc_html($stock_qty) : 'Đang bán chạy'; ?></span>
                                    </div>
                                    <div class="cung-progress-bar">
                                        <div class="cung-progress-fill" style="width: <?php echo esc_attr($percent); ?>%;"></div>
                                    </div>
                                </div>

                                <div style="margin-top: 10px;">
                                    <?php 
                                    echo sprintf(
                                        '<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
                                        esc_url($product->add_to_cart_url()),
                                        esc_attr('cung-btn-choose button add_to_cart_button ajax_add_to_cart w-full text-center'),
                                        'data-product_id="' . esc_attr($p_id) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" aria-label="Thêm ' . esc_attr($product->get_name()) . ' vào giỏ"',
                                        esc_html($product->add_to_cart_text())
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
        </div>
        <?php
    }
}
