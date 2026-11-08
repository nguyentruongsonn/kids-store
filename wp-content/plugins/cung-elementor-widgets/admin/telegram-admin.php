<?php
/**
 * Cưng Store - Telegram Notifier Admin Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Telegram_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
    }

    public function add_menu() {
        add_submenu_page(
            'cung-hero-slideshow',
            __('Thông báo Telegram', 'cung-widgets'),
            __('Thông báo Telegram', 'cung-widgets'),
            'manage_options',
            'cung-telegram-settings',
            [$this, 'render_page']
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Bạn không có quyền truy cập trang này.', 'cung-widgets'));
        }

        $notice = '';

        // Xử lý gửi tin nhắn Test
        if (isset($_POST['cung_test_telegram']) && check_admin_referer('cung_telegram_action', 'cung_telegram_nonce')) {
            $test_token   = sanitize_text_field(trim($_POST['bot_token'] ?? ''));
            $test_chat_id = sanitize_text_field(trim($_POST['chat_id'] ?? ''));

            if (empty($test_token) || empty($test_chat_id)) {
                $notice = '<div class="notice notice-error is-dismissible"><p><strong>' . __('Vui lòng nhập đầy đủ Bot Token và Chat ID trước khi bấm thử nghiệm!', 'cung-widgets') . '</strong></p></div>';
            } else {
                $test_msg  = "🎉 <b>CƯNG STORE - KẾT NỐI TELEGRAM THÀNH CÔNG!</b>\n";
                $test_msg .= "━━━━━━━━━━━━━━━━━━━━\n";
                $test_msg .= "✅ Bot thông báo đơn hàng của website đã kết nối hoàn hảo với điện thoại của bạn.\n";
                $test_msg .= "🛍 Từ bây giờ, mỗi khi có khách đặt hàng mới hoặc quét VietQR thành công, chi tiết đơn sẽ tự động gửi về đây tức thì!\n";
                $test_msg .= "⏰ <i>Kiểm tra lúc: " . current_time('d/m/Y H:i:s') . "</i>";

                $res = Cung_Telegram_Notifier::send_message($test_msg, $test_token, $test_chat_id);

                if ($res['success']) {
                    $notice = '<div class="notice notice-success is-dismissible"><p><strong>' . __('Tuyệt vời! Đã gửi tin nhắn thử nghiệm thành công. Hãy kiểm tra ứng dụng Telegram trên điện thoại của bạn!', 'cung-widgets') . '</strong></p></div>';
                } else {
                    $notice = '<div class="notice notice-error is-dismissible"><p><strong>' . sprintf(__('Gửi thất bại: %s', 'cung-widgets'), esc_html($res['message'])) . '</strong><br><small>' . __('Hãy kiểm tra lại: 1. Bạn đã bấm START chat với bot chưa? 2. Bot Token và Chat ID đã nhập chính xác chưa?', 'cung-widgets') . '</small></p></div>';
                }
            }
        }

        // Xử lý lưu cài đặt
        if (isset($_POST['cung_save_telegram']) && check_admin_referer('cung_telegram_action', 'cung_telegram_nonce')) {
            $enabled = isset($_POST['enabled']) ? 'yes' : 'no';
            $token   = sanitize_text_field(trim($_POST['bot_token'] ?? ''));
            $chat_id = sanitize_text_field(trim($_POST['chat_id'] ?? ''));

            update_option(Cung_Telegram_Notifier::OPTION_ENABLED, $enabled);
            update_option(Cung_Telegram_Notifier::OPTION_TOKEN, $token);
            update_option(Cung_Telegram_Notifier::OPTION_CHAT_ID, $chat_id);

            $notice = '<div class="notice notice-success is-dismissible"><p><strong>' . __('Đã lưu cấu hình thông báo Telegram thành công!', 'cung-widgets') . '</strong></p></div>';
        }

        $enabled = get_option(Cung_Telegram_Notifier::OPTION_ENABLED, 'yes');
        $token   = get_option(Cung_Telegram_Notifier::OPTION_TOKEN, '');
        $chat_id = get_option(Cung_Telegram_Notifier::OPTION_CHAT_ID, '');

        ?>
        <div class="wrap cung-admin-wrap" style="max-width: 960px;">
            <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <span class="dashicons dashicons-format-chat" style="font-size:32px;width:32px;height:32px;color:#8F3F54;"></span>
                <?php _e('Cấu hình Thông Báo Đơn Hàng qua Telegram - Cưng Store', 'cung-widgets'); ?>
            </h1>

            <?php if (!empty($notice)) echo $notice; ?>

            <!-- Hướng dẫn nhanh 2 phút -->
            <div class="card" style="padding: 20px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background:#fff; margin-bottom: 25px; border-left: 4px solid #8F3F54;">
                <h3 style="margin-top:0; color:#8F3F54; font-size:16px;">
                    <span class="dashicons dashicons-info"></span> <?php _e('Hướng dẫn 2 phút lấy Token & Chat ID (Hoàn toàn miễn phí):', 'cung-widgets'); ?>
                </h3>
                <ol style="margin: 0; padding-left: 20px; line-height: 1.8; color: #444;">
                    <li><strong>Lấy Bot Token:</strong> Mở Telegram, tìm <code>@BotFather</code> (tích xanh), gửi lệnh <code>/newbot</code>, đặt tên hiển thị và username bot (kết thúc bằng <code>_bot</code>). BotFather sẽ gửi cho bạn một chuỗi dài mã <strong>HTTP API Token</strong>.</li>
                    <li><strong>Kích hoạt Bot:</strong> Bấm vào đường link bot bạn vừa tạo và bấm nút <strong>START</strong> trên Telegram.</li>
                    <li><strong>Lấy Chat ID:</strong> Mở tìm kiếm Telegram, tìm <code>@userinfobot</code>, bấm <strong>START</strong> $\rightarrow$ Bạn sẽ nhận được số <strong>Id</strong> của mình (ví dụ: <code>123456789</code>).</li>
                    <li><strong>Dán vào bên dưới</strong> và bấm <strong>"Gửi tin nhắn thử nghiệm"</strong> để thấy tin nhắn báo về điện thoại ngay tức thì!</li>
                </ol>
            </div>

            <!-- Form cài đặt -->
            <form method="post" action="">
                <?php wp_nonce_field('cung_telegram_action', 'cung_telegram_nonce'); ?>

                <div class="card" style="padding: 24px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); background:#fff;">
                    <table class="form-table" style="margin-top:0;">
                        <tr>
                            <th scope="row" style="width: 220px;"><?php _e('Kích hoạt thông báo', 'cung-widgets'); ?></th>
                            <td>
                                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                                    <input type="checkbox" name="enabled" value="yes" <?php checked($enabled, 'yes'); ?> style="width:20px;height:20px;" />
                                    <?php _e('Tự động gửi tin nhắn báo về Telegram mỗi khi có đơn hàng mới / thanh toán VietQR thành công', 'cung-widgets'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Telegram Bot Token', 'cung-widgets'); ?></th>
                            <td>
                                <input type="text" name="bot_token" value="<?php echo esc_attr($token); ?>" class="large-text" placeholder="Ví dụ: 7458921456:AAFlB3_xyz987Abc..." style="max-width: 550px; font-family: monospace;" />
                                <p class="description"><?php _e('Mã Token bí mật do @BotFather cung cấp khi bạn tạo bot.', 'cung-widgets'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Telegram Chat ID', 'cung-widgets'); ?></th>
                            <td>
                                <input type="text" name="chat_id" value="<?php echo esc_attr($chat_id); ?>" class="regular-text" placeholder="Ví dụ: 123456789" style="max-width: 300px; font-family: monospace;" />
                                <p class="description"><?php _e('ID người nhận cá nhân hoặc ID nhóm Telegram (do @userinfobot cung cấp).', 'cung-widgets'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; display:flex; gap: 15px; align-items:center;">
                        <input type="submit" name="cung_save_telegram" class="button button-primary button-hero" value="<?php esc_attr_e('Lưu cấu hình', 'cung-widgets'); ?>" style="background: #8F3F54; border-color: #793245; font-weight:600;" />
                        
                        <input type="submit" name="cung_test_telegram" class="button button-secondary button-hero" value="<?php esc_attr_e('🔔 Gửi tin nhắn thử nghiệm', 'cung-widgets'); ?>" style="font-weight:600;" />
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}
