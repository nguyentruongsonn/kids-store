<?php
/**
 * Cưng Store - Kids Fashion Size Guide Modal & Button
 *
 * @package Cung_Widgets
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Size_Guide {

    public static function init() {
        // Render trigger button on single product page
        add_action('woocommerce_single_product_summary', [__CLASS__, 'render_trigger_button'], 25);

        // Render modal in footer
        add_action('wp_footer', [__CLASS__, 'render_modal']);
    }

    /**
     * Render trigger button above/near add to cart
     */
    public static function render_trigger_button() {
        ?>
        <div class="cung-size-guide-btn-wrap">
            <button type="button" class="cung-btn-size-guide" id="cung-btn-size-guide" aria-haspopup="dialog">
                <svg class="cung-ruler-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.3 15.3l-6.6 6.6c-.4.4-1 .4-1.4 0l-11-11c-.4-.4-.4-1 0-1.4l6.6-6.6c.4-.4 1-.4 1.4 0l11 11c.4.4.4 1 0 1.4z"></path>
                    <path d="m14.5 12.5 2-2"></path>
                    <path d="m11.5 9.5 2-2"></path>
                    <path d="m8.5 6.5 2-2"></path>
                    <path d="m17.5 15.5 2-2"></path>
                </svg>
                <span>Bảng hướng dẫn chọn size chuẩn cho bé</span>
            </button>
        </div>
        <?php
    }

    /**
     * Render Size Guide Modal in wp_footer
     */
    public static function render_modal() {
        // Only render if WooCommerce single product or modal is needed
        if (!is_product()) {
            return;
        }
        ?>
        <!-- CUNG STORE SIZE GUIDE MODAL -->
        <div id="cung-size-guide-backdrop" class="cung-modal-backdrop" aria-hidden="true"></div>

        <div id="cung-size-guide-modal" class="cung-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="cung-size-modal-title" aria-hidden="true">
            <div class="cung-modal-content">
                <div class="cung-modal-header">
                    <div class="cung-modal-title-wrap">
                        <span class="cung-modal-badge">Cưng Store • Size Chart</span>
                        <h3 id="cung-size-modal-title" class="cung-modal-title">Bảng Quy Đổi Kích Cỡ Thời Trang Trẻ Em</h3>
                    </div>
                    <button type="button" class="cung-modal-close" id="cung-size-guide-close" aria-label="Đóng bảng size">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Tabs Navigation -->
                <div class="cung-size-tabs-nav">
                    <button type="button" class="cung-size-tab-btn is-active" data-tab="tab-begai">
                        <span>Váy Đầm Bé Gái</span>
                    </button>
                    <button type="button" class="cung-size-tab-btn" data-tab="tab-betrai">
                        <span>Thời Trang Bé Trai</span>
                    </button>
                    <button type="button" class="cung-size-tab-btn" data-tab="tab-sosinh">
                        <span>Đồ Sơ Sinh (0 - 12M)</span>
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="cung-modal-body">
                    <!-- TAB 1: BÉ GÁI -->
                    <div id="tab-begai" class="cung-size-tab-panel is-active">
                        <div class="cung-table-responsive">
                            <table class="cung-size-table">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Độ tuổi phù hợp</th>
                                        <th>Cân nặng (kg)</th>
                                        <th>Chiều cao (cm)</th>
                                        <th>Dài váy (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 1 (1Y)</strong></td>
                                        <td>1 – 2 tuổi</td>
                                        <td><strong>9 – 11 kg</strong></td>
                                        <td>75 – 85 cm</td>
                                        <td>48 – 50 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 2 (2Y)</strong></td>
                                        <td>2 – 3 tuổi</td>
                                        <td><strong>11 – 13 kg</strong></td>
                                        <td>85 – 95 cm</td>
                                        <td>52 – 54 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 3 (3Y)</strong></td>
                                        <td>3 – 4 tuổi</td>
                                        <td><strong>13 – 15 kg</strong></td>
                                        <td>95 – 105 cm</td>
                                        <td>56 – 58 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 4 (4Y)</strong></td>
                                        <td>4 – 5 tuổi</td>
                                        <td><strong>15 – 18 kg</strong></td>
                                        <td>105 – 115 cm</td>
                                        <td>60 – 62 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 5 (5Y)</strong></td>
                                        <td>5 – 6 tuổi</td>
                                        <td><strong>18 – 22 kg</strong></td>
                                        <td>115 – 125 cm</td>
                                        <td>64 – 66 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 6 (6Y)</strong></td>
                                        <td>6 – 7 tuổi</td>
                                        <td><strong>22 – 26 kg</strong></td>
                                        <td>125 – 132 cm</td>
                                        <td>68 – 70 cm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: BÉ TRAI -->
                    <div id="tab-betrai" class="cung-size-tab-panel">
                        <div class="cung-table-responsive">
                            <table class="cung-size-table">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Độ tuổi phù hợp</th>
                                        <th>Cân nặng (kg)</th>
                                        <th>Chiều cao (cm)</th>
                                        <th>Dài áo (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 1 (1Y)</strong></td>
                                        <td>1 – 2 tuổi</td>
                                        <td><strong>9 – 11 kg</strong></td>
                                        <td>75 – 85 cm</td>
                                        <td>36 – 38 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 2 (2Y)</strong></td>
                                        <td>2 – 3 tuổi</td>
                                        <td><strong>11 – 13 kg</strong></td>
                                        <td>85 – 95 cm</td>
                                        <td>39 – 41 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 3 (3Y)</strong></td>
                                        <td>3 – 4 tuổi</td>
                                        <td><strong>13 – 15 kg</strong></td>
                                        <td>95 – 105 cm</td>
                                        <td>42 – 44 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 4 (4Y)</strong></td>
                                        <td>4 – 5 tuổi</td>
                                        <td><strong>15 – 18 kg</strong></td>
                                        <td>105 – 115 cm</td>
                                        <td>45 – 47 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 5 (5Y)</strong></td>
                                        <td>5 – 6 tuổi</td>
                                        <td><strong>18 – 22 kg</strong></td>
                                        <td>115 – 125 cm</td>
                                        <td>48 – 50 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">Size 6 (6Y)</strong></td>
                                        <td>6 – 7 tuổi</td>
                                        <td><strong>22 – 26 kg</strong></td>
                                        <td>125 – 132 cm</td>
                                        <td>51 – 53 cm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: SƠ SINH -->
                    <div id="tab-sosinh" class="cung-size-tab-panel">
                        <div class="cung-table-responsive">
                            <table class="cung-size-table">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <th>Tháng tuổi</th>
                                        <th>Cân nặng khuyến nghị</th>
                                        <th>Chiều dài cơ thể (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong class="cung-size-tag">0 – 3M</strong></td>
                                        <td>0 – 3 tháng</td>
                                        <td><strong>3.0 – 5.5 kg</strong></td>
                                        <td>50 – 58 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">3 – 6M</strong></td>
                                        <td>3 – 6 tháng</td>
                                        <td><strong>5.5 – 7.5 kg</strong></td>
                                        <td>58 – 66 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">6 – 9M</strong></td>
                                        <td>6 – 9 tháng</td>
                                        <td><strong>7.5 – 9.0 kg</strong></td>
                                        <td>66 – 72 cm</td>
                                    </tr>
                                    <tr>
                                        <td><strong class="cung-size-tag">9 – 12M</strong></td>
                                        <td>9 – 12 tháng</td>
                                        <td><strong>9.0 – 11.0 kg</strong></td>
                                        <td>72 – 78 cm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Footer with contact assistance -->
                <?php
                $zalo_raw   = get_option('cung_contact_zalo', '0931442324');
                $zalo_clean = preg_replace('/[^0-9]/', '', $zalo_raw) ?: '0931442324';
                $hotline    = get_option('cung_contact_hotline', '093 144 23 24');
                ?>
                <div class="cung-modal-footer">
                    <span class="cung-modal-footer-note">Mẹ vẫn phân vân chưa biết chọn size nào?</span>
                    <a href="https://zalo.me/<?php echo esc_attr($zalo_clean); ?>" target="_blank" rel="noopener noreferrer" class="cung-btn-consult-zalo">
                        <span>Nhắn Zalo <?php echo esc_html($hotline); ?> để tư vấn size</span>
                    </a>
                </div>
            </div>
        </div>
        <!-- / CUNG STORE SIZE GUIDE MODAL -->
        <?php
    }
}

Cung_Size_Guide::init();
