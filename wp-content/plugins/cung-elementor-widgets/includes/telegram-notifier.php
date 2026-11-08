<?php
/**
 * Cưng Store - Telegram Order Notifier
 * Tự động gửi thông báo chi tiết đơn hàng WooCommerce về Telegram Real-time.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Cung_Telegram_Notifier {

    const OPTION_ENABLED = 'cung_telegram_enabled';
    const OPTION_TOKEN   = 'cung_telegram_bot_token';
    const OPTION_CHAT_ID = 'cung_telegram_chat_id';

    public function __construct() {
        // Bắt sự kiện đơn hàng mới hoặc thanh toán thành công
        add_action('woocommerce_thankyou', [$this, 'handle_order_notification'], 10, 1);
        add_action('woocommerce_payment_complete', [$this, 'handle_order_notification'], 10, 1);
        add_action('woocommerce_order_status_processing', [$this, 'handle_order_notification'], 10, 1);
    }

    /**
     * Xử lý gửi thông báo khi có đơn hàng
     */
    public function handle_order_notification($order_id) {
        if (!$order_id) return;

        // Kiểm tra xem tính năng có được bật không
        if (get_option(self::OPTION_ENABLED, 'yes') !== 'yes') {
            return;
        }

        // Chống gửi trùng tin nhắn nhiều lần cho cùng 1 đơn hàng
        $already_sent = get_post_meta($order_id, '_cung_telegram_notified', true);
        if ($already_sent === 'yes') {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) return;

        // Trích xuất thông tin khách hàng
        $customer_name  = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if (empty($customer_name)) $customer_name = 'Khách vãng lai';

        $phone          = $order->get_billing_phone() ?: 'Chưa cung cấp';
        $email          = $order->get_billing_email() ?: 'Chưa cung cấp';

        // Địa chỉ giao hàng
        $address_parts = array_filter([
            $order->get_shipping_address_1() ?: $order->get_billing_address_1(),
            $order->get_shipping_city() ?: $order->get_billing_city(),
            $order->get_shipping_state() ?: $order->get_billing_state(),
        ]);
        $full_address = !empty($address_parts) ? implode(', ', $address_parts) : 'Nhận tại cửa hàng';

        // Phương thức thanh toán & Trạng thái
        $payment_title = $order->get_payment_method_title() ?: 'Thanh toán khi nhận hàng (COD)';
        $status_label  = wc_get_order_status_name($order->get_status());
        $total_price   = html_entity_decode(wp_strip_all_tags(wc_price($order->get_total())));

        // Danh sách sản phẩm mua
        $items_text = '';
        foreach ($order->get_items() as $item) {
            $name     = $item->get_name();
            $qty      = $item->get_quantity();
            $subtotal = html_entity_decode(wp_strip_all_tags(wc_price($item->get_total())));
            $items_text .= "• <b>{$name}</b> (x{$qty}) — <code>{$subtotal}</code>\n";
        }
        if (empty($items_text)) {
            $items_text = "• Không có thông tin sản phẩm\n";
        }

        // Ghi chú của khách hàng
        $note = $order->get_customer_note();
        $note_text = !empty($note) ? "\n📝 <b>Ghi chú của khách:</b> <i>" . esc_html($note) . "</i>\n" : "";

        // Soạn tin nhắn HTML chuẩn Telegram
        $msg  = "🛍 <b>ĐƠN HÀNG MỚI: #" . $order_id . " - CƯNG STORE</b>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "👤 <b>Khách hàng:</b> " . esc_html($customer_name) . "\n";
        $msg .= "📞 <b>Điện thoại:</b> <code>" . esc_html($phone) . "</code>\n";
        $msg .= "📍 <b>Địa chỉ:</b> " . esc_html($full_address) . "\n";
        $msg .= "💳 <b>Thanh toán:</b> " . esc_html($payment_title) . "\n";
        $msg .= "📊 <b>Trạng thái:</b> <code>" . esc_html($status_label) . "</code>\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📦 <b>Chi tiết món hàng:</b>\n" . $items_text;
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "💰 <b>TỔNG CỘNG: " . $total_price . "</b>\n";
        $msg .= $note_text;
        $msg .= "⏰ <i>Thời gian: " . current_time('d/m/Y H:i:s') . "</i>";

        // Gửi qua Telegram API
        $res = self::send_message($msg);

        // Nếu gửi thành công thì đánh dấu đã gửi
        if ($res['success']) {
            update_post_meta($order_id, '_cung_telegram_notified', 'yes');
        }
    }

    /**
     * Hàm dùng chung gửi tin nhắn qua Telegram Bot API
     */
    public static function send_message($text, $token = '', $chat_id = '') {
        if (empty($token)) {
            $token = get_option(self::OPTION_TOKEN, '');
        }
        if (empty($chat_id)) {
            $chat_id = get_option(self::OPTION_CHAT_ID, '');
        }

        if (empty($token) || empty($chat_id)) {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình Bot Token hoặc Chat ID trong Cài đặt.'
            ];
        }

        $endpoint = "https://api.telegram.org/bot{$token}/sendMessage";

        $response = wp_remote_post($endpoint, [
            'timeout' => 12,
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode([
                'chat_id'                  => $chat_id,
                'text'                     => $text,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
            ]),
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code === 200 && isset($body['ok']) && $body['ok'] === true) {
            return [
                'success' => true,
                'message' => 'Gửi tin nhắn Telegram thành công!'
            ];
        }

        $err_desc = $body['description'] ?? 'Lỗi không xác định từ Telegram API';
        return [
            'success' => false,
            'message' => "Mã lỗi {$code}: {$err_desc}"
        ];
    }
}
