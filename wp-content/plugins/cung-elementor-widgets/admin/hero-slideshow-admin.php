<?php
/**
 * Cưng Store - Hero Slideshow Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Hero_Slideshow_Admin {

    const OPTION_SLIDES   = 'cung_hero_slides';
    const OPTION_SETTINGS = 'cung_hero_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'maybe_seed_defaults']);
    }

    public function add_menu() {
        add_menu_page(
            __('Quản lý Hero Slideshow', 'cung-widgets'),
            __('Cưng Slideshow', 'cung-widgets'),
            'manage_options',
            'cung-hero-slideshow',
            [$this, 'render_page'],
            'dashicons-images-alt2',
            25
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_cung-hero-slideshow') {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    public function maybe_seed_defaults() {
        $existing = get_option(self::OPTION_SLIDES, null);
        if ($existing === null) {
            $default_slides = [
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
            ];
            update_option(self::OPTION_SLIDES, $default_slides);
            update_option(self::OPTION_SETTINGS, [
                'interval' => 5000,
                'fit'      => 'cover'
            ]);
        }
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Bạn không có quyền truy cập trang này.', 'cung-widgets'));
        }

        $message = '';
        if (isset($_POST['cung_save_slides']) && check_admin_referer('cung_hero_slides_action', 'cung_hero_slides_nonce')) {
            $raw_slides = isset($_POST['slides']) && is_array($_POST['slides']) ? $_POST['slides'] : [];
            $clean_slides = [];

            foreach ($raw_slides as $slide) {
                $src   = isset($slide['src']) ? esc_url_raw(trim($slide['src'])) : '';
                $title = isset($slide['title']) ? sanitize_text_field(trim($slide['title'])) : '';
                $link  = isset($slide['link']) ? esc_url_raw(trim($slide['link'])) : '';

                if (!empty($src)) {
                    $clean_slides[] = [
                        'src'   => $src,
                        'title' => $title,
                        'link'  => $link
                    ];
                }
            }

            update_option(self::OPTION_SLIDES, $clean_slides);

            $interval = isset($_POST['interval']) ? max(2000, intval($_POST['interval'])) : 5000;
            $fit      = isset($_POST['fit']) && in_array($_POST['fit'], ['cover', 'contain'], true) ? $_POST['fit'] : 'cover';

            update_option(self::OPTION_SETTINGS, [
                'interval' => $interval,
                'fit'      => $fit
            ]);

            $message = '<div class="notice notice-success is-dismissible"><p><strong>' . __('Đã lưu danh sách slide và cấu hình thành công!', 'cung-widgets') . '</strong></p></div>';
        }

        $slides   = get_option(self::OPTION_SLIDES, []);
        $settings = get_option(self::OPTION_SETTINGS, ['interval' => 5000, 'fit' => 'cover']);

        ?>
        <div class="wrap cung-admin-wrap" style="max-width: 960px;">
            <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <span class="dashicons dashicons-images-alt2" style="font-size:32px;width:32px;height:32px;color:#8F3F54;"></span>
                <?php _e('Quản lý Hero Slideshow - Cưng Store', 'cung-widgets'); ?>
            </h1>

            <?php if (!empty($message)) echo $message; ?>

            <div class="card" style="padding: 20px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background:#fff; margin-bottom: 25px;">
                <p style="font-size: 14px; color: #555; margin-top:0;">
                    <?php _e('Trang quản lý các hình ảnh trình chiếu trên banner đầu trang chủ (Hero Banner). Bạn có thể bấm <strong>"Chọn ảnh"</strong> để tải từ máy tính hoặc chọn từ thư viện có sẵn, sau đó bấm <strong>"Lưu thay đổi"</strong>.', 'cung-widgets'); ?>
                </p>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('cung_hero_slides_action', 'cung_hero_slides_nonce'); ?>

                <!-- Cấu hình chung -->
                <div class="card" style="padding: 20px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background:#fff; margin-bottom: 25px;">
                    <h2 style="font-size: 16px; margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px; color:#8F3F54;">
                        <span class="dashicons dashicons-admin-settings"></span> <?php _e('Cài đặt hiển thị', 'cung-widgets'); ?>
                    </h2>
                    <table class="form-table" style="margin-top:0;">
                        <tr>
                            <th scope="row" style="width: 220px;"><?php _e('Thời gian chuyển slide', 'cung-widgets'); ?></th>
                            <td>
                                <input type="number" name="interval" value="<?php echo esc_attr($settings['interval'] ?? 5000); ?>" min="2000" step="500" style="width: 120px;" />
                                <span class="description"><?php _e('mili-giây (Mặc định: 5000ms = 5 giây)', 'cung-widgets'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Kiểu bao phủ ảnh', 'cung-widgets'); ?></th>
                            <td>
                                <select name="fit" style="width: 320px;">
                                    <option value="cover" <?php selected($settings['fit'] ?? 'cover', 'cover'); ?>>
                                        <?php _e('Bao phủ toàn bộ khung (Cover - Đẹp nhất, tràn viền)', 'cung-widgets'); ?>
                                    </option>
                                    <option value="contain" <?php selected($settings['fit'] ?? 'cover', 'contain'); ?>>
                                        <?php _e('Vừa vặn trong khung (Contain - Phù hợp ảnh cắt nền)', 'cung-widgets'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Chế độ <strong>Bao phủ toàn bộ khung</strong> sẽ làm ảnh phủ kín khung bo tròn sang trọng trên cả Mobile & Desktop.', 'cung-widgets'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Danh sách slide -->
                <div class="card" style="padding: 20px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background:#fff; margin-bottom: 25px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 15px;">
                        <h2 style="font-size: 16px; margin:0; color:#8F3F54;">
                            <span class="dashicons dashicons-format-gallery"></span> <?php _e('Danh sách hình ảnh Slide', 'cung-widgets'); ?>
                        </h2>
                        <button type="button" class="button button-secondary" id="cung-add-slide-btn" style="display:flex;align-items:center;gap:4px;">
                            <span class="dashicons dashicons-plus-alt2" style="margin-top:2px;"></span> <?php _e('Thêm slide mới', 'cung-widgets'); ?>
                        </button>
                    </div>

                    <div id="cung-slides-list" style="display:flex; flex-direction:column; gap: 15px;">
                        <?php if (!empty($slides)): ?>
                            <?php foreach ($slides as $idx => $slide): ?>
                                <?php $this->render_slide_row($idx, $slide); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="cung-no-slides" style="color:#888; font-style:italic;"><?php _e('Chưa có slide nào. Bấm "Thêm slide mới" để bắt đầu.', 'cung-widgets'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="submit" style="margin-top: 20px;">
                    <input type="submit" name="cung_save_slides" class="button button-primary button-hero" value="<?php esc_attr_e('Lưu tất cả thay đổi', 'cung-widgets'); ?>" style="background: #8F3F54; border-color: #793245; box-shadow: 0 4px 12px rgba(143,63,84,0.3); font-weight: 600;" />
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var slideIndex = <?php echo count($slides); ?>;

            // Media uploader
            $(document).on('click', '.cung-select-image-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $row = $btn.closest('.cung-slide-row');
                var $input = $row.find('.cung-slide-src-input');
                var $preview = $row.find('.cung-slide-preview-img');
                var $placeholder = $row.find('.cung-slide-preview-placeholder');

                var frame = wp.media({
                    title: '<?php echo esc_js(__('Chọn hoặc Tải ảnh lên cho Slide', 'cung-widgets')); ?>',
                    button: { text: '<?php echo esc_js(__('Dùng ảnh này cho Slide', 'cung-widgets')); ?>' },
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var url = attachment.url;
                    $input.val(url);
                    $preview.attr('src', url).show();
                    $placeholder.hide();
                });

                frame.open();
            });

            // Delete slide row
            $(document).on('click', '.cung-delete-slide-btn', function(e) {
                e.preventDefault();
                if (confirm('<?php echo esc_js(__('Bạn có chắc chắn muốn xóa slide này?', 'cung-widgets')); ?>')) {
                    $(this).closest('.cung-slide-row').fadeOut(300, function() {
                        $(this).remove();
                        if ($('#cung-slides-list .cung-slide-row').length === 0) {
                            $('#cung-slides-list').append('<p class="cung-no-slides" style="color:#888; font-style:italic;"><?php echo esc_js(__('Chưa có slide nào. Bấm "Thêm slide mới" để bắt đầu.', 'cung-widgets')); ?></p>');
                        }
                    });
                }
            });

            // Add new slide
            $('#cung-add-slide-btn').on('click', function(e) {
                e.preventDefault();
                $('.cung-no-slides').remove();
                var html = `
                <div class="cung-slide-row" style="display:flex; gap:16px; align-items:flex-start; padding:15px; background:#FAF7F5; border:1px solid #EAE3DE; border-radius:10px;">
                    <div style="width:110px; height:90px; border-radius:8px; overflow:hidden; background:#FFF; border:1px dashed #CCC; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <img class="cung-slide-preview-img" src="" style="width:100%; height:100%; object-fit:cover; display:none;" />
                        <span class="cung-slide-preview-placeholder" style="color:#AAA; font-size:12px; text-align:center; padding:5px;"><?php echo esc_js(__('Chưa chọn ảnh', 'cung-widgets')); ?></span>
                    </div>
                    <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; gap:8px;">
                            <input type="text" name="slides[${slideIndex}][src]" class="cung-slide-src-input regular-text" value="" placeholder="https://..." style="flex:1;" readonly />
                            <button type="button" class="button cung-select-image-btn" style="flex-shrink:0;">
                                <span class="dashicons dashicons-upload" style="margin-top:3px;"></span> <?php echo esc_js(__('Chọn / Tải ảnh', 'cung-widgets')); ?>
                            </button>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <input type="text" name="slides[${slideIndex}][title]" value="" placeholder="<?php echo esc_js(__('Tiêu đề / Tên trang phục (ví dụ: Váy hoa cổ sen)', 'cung-widgets')); ?>" style="flex:1;" />
                            <input type="text" name="slides[${slideIndex}][link]" value="http://localhost/cung/san-pham/" placeholder="<?php echo esc_js(__('Link khi click (ví dụ: http://localhost/cung/san-pham/)', 'cung-widgets')); ?>" style="flex:1;" />
                        </div>
                    </div>
                    <div>
                        <button type="button" class="button button-link-delete cung-delete-slide-btn" style="color:#a00; text-decoration:none; padding:5px;" title="<?php echo esc_js(__('Xóa slide', 'cung-widgets')); ?>">
                            <span class="dashicons dashicons-trash" style="font-size:20px;"></span>
                        </button>
                    </div>
                </div>`;
                $('#cung-slides-list').append(html);
                slideIndex++;
            });
        });
        </script>
        <?php
    }

    private function render_slide_row($idx, $slide) {
        $src   = $slide['src'] ?? '';
        $title = $slide['title'] ?? '';
        $link  = $slide['link'] ?? 'http://localhost/cung/san-pham/';
        ?>
        <div class="cung-slide-row" style="display:flex; gap:16px; align-items:flex-start; padding:15px; background:#FAF7F5; border:1px solid #EAE3DE; border-radius:10px;">
            <div style="width:110px; height:90px; border-radius:8px; overflow:hidden; background:#FFF; border:1px dashed #CCC; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <?php if (!empty($src)): ?>
                    <img class="cung-slide-preview-img" src="<?php echo esc_url($src); ?>" style="width:100%; height:100%; object-fit:cover;" />
                    <span class="cung-slide-preview-placeholder" style="display:none; color:#AAA; font-size:12px; text-align:center; padding:5px;"><?php _e('Chưa chọn ảnh', 'cung-widgets'); ?></span>
                <?php else: ?>
                    <img class="cung-slide-preview-img" src="" style="width:100%; height:100%; object-fit:cover; display:none;" />
                    <span class="cung-slide-preview-placeholder" style="color:#AAA; font-size:12px; text-align:center; padding:5px;"><?php _e('Chưa chọn ảnh', 'cung-widgets'); ?></span>
                <?php endif; ?>
            </div>
            <div style="flex:1; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; gap:8px;">
                    <input type="text" name="slides[<?php echo esc_attr($idx); ?>][src]" class="cung-slide-src-input regular-text" value="<?php echo esc_attr($src); ?>" placeholder="https://..." style="flex:1;" readonly />
                    <button type="button" class="button cung-select-image-btn" style="flex-shrink:0;">
                        <span class="dashicons dashicons-upload" style="margin-top:3px;"></span> <?php _e('Chọn / Tải ảnh', 'cung-widgets'); ?>
                    </button>
                </div>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="slides[<?php echo esc_attr($idx); ?>][title]" value="<?php echo esc_attr($title); ?>" placeholder="<?php esc_attr_e('Tiêu đề / Tên trang phục (ví dụ: Váy hoa cổ sen)', 'cung-widgets'); ?>" style="flex:1;" />
                    <input type="text" name="slides[<?php echo esc_attr($idx); ?>][link]" value="<?php echo esc_attr($link); ?>" placeholder="<?php esc_attr_e('Link khi click (ví dụ: http://localhost/cung/san-pham/)', 'cung-widgets'); ?>" style="flex:1;" />
                </div>
            </div>
            <div>
                <button type="button" class="button button-link-delete cung-delete-slide-btn" style="color:#a00; text-decoration:none; padding:5px;" title="<?php esc_attr_e('Xóa slide', 'cung-widgets'); ?>">
                    <span class="dashicons dashicons-trash" style="font-size:20px;"></span>
                </button>
            </div>
        </div>
        <?php
    }
}
