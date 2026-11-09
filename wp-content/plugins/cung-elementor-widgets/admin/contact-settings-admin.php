<?php
/**
 * Cưng Store - Contact Settings Admin Page (Hotline, Zalo, Messenger)
 *
 * @package Cung_Widgets
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Contact_Settings_Admin {

    const OPTION_HOTLINE   = 'cung_contact_hotline';
    const OPTION_ZALO      = 'cung_contact_zalo';
    const OPTION_MESSENGER = 'cung_contact_messenger';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_submenu'], 30);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_submenu() {
        add_submenu_page(
            'cung-hero-slideshow',
            __('Cài đặt Hotline & Zalo - Cưng Store', 'cung-widgets'),
            __('Hotline & Zalo', 'cung-widgets'),
            'manage_options',
            'cung-contact-settings',
            [$this, 'render_page']
        );
    }

    public function register_settings() {
        register_setting('cung_contact_settings_group', self::OPTION_HOTLINE, [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '093 144 23 24',
        ]);
        register_setting('cung_contact_settings_group', self::OPTION_ZALO, [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0931442324',
        ]);
        register_setting('cung_contact_settings_group', self::OPTION_MESSENGER, [
            'sanitize_callback' => 'esc_url_raw',
            'default'           => 'https://m.me/sontruongnguyenn',
        ]);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $hotline   = get_option(self::OPTION_HOTLINE, '093 144 23 24');
        $zalo      = get_option(self::OPTION_ZALO, '0931442324');
        $messenger = get_option(self::OPTION_MESSENGER, 'https://m.me/sontruongnguyenn');
        ?>
        <div class="wrap" style="max-width: 820px; margin-top: 25px;">
            <div style="background: #FFFDF9; border: 1px solid rgba(169, 79, 101, 0.2); border-radius: 14px; padding: 28px 32px; box-shadow: 0 4px 18px rgba(60, 45, 49, 0.06); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px; border-bottom: 1px solid rgba(169, 79, 101, 0.15); padding-bottom: 18px;">
                    <span style="background: #A94F65; color: #FFF; width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 22px;">📞</span>
                    <div>
                        <h1 style="margin: 0; font-size: 22px; color: #3C2D31; font-weight: 700; line-height: 1.3;">Cài Đặt Hotline, Zalo & Messenger</h1>
                        <p style="margin: 4px 0 0; color: #6D5C60; font-size: 14px;">Thay đổi số điện thoại và link liên hệ tại đây. Toàn bộ nút liên hệ nổi và nút hỗ trợ bảng size sẽ tự động cập nhật theo!</p>
                    </div>
                </div>

                <form method="post" action="options.php">
                    <?php
                    settings_fields('cung_contact_settings_group');
                    do_settings_sections('cung_contact_settings_group');
                    ?>

                    <table class="form-table" role="presentation" style="margin-top: 0;">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 220px; padding: 18px 10px 18px 0;">
                                    <label for="cung_contact_hotline" style="font-weight: 600; color: #3C2D31; font-size: 14px;">Số Hotline gọi nhanh</label>
                                </th>
                                <td style="padding: 18px 0;">
                                    <input name="cung_contact_hotline" type="text" id="cung_contact_hotline" value="<?php echo esc_attr($hotline); ?>" class="regular-text" style="border-radius: 8px; padding: 8px 14px; border: 1.5px solid #DDD; font-size: 14px; width: 100%; max-width: 380px;" placeholder="093 144 23 24">
                                    <p class="description" style="margin-top: 6px; color: #777;">Hiển thị ở nhãn nút gọi và tự động kích hoạt tính năng gọi khi khách bấm vào.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="padding: 18px 10px 18px 0;">
                                    <label for="cung_contact_zalo" style="font-weight: 600; color: #3C2D31; font-size: 14px;">Số điện thoại Zalo</label>
                                </th>
                                <td style="padding: 18px 0;">
                                    <input name="cung_contact_zalo" type="text" id="cung_contact_zalo" value="<?php echo esc_attr($zalo); ?>" class="regular-text" style="border-radius: 8px; padding: 8px 14px; border: 1.5px solid #DDD; font-size: 14px; width: 100%; max-width: 380px;" placeholder="0931442324">
                                    <p class="description" style="margin-top: 6px; color: #777;">Hệ thống sẽ tự động tạo link chat Zalo chuẩn dạng <code>https://zalo.me/[số_điện_thoại]</code>.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row" style="padding: 18px 10px 18px 0;">
                                    <label for="cung_contact_messenger" style="font-weight: 600; color: #3C2D31; font-size: 14px;">Link Facebook Messenger</label>
                                </th>
                                <td style="padding: 18px 0;">
                                    <input name="cung_contact_messenger" type="url" id="cung_contact_messenger" value="<?php echo esc_attr($messenger); ?>" class="regular-text" style="border-radius: 8px; padding: 8px 14px; border: 1.5px solid #DDD; font-size: 14px; width: 100%; max-width: 380px;" placeholder="https://m.me/sontruongnguyenn">
                                    <p class="description" style="margin-top: 6px; color: #777;">Link mở nhanh Messenger (ví dụ: <code>https://m.me/sontruongnguyenn</code> hoặc Fanpage).</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(169, 79, 101, 0.15);">
                        <button type="submit" class="button button-primary" style="background-color: #A94F65; border-color: #A94F65; border-radius: 8px; padding: 8px 24px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 12px rgba(169, 79, 101, 0.25); cursor: pointer;">
                            Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
