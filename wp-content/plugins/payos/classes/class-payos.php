<?php
// phpcs:disable WordPress.Security.NonceVerification -- disable nonce warning

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * payOS Transfer Payment Gateway.
 *
 * Provides a payOS Payment Gateway. Based on code by Mike Pepper.
 *
 * @class       WC_payOS_Payment_Gateway
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce\Classes\Payment
 */
class WC_payOS_Payment_Gateway extends WC_Payment_Gateway
{

	/**
	 * Array of locales
	 *
	 * @var array
	 */
	public $locale;

	/**
	 * PayOS gateway settings
	 * 
	 * @var array 
	 */
	public $payos_gateway_settings;

	public $message;

	public $instructions;

	/**
	 * Lý do tạo webhook thất bại gần nhất (hiển thị cho admin).
	 *
	 * @var string
	 */
	public $webhook_error = '';

	/**
	 * Khoá webhook đang giữ: array('name' => option_name, 'value' => "thời điểm:mã riêng"), null nếu không giữ.
	 *
	 * @var array|null
	 */
	private $payos_webhook_lock = null;

	/**
	 * Constructor for the gateway.
	 */
	const PAYOS_URL_MERCHANT_API = "https://api-merchant.payos.vn";
	static private $PAYOS_CHECKOUT_HOST = "https://pay.payos.vn";
	static private $PAYOS_URL_CREATE_PAYMENT_LINK = self::PAYOS_URL_MERCHANT_API . "/v2/payment-requests";
	static private $PAYOS_URL_CONFIRM_WEBHOOK = self::PAYOS_URL_MERCHANT_API . "/confirm-webhook";
	static private $URL_GET_PAYMENT_LINK_INFO = self::PAYOS_URL_MERCHANT_API . "/v2/payment-requests/";
	static private $PAYOS_SUCCESS = "00";
	static private $PAYOS_NOT_FOUND_ORDER_CODE = 101;
	static private $PAYOS_ORDER_EXIST = 231;
	static private $PAYOS_WEBHOOK_ENDPOINT = "verify_payos_webhook";
	static private $PAYOS_WEBHOOK_LOCK_TTL = 300;
	static public $payos_default_settings = array(
		'use_payment_gateway'         => 'yes',
		'client_id' => '',
		'api_key' => '',
		'checksum_key' => '',
		'order_status' => array(
			'order_status_after_paid'   => 'wc-processing',
			'order_status_after_underpaid' => 'wc-underpaid',
			'order_status_after_failed' => 'wc-failed',
		),
		'transaction_prefix' => 'DH',
		'link_webhook' => 'no',
		'gateway_info' => array(
			'name' => '',
			'account_number' => '',
			'account_name' => '',
			'bank_name' => ''
		),
		'refresh_upon_successful_payment' => 'no'
	);
	static private $PAYOS_BLACK_LIST_PREFIX = array("FT", "TF", "TT", "VQR");
	public function __construct()
	{
		$this->id                 = 'payos';
		$this->has_fields         = false;
		$this->icon               = apply_filters('woocommerce_icon_payos', PAYOS_GATEWAY_URL . '/assets/img/payos_crop.png');
		$this->method_title       = __('Payment by bank transfer (Scan VietQR)', 'payos');
		$this->method_description = __('Take payments by scanning QR code with Vietnamese banking App. Supported by most major banks in Vietnam', 'payos');

		$this->message = '';

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		// Shop chưa tự đặt tên (đang dùng tên mặc định cũ hoặc mới): bỏ tên đã lưu để WooCommerce dùng tên mặc định,
		// dịch theo ngôn ngữ ở mỗi request. Tên mặc định đồng nhất giữa VietQR, Casso Flow, payOS.
		if (in_array($this->get_option('title'), array('Payment by bank transfer (Scan VietQR)', 'Thanh toán bằng chuyển khoản ngân hàng (quét mã QR)', 'VietQR payment via payOS', 'Thanh toán VietQR qua cổng payOS'), true)) {
			unset($this->settings['title']);
			update_option($this->get_option_key(), $this->settings, 'yes');
		}
		$this->title        = $this->get_option('title');
		$this->description  = $this->get_option('description');

		// payOS account fields shown on the thanks page and in emails.
		$this->payos_gateway_settings = $this->payos_get_gateway_settings();

		// Nút "Kiểm tra kết nối payOS" trên trang cài đặt: chỉ admin, kèm nonce của form
		if (isset($_POST['payos_gateway_settings']) && (isset($_POST['payos_check_connection']) || isset($_POST['submit'])) && $this->payos_can_save_settings()) {
			$this->payos_save_settings_and_webhook();
		}

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'payos_save_settings_and_reset_webhook'));
		add_action('woocommerce_thankyou_' . $this->id, array($this, 'payos_thankyou_page'), 5);
		// Customer Emails.
		add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
		// Customize Order Button
		add_action('woocommerce_after_checkout_validation', array($this, 'payos_check_gateway_settings'), 10, 2);

		add_action('woocommerce_api_' . self::$PAYOS_WEBHOOK_ENDPOINT, array($this, 'payos_verify_payment_handler'));
		add_action('admin_notices', array($this, 'payos_notice_checkout'));

		// custom thankyou text
		add_filter('woocommerce_thankyou_order_received_text', array($this, 'payos_order_received_text'), 10, 2);
		// Custom checkout ui
		add_shortcode('payos_checkout', array($this, 'payos_checkout_shortcode'));
		add_action('admin_enqueue_scripts', array($this, 'payos_enqueue_admin_script'));
	}

	/**
	 * Lưu cài đặt chỉ từ trang WooCommerce > Cài đặt > Thanh toán > payOS, người dùng có quyền quản lý WooCommerce
	 * và nonce payos_save_settings hợp lệ (form cài đặt có sẵn trường payos_nonce).
	 */
	private function payos_can_save_settings()
	{
		if (!is_admin() || wp_doing_ajax() || !current_user_can('manage_woocommerce')) {
			return false;
		}
		if (!isset($_GET['page'], $_GET['section']) || 'wc-settings' !== $_GET['page'] || $this->id !== $_GET['section']) {
			return false;
		}
		$nonce = isset($_POST['payos_nonce']) ? sanitize_text_field(wp_unslash($_POST['payos_nonce'])) : '';
		return (bool) wp_verify_nonce($nonce, 'payos_save_settings');
	}

	public function payos_notice_checkout()
	{
		$payos_gateway_settings = $this->payos_get_gateway_settings();
		if (
			!isset($payos_gateway_settings['client_id']) || strlen($payos_gateway_settings['client_id']) == 0
			|| !isset($payos_gateway_settings['api_key']) || strlen($payos_gateway_settings['api_key']) == 0
			|| !isset($payos_gateway_settings['checksum_key']) || strlen($payos_gateway_settings['checksum_key']) == 0
		) {
			echo '<div class="notice notice-warning">
					<p>' . esc_html__('payOS has not been set up yet!', 'payos') . '</p>
				</div>';
		}
	}

	public function payos_check_gateway_settings($data, $error)
	{
		if ($data['payment_method'] === $this->id) {
			$payos_gateway_settings = $this->payos_get_gateway_settings();
			if (
				!isset($payos_gateway_settings['client_id']) || strlen($payos_gateway_settings['client_id']) == 0
				|| !isset($payos_gateway_settings['api_key']) || strlen($payos_gateway_settings['api_key']) == 0
				|| !isset($payos_gateway_settings['checksum_key']) || strlen($payos_gateway_settings['checksum_key']) == 0
			) {
				wc_add_notice(__('payOS has not been set up by the administrator. Please contact the system administrator.', 'payos'), 'error');
			}
		}
	}

	/**
	 * Chuẩn hoá các trường người dùng nhập trên trang cài đặt, dùng chung cho nút "Kiểm tra kết nối payOS"
	 * và "Lưu thay đổi". Chỉ trả về các trường được phép sửa; trạng thái đơn không hợp lệ giữ giá trị đang lưu.
	 * Không có tác dụng phụ, gọi nhiều lần vẫn cho cùng kết quả.
	 *
	 * @param array $input payos_gateway_settings từ form, đã wp_unslash.
	 * @return array
	 */
	private function payos_normalize_settings($input)
	{
		$text = function ($key, $source) {
			return isset($source[$key]) && is_scalar($source[$key]) ? sanitize_text_field((string) $source[$key]) : '';
		};

		// Tối đa 3 ký tự chữ/số, không bắt đầu bằng mã giao dịch ngân hàng (FT, TF, TT, VQR), không phân biệt hoa thường
		$prefix = substr(preg_replace('/[^a-zA-Z0-9]/', '', $text('transaction_prefix', $input)), 0, 3);
		foreach (self::$PAYOS_BLACK_LIST_PREFIX as $blocked) {
			if (0 === stripos($prefix, $blocked)) {
				$prefix = self::$payos_default_settings['transaction_prefix'];
				break;
			}
		}

		$posted_status = isset($input['order_status']) && is_array($input['order_status']) ? $input['order_status'] : array();
		$order_status  = array();
		foreach (array(
			'order_status_after_paid'      => $this->payos_get_order_statuses_after_paid(),
			'order_status_after_underpaid' => $this->payos_get_order_statuses_after_underpaid(),
			'order_status_after_failed'    => $this->payos_get_order_statuses_after_failed(),
		) as $key => $choices) {
			$value              = $text($key, $posted_status);
			$order_status[$key] = isset($choices[$value]) ? $value : ($this->payos_gateway_settings['order_status'][$key] ?? self::$payos_default_settings['order_status'][$key]);
		}

		return array(
			'client_id'                       => $text('client_id', $input),
			'api_key'                         => $text('api_key', $input),
			'checksum_key'                    => $text('checksum_key', $input),
			'transaction_prefix'              => $prefix,
			'order_status'                    => $order_status,
			'refresh_upon_successful_payment' => 'yes' === $text('refresh_upon_successful_payment', $input) ? 'yes' : 'no',
		);
	}

	public function payos_save_settings_and_webhook()
	{
		if (is_array($_POST['payos_gateway_settings'])) {
			$sanitized_settings = $this->payos_normalize_settings(wp_unslash($_POST['payos_gateway_settings']));

			$create_webhook = $this->payos_create_webhook($sanitized_settings);
			if ($create_webhook) {
				$gateway_info = json_decode($create_webhook, true);
				$this->payos_update_gateway_settings("yes", $gateway_info, $sanitized_settings);

				$this->message = '<div class="updated notice"><p><strong>' .
					esc_html__('Successful webhook registration', 'payos') .
					'</p></strong></div>';
			} else {
				$this->payos_update_gateway_settings("no", self::$payos_default_settings['gateway_info']);

				$this->message =
					'<div class="error notice"><p><strong>' .
					esc_html__('Webhook creation failed', 'payos') .
					'</strong>' . ($this->webhook_error ? '<br>' . esc_html($this->webhook_error) : '') . '</p></div>';
			}
			// Message for use
			$this->message .=
				'<div class="updated notice"><p><strong>' .
				esc_html__('Settings saved', 'payos') .
				'</p></strong></div>';
		} else {
			$this->payos_update_gateway_settings("no", self::$payos_default_settings['gateway_info']);
			$this->message =
				'<div class="error notice"><p><strong>' .
				esc_html__('Can not save settings! Please refresh this page.', 'payos') .
				'</p></strong></div>';
		}
	}

	public function payos_update_gateway_settings($webhook_status, $gateway_info, $gateway_settings = null)
	{
		if ($webhook_status == 'no') {
			$this->payos_gateway_settings['gateway_info'] = $gateway_info;
		} else {
			$this->payos_gateway_settings = array_merge($this->payos_gateway_settings, $gateway_settings);
			// Thông tin kênh do payOS trả về: vẫn làm sạch trước khi lưu
			$channel = isset($gateway_info['data']) && is_array($gateway_info['data']) ? $gateway_info['data'] : array();
			foreach (array('account_name' => 'accountName', 'account_number' => 'accountNumber', 'name' => 'name', 'bank_name' => 'shortName') as $key => $field) {
				$this->payos_gateway_settings['gateway_info'][$key] = isset($channel[$field]) && is_scalar($channel[$field]) ? sanitize_text_field((string) $channel[$field]) : '';
			}
		}
		$this->payos_gateway_settings['link_webhook'] = $webhook_status;
		update_option("payos_gateway_settings", $this->payos_gateway_settings);
	}

	static function payos_get_gateway_settings()
	{
		$payos_settings = get_option('payos_gateway_settings', self::$payos_default_settings);
		$payos_settings = wp_parse_args($payos_settings, self::$payos_default_settings);
		return $payos_settings;
	}

	public function payos_save_settings_and_reset_webhook()
	{
		// WooCommerce đã kiểm tra nonce trước khi gọi hook woocommerce_update_options_payment_gateways_payos
		if (!current_user_can('manage_woocommerce')) {
			return;
		}
		if (isset($_POST['payos_gateway_settings']) && is_array($_POST['payos_gateway_settings'])) {
			$settings = $this->payos_normalize_settings(wp_unslash($_POST['payos_gateway_settings']));

			// Đổi khoá thì phải kiểm tra kết nối lại; trạng thái kết nối lấy từ cấu hình đang lưu, không lấy từ form
			$keys_changed = $settings['client_id'] !== $this->payos_gateway_settings['client_id']
				|| $settings['api_key'] !== $this->payos_gateway_settings['api_key']
				|| $settings['checksum_key'] !== $this->payos_gateway_settings['checksum_key'];
			$settings['link_webhook'] = $keys_changed ? 'no' : $this->payos_gateway_settings['link_webhook'];

			// Giữ các trường không có trên form, vd. thông tin kênh thanh toán
			$this->payos_gateway_settings = array_merge($this->payos_gateway_settings, $settings);
			update_option('payos_gateway_settings', $this->payos_gateway_settings);
		}
	}

	/**
	 * Initialize Gateway Settings Form Fields. 
	 * 
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled'         => array(
				'title'   => __('Enable/Disable', 'payos'),
				'type'    => 'checkbox',
				'label'   => __('Enable bank transfer', 'payos'),
				'default' => 'true',
			),
			'title'           => array(
				'title'       => __('Title', 'payos'),
				'type'        => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'payos'),
				'default'     => __('VietQR payment via payOS', 'payos'),
				'desc_tip'    => true,
			),
			'description'     => array(
				'title'       => __('Description', 'payos'),
				'type'        => 'textarea',
				'description' => __('Payment method description that the customer will see on your checkout.', 'payos'),
				'default'     => __('Pay for orders via payOS. Supported by almost Vietnamese banking apps', 'payos'),
				'desc_tip'    => true,
			),
			'payos_gateway_settings' => array(
				'type' => 'payos_gateway_settings',
			)
		);
	}

	public function payos_enqueue_admin_script()
	{
		// chỉ trang WooCommerce > Cài đặt > Thanh toán > payOS (CSS cũ ghi đè #submit của mọi trang quản trị)
		if (!isset($_GET['page'], $_GET['section']) || 'wc-settings' !== $_GET['page'] || $this->id !== $_GET['section']) {
			return;
		}
		wp_register_script('payos-admin-script', PAYOS_GATEWAY_URL . '/assets/js/payos-admin.js', array(), PAYOS_GATEWAY_VERSION, true);
		$payos_admin_data = array(
			'show_less' => __('Show less', 'payos'),
			'connect_status' => $this->payos_gateway_settings['link_webhook'] == 'no' ? __('No Connection', 'payos') : __('Connected', 'payos'),
			'hide' => __('Hide', 'payos'),
			'show' => __('Show', 'payos')
		);
		wp_localize_script('payos-admin-script', 'payos_data', $payos_admin_data);
		wp_enqueue_script('payos-admin-script');
		wp_enqueue_style('payos-custom-styles', PAYOS_GATEWAY_URL . '/assets/css/payos-admin.css', array(), PAYOS_GATEWAY_VERSION, 'all');
	}
	/**
	 * Generate account details html.
	 *
	 * @return string
	 */
	public function generate_payos_gateway_settings_html()
	{
		ob_start();
		$allowed_html_tags = [
			'tr' => ['id' => []],
			'th' => ['scope' => []],
			'td' => ['class' => [], 'id' => []],
			'tbody' => ['id' => []],
			'button' => [
				'id' => [],
				'onclick' => [],
				'type' => [],
				'class' => []
			],
			'div' => ['id' => [], 'class' => []],
			'ul' => [],
			'li' => [],
			'b' => [],
			'input' => [
				'type' => [],
				'name' => [],
				'value' => [],
				'id' => [],
				'onclick' => [],
				'class' => [],
				'checked' => []
			],
			'select' => [
				'name' => [],
				'id' => []
			],
			'option' => [
				'value' => [],
				'selected' => []
			],
			'label' => [
				'for' => [],
				'id' => []
			],
			'a' => [
				'id' => [],
				'href' => [],
				'onclick' => [],
				'onkeypress' => []
			],
			'br' => []
		];
		echo wp_kses_post($this->message);
		echo wp_kses('<input type="hidden" id="action" name="action" value="payos_save_settings">
		<input type="hidden" id="payos_nonce" name="payos_nonce" value="' . esc_attr(wp_create_nonce('payos_save_settings')) . '">', $allowed_html_tags);
		if ($this->payos_gateway_settings['use_payment_gateway'] == 'yes') {

			$this->payos_connection_html();
			$payment_gateway_config = '
			<tr>
				<th scope="row">' . esc_html(__('Prefix:', 'payos')) . '</th>
				<td id="payos_gateway_settings">
					<input name="payos_gateway_settings[transaction_prefix]" type="text" value="' . esc_attr($this->payos_gateway_settings['transaction_prefix']) . '" id="transaction_prefix" />
					<label id="transaction_prefix" for="transaction_prefix">' . esc_html(__('Maximum 3 characters, no spaces and no special characters. If contained, it will be deleted. Please do not prefix starting with FT, TF, TT, VQR', 'payos')) . '</label>
				</td>
			</tr>
			<tr>
				<th scope="row">' . esc_html(__('Status if payment is successful:', 'payos')) . '</th>
				<td id="payos_gateway_settings">
					<select name="payos_gateway_settings[order_status][order_status_after_paid]" id="order_status_after_paid">';

			foreach ($this->payos_get_order_statuses_after_paid() as $key => $value) {
				$payment_gateway_config .= '<option value="' . esc_attr($key) . '" ' . selected($key, $this->payos_gateway_settings["order_status"]["order_status_after_paid"], false) . '>' . esc_html($value) . '</option>';
			}

			$payment_gateway_config .= '</select>
				</td>
			</tr>
			<tr>
				<th scope="row">' . esc_html(__('Status if payment is underpaid:', 'payos')) . '</th>
				<td id="payos_gateway_settings">
					<select name="payos_gateway_settings[order_status][order_status_after_underpaid]" id="order_status_after_underpaid">';

			foreach ($this->payos_get_order_statuses_after_underpaid() as $key => $value) {
				$payment_gateway_config .= '<option value="' . esc_attr($key) . '" ' . selected($key, $this->payos_gateway_settings['order_status']['order_status_after_underpaid'], false) . '>' . esc_html($value) . '</option>';
			}

			$payment_gateway_config .= '</select>
				</td>
			</tr>
			<tr>
				<th scope="row">' . esc_html(__('Status if payment is failed:', 'payos')) . '</th>
				<td id="payos_gateway_settings">
					<select name="payos_gateway_settings[order_status][order_status_after_failed]" id="order_status_after_failed">';

			foreach ($this->payos_get_order_statuses_after_failed() as $key => $value) {
				$payment_gateway_config .= '<option value="' . esc_attr($key) . '" ' . selected($key, $this->payos_gateway_settings['order_status']['order_status_after_failed'], false) . '>' . esc_html($value) . '</option>';
			}

			$payment_gateway_config .= '</select>
				</td>
			</tr>';

			$payment_gateway_config .= '<tr id="payos_gateway_settings_refresh" valign="top">
				<th scope="row">' . esc_html(__('Enable refresh upon successful payment:', 'payos')) . '</th>
				<td id="payos_gateway_settings">
					<input name="payos_gateway_settings[refresh_upon_successful_payment]" type="hidden" value="no">
					<input name="payos_gateway_settings[refresh_upon_successful_payment]" type="checkbox" value="yes" id="refresh" ' . checked($this->payos_gateway_settings['refresh_upon_successful_payment'], 'yes', false) . ' />
					<label for="payos_gateway_settings[refresh_upon_successful_payment]">' . esc_html(__('Enable', 'payos')) . '</label>
				</td>
			</tr>';

			echo wp_kses($payment_gateway_config, $allowed_html_tags);
		}
		return ob_get_clean();
	}

	/**
	 * Phần kết nối kênh thanh toán payOS trong trang cài đặt: trạng thái, webhook URL (chỉ đọc),
	 * Client ID / API Key / Checksum Key và nút kiểm tra kết nối.
	 */
	private function payos_connection_html()
	{
		$settings    = $this->payos_gateway_settings;
		$connected   = 'yes' === $settings['link_webhook'];
		$info        = wp_parse_args((array) $settings['gateway_info'], self::$payos_default_settings['gateway_info']);
		$webhook_url = self::get_webhook_url();
		$is_local    = self::payos_is_local_url($webhook_url);
		$keys        = array(
			'client_id'    => array(__('Client ID', 'payos'), 'payos_client_id', 'show_client_id', 'showClientId()'),
			'api_key'      => array(__('API Key', 'payos'), 'payos_api_key', 'show_api_key', 'showApiKey()'),
			'checksum_key' => array(__('Checksum Key', 'payos'), 'payos_checksum_key', 'show_checksum_key', 'showChecksumKey()'),
		);
		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php esc_html_e('payOS connection', 'payos'); ?></th>
			<td class="forminp">
				<div class="payos-connect">
					<div class="payos-connect__status payos-connect__status--<?php echo $connected ? 'on' : 'off'; ?>">
						<span class="payos-connect__badge"><?php $connected ? esc_html_e('Connected', 'payos') : esc_html_e('Not connected', 'payos'); ?></span>
						<?php if ($connected) : ?>
							<dl class="payos-connect__info">
								<?php
								foreach (array(
									__('Payment channel', 'payos') => $info['name'],
									__('Bank', 'payos')            => $info['bank_name'],
									__('Account number', 'payos')  => $info['account_number'],
									__('Account name', 'payos')    => $info['account_name'],
								) as $label => $value) :
									if ('' === (string) $value) {
										continue;
									}
									?>
									<div><dt><?php echo esc_html($label); ?></dt><dd><?php echo esc_html($value); ?></dd></div>
								<?php endforeach; ?>
							</dl>
						<?php else : ?>
							<p><?php esc_html_e('Connect your payOS payment channel to show a QR code for every order and confirm payments automatically.', 'payos'); ?></p>
						<?php endif; ?>
					</div>

					<div class="payos-connect__field">
						<label for="payos_webhook_url"><?php esc_html_e('Webhook URL', 'payos'); ?></label>
						<div class="payos-connect__copy">
							<input type="text" id="payos_webhook_url" class="large-text code" value="<?php echo esc_attr($webhook_url); ?>" readonly onfocus="this.select()">
							<button type="button" class="button" data-payos-copy="payos_webhook_url" data-copied="<?php esc_attr_e('Copied', 'payos'); ?>"><?php esc_html_e('Copy', 'payos'); ?></button>
						</div>
						<p class="description"><?php esc_html_e('payOS sends payment notifications to this address. It is registered with payOS automatically when you click "Check connection payOS", you do not need to enter it on my.payos.vn.', 'payos'); ?></p>
						<?php if ($is_local) : ?>
							<div class="notice notice-warning inline payos-connect__warning">
								<p>
									<?php
									/* translators: %s: webhook URL */
									echo esc_html(sprintf(__('payOS needs to reach your website over the Internet to send payment notifications, but the webhook URL %s is a local address (localhost or LAN). Connect payOS from the public domain of your website. When testing on your computer, open the site through a tunnel (for example Cloudflare Tunnel or ngrok) and connect again.', 'payos'), $webhook_url));
									?>
								</p>
							</div>
						<?php endif; ?>
					</div>

					<details class="payos-connect__keys"<?php echo $connected ? '' : ' open'; ?>>
						<summary><?php $connected ? esc_html_e('Change payment channel', 'payos') : esc_html_e('Enter connection details', 'payos'); ?></summary>
						<ol class="payos-connect__steps">
							<li>
								<?php
								/* translators: %s: link my.payos.vn */
								printf(esc_html__('Sign in to %s, open "Payment channels" and create or choose a channel.', 'payos'), '<a href="https://my.payos.vn" target="_blank" rel="noopener">my.payos.vn</a>');
								?>
							</li>
							<li><?php esc_html_e('Copy the Client ID, API Key and Checksum Key of the channel into the fields below.', 'payos'); ?></li>
							<li><?php esc_html_e('Click "Check connection payOS". The status turns to "Connected" when everything is set up.', 'payos'); ?></li>
						</ol>
						<?php foreach ($keys as $key => $field) : ?>
							<div class="payos-connect__field">
								<label for="<?php echo esc_attr($field[1]); ?>"><?php echo esc_html($field[0]); ?></label>
								<div class="payos-connect__copy">
									<input type="password" class="large-text code" autocomplete="off" spellcheck="false" value="<?php echo esc_attr($settings[$key]); ?>" name="payos_gateway_settings[<?php echo esc_attr($key); ?>]" id="<?php echo esc_attr($field[1]); ?>">
									<input type="button" class="button" id="<?php echo esc_attr($field[2]); ?>" onclick="<?php echo esc_attr($field[3]); ?>" value="<?php esc_attr_e('Show', 'payos'); ?>">
								</div>
							</div>
						<?php endforeach; ?>
						<p class="payos-connect__actions">
							<input type="submit" name="payos_check_connection" id="payos_check_connection" class="button button-primary" value="<?php esc_attr_e('Check connection payOS', 'payos'); ?>">
							<a href="https://payos.vn/docs/" target="_blank" rel="noopener"><?php esc_html_e('Instructions to get Client ID and API Key', 'payos'); ?></a>
						</p>
					</details>
				</div>
			</td>
		</tr>
		<?php
	}

	public function payos_checkout_shortcode($attributes)
	{
		// Define your data
		$payos_data = array(
			'message' => __('Please wait...', 'payos'),
			'error_message' => __('Cannot show payment link', 'payos'),
			'icon' =>  PAYOS_GATEWAY_URL . '/assets/img/failed.png',
			'redirect_url' => '',
			'checkout_url' => '',
			'status' => isset($attributes['status']) ? $attributes['status'] : 'DEFAULT',
			'refresh_when_paid' => $this->payos_gateway_settings['refresh_upon_successful_payment']
		);

		// Test if there is a specific status and set the redirect url and message accordingly
		if ($payos_data['status'] === 'PENDING' && isset($attributes['order_id'])) {
			$order = wc_get_order($attributes['order_id']);
			if ($order) {
				$payos_data['redirect_url'] = $order->get_checkout_order_received_url();
				$payos_data['checkout_url'] =  isset($attributes['checkout_url']) ? $attributes['checkout_url'] : '';
				$payos_data['icon'] =  PAYOS_GATEWAY_URL . '/assets/img/success.png';
				$payos_data['message'] = __('Order has been successfully paid.', 'payos');
			}
		} elseif ($payos_data['status'] === 'PAID') {
			$payos_data['icon'] = PAYOS_GATEWAY_URL . '/assets/img/success.png';
			$payos_data['message'] = __('Order has been successfully paid.', 'payos');
			$payos_data['refresh_when_paid'] = 'no';
		} elseif ($payos_data['status'] === 'ERROR') {
			$payos_data['icon'] = PAYOS_GATEWAY_URL . '/assets/img/failed.png';
			$payos_data['message'] = __('Cannot show payment link', 'payos');
		}

		// Register and enqueue the script
		wp_enqueue_style('payos-checkout-styles', PAYOS_GATEWAY_URL . '/assets/css/payos-checkout.css', array(), false, 'all');
		global $wp_styles;
		if (isset($wp_styles->registered['payos-checkout-styles']) && $wp_styles->registered['payos-checkout-styles']->args !== 'all') {
			$wp_styles->registered['payos-checkout-styles']->args = 'all';
		}
		wp_register_script('payos-checkout-script', PAYOS_GATEWAY_URL . '/assets/js/payos-checkout.js', array('jquery'), false, true);
		wp_localize_script('payos-checkout-script', 'payos_checkout_data', $payos_data);
		wp_enqueue_script('payos-checkout-script');

		// Output the shortcode content
		return '<div id="payos-checkout-container"></div>';
	}

	public function use_payment_gateway_template(string $status = null, string $checkout_url = null, $order = null)
	{
		$order_id = $order ? $order->get_id() : null;
		echo do_shortcode('[payos_checkout status="' . $status . '" order_id="' . $order_id . '" checkout_url="' . $checkout_url . '"]');
	}

	/**
	 * Output for the order received page.
	 *
	 * @param int $order_id Order ID.
	 */
	public function payos_thankyou_page($order_id)
	{
		// loading ui
		$this->use_payment_gateway_template();
		$logger = wc_get_logger();
		try {
			$order = wc_get_order($order_id);
			if (!$order) {
				throw new Exception(sprintf('Not found order #%d', $order_id));
			}
			// order not use payOS, do nothing
			if ($this->id !== $order->get_payment_method()) {
				return;
			}

			// return if paid
			if (str_contains($this->payos_gateway_settings["order_status"]["order_status_after_paid"], $order->get_status())) {
				$this->use_payment_gateway_template('PAID');
				return;
			}
		} catch (Exception $e) {
			// if there was an error, log to a file and add an order note
			$logger->error(
				sprintf('Error when get order #%d', $order_id),
				array('source' => 'payos', 'order' => $order_id, 'error' => $e->getMessage())
			);
			$this->use_payment_gateway_template('ERROR');
			return;
		}

		// get checkout url
		try {
			$checkout_url = $this->get_payos_payment_url($order_id);
			$this->use_payment_gateway_template('PENDING', $checkout_url, $order);
		} catch (Exception $e) {
			$logger->error('Error when create payment link', array('source' => 'payos', 'order' => $order_id, 'error' => $e->getMessage()));
			$this->use_payment_gateway_template('ERROR');
			return;
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions($order, $sent_to_admin, $plain_text = false)
	{
		if (!$sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status('on-hold')) {
			if ($this->instructions) {
				echo wp_kses_post(wpautop(wptexturize($this->instructions)) . PHP_EOL);
			}
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment($order_id)
	{
		$order = wc_get_order($order_id);
		if ($order->get_total() > 0) {
			// Mark as on-hold (we're awaiting the payment).
			$order->update_status(apply_filters('woocommerce_payos_process_payment_order_status', 'on-hold', $order), __('Awaiting payment', 'payos'));
		} else {
			$order->payment_complete();
		}

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url($order),
		);
	}

	public function get_payos_payment_url($order_id)
	{
		$order = wc_get_order($order_id);
		$payos_gateway_settings = self::payos_get_gateway_settings();
		if ($order->get_payment_method() !== 'payos') {
			throw new Exception(__('Not use PayOS as payment method', 'payos'));
		}
		if (!isset($payos_gateway_settings['use_payment_gateway']) || $payos_gateway_settings['use_payment_gateway'] !== 'yes') {
			throw new Exception(__('Not use PayOS payment gateway', 'payos'));
		}
		$payment_link_id = $order->get_meta('_wc_order_payos_payment_link_id') ?? null;
		if ($payment_link_id) {
			return self::$PAYOS_CHECKOUT_HOST . '/embedded/' . $payment_link_id;
		}
		try {
			parse_str(sanitize_text_field($_SERVER['QUERY_STRING']), $query_str_arr);
			$query_str_arr = array_map('sanitize_text_field', $query_str_arr);
			if ($query_str_arr && array_key_exists("code", $query_str_arr)) return;

			$order_id = $order->get_id();
			$woo_checkout_url = wc_get_checkout_url();
			if (substr($woo_checkout_url, -1) != '/') {
				$woo_checkout_url = $woo_checkout_url . '/';
			}
			$redirect_url = $order->get_checkout_order_received_url();

			$item_cart = array();
			foreach ($order->get_items() as $item_id => $item) {
				$product_name = $item->get_name();
				$quantity = $item->get_quantity();
				$total = intval($item['total']);
				array_push($item_cart, array(
					'name' => $product_name,
					'quantity' => $quantity,
					'price' => $total
				));
			}
			$data = array(
				"orderCode" => $order->get_id(),
				"description" => sanitize_text_field($this->payos_gateway_settings['transaction_prefix'] . $order->get_id()),
				"amount" => intval($order->get_total()),
				"buyerName" => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				"buyerEmail" => $order->get_billing_email(),
				"items" => $item_cart,
				"returnUrl" => $redirect_url,
				"cancelUrl" => $redirect_url
			);
			// create signature for request data
			$request_data_signature = $this->create_signature_payment_request($data["amount"], $data["cancelUrl"], $data["description"], $data["orderCode"], $data["returnUrl"]);
			$data["signature"] = $request_data_signature;

			$result = $this->handle_fetch_payos(self::$PAYOS_URL_CREATE_PAYMENT_LINK, 'POST', $data);
			$order->update_meta_data('_wc_order_payos_payment_link_id', $result['paymentLinkId']);
			$order->save();
			return str_replace('/web/', '/embedded/', $result['checkoutUrl']);
		} catch (Exception $e) {
			if (intval($e->getCode()) === self::$PAYOS_ORDER_EXIST) {
				$result = $this->handle_fetch_payos(self::$URL_GET_PAYMENT_LINK_INFO . $order->get_id());
				$order->update_meta_data('_wc_order_payos_payment_link_id', $result['id']);
				$order->save();
				return self::$PAYOS_CHECKOUT_HOST . '/embedded/' . $result['id'];
			}
			throw new Exception(esc_html($e->getMessage()), intval($e->getCode()));
		}
	}

	public function payos_order_received_text($text, $order)
	{
		if ($order && $order->get_payment_method() === 'payos') {
			$status = $order->get_status();
			$order_status_after_failed = isset($this->payos_gateway_settings["order_status"]["order_status_after_failed"]) ? $this->payos_gateway_settings["order_status"]["order_status_after_failed"] : '';
			$order_status_after_paid = isset($this->payos_gateway_settings["order_status"]["order_status_after_paid"]) ? $this->payos_gateway_settings["order_status"]["order_status_after_paid"] : '';
			$order_status_after_underpaid = isset($this->payos_gateway_settings["order_status"]["order_status_after_underpaid"]) ? $this->payos_gateway_settings["order_status"]["order_status_after_underpaid"] : '';
			switch ($status) {
				case str_contains($order_status_after_failed, $order->get_status()):
					return esc_html__('Your order has been cancelled.', 'payos');
				case str_contains($order_status_after_underpaid, $order->get_status()):
					return esc_html__('Your order is underpaid.', 'payos');
				case str_contains('wc-completed', $order->get_status()):
				case str_contains('wc-refunded', $order->get_status()):
				case str_contains($order_status_after_paid, $order->get_status()):
					return esc_html__('Thank you. Your order has been fulfilled.', 'payos');
				default:
					return esc_html__('Thank you. Your order is processing.', 'payos');
			}
		}
		return $text;
	}

	public function payos_get_order_statuses_after_paid()
	{
		$wooRemovedStatuses = array(
			'wc-pending',
			// 'wc-processing',
			// 'wc-on-hold',
			// 'wc-completed',
			'wc-underpaid',
			'wc-cancelled',
			'wc-refunded',
			'wc-failed',
		);
		$statuses =  wc_get_order_statuses();
		for ($i = 0; $i < count($wooRemovedStatuses); $i++) {
			$statusName = $wooRemovedStatuses[$i];
			if (isset($statuses[$statusName])) {
				unset($statuses[$statusName]);
			}
		}
		return $statuses;
	}

	public function payos_get_order_statuses_after_underpaid()
	{
		$wooRemovedStatuses = array(
			'wc-pending',
			'wc-processing',
			'wc-on-hold',
			'wc-completed',
			// 'wc-underpaid',
			// 'wc-cancelled',
			// 'wc-refunded',
			// 'wc-failed',
		);
		$statuses =  wc_get_order_statuses();
		for ($i = 0; $i < count($wooRemovedStatuses); $i++) {
			$statusName = $wooRemovedStatuses[$i];
			if (isset($statuses[$statusName])) {
				unset($statuses[$statusName]);
			}
		}
		return $statuses;
	}

	public function payos_get_order_statuses_after_failed()
	{
		$wooRemovedStatuses = array(
			'wc-pending',
			'wc-processing',
			'wc-on-hold',
			'wc-completed',
			'wc-underpaid',
			// 'wc-cancelled',
			// 'wc-refunded',
			// 'wc-failed',
		);
		$statuses =  wc_get_order_statuses();
		for ($i = 0; $i < count($wooRemovedStatuses); $i++) {
			$statusName = $wooRemovedStatuses[$i];
			if (isset($statuses[$statusName])) {
				unset($statuses[$statusName]);
			}
		}
		return $statuses;
	}

	public function payos_verify_payment_handler()
	{
		$logger = wc_get_logger();
		$txtBody = file_get_contents('php://input');
		$body = json_decode(str_replace('\\', '\\\\', $txtBody), true);
		if (!is_array($body) || !isset($body['code'], $body['data']) || !is_array($body['data'])) {
			status_header(400);
			echo wp_json_encode(array('code' => 400, 'message' => 'Invalid webhook body'));
			die();
		}
		$is_test_webhook = ($body['desc'] ?? '') == 'Giao dich thu nghiem' || ($body['data']['description'] ?? '') == 'VQRIO123' || ($body['data']['reference'] ?? '') == 'MA_GIAO_DICH_THU_NGHIEM';
		try {
			if ($body['code'] !== self::$PAYOS_SUCCESS) {
				throw new Exception(strval($body['desc']), intval($body['code']));
			}
			if ($is_test_webhook) {
				echo esc_html__('Webhook delivered successfully', 'payos');
				die();
			}
			$transaction = $body['data'];
			// Chưa có Checksum Key (chưa kết nối hoặc cấu hình bị xoá): ai cũng ký được bằng khoá rỗng, không xử lý
			if ('' === (string) $this->payos_gateway_settings['checksum_key']) {
				throw new Exception(__('payOS has not been set up yet!', 'payos'));
			}
			// --------------Xac thuc du lieu webhook-------------------
			// Chữ ký sai: không đụng tới đơn hàng (trước đây đơn bị chuyển sang thất bại, ai cũng huỷ được đơn)
			$signature = $this->create_signature($transaction);
			if (empty($body['signature']) || !is_string($body['signature']) || !hash_equals($signature, $body['signature'])) {
				throw new Exception(__('Data not integrity', 'payos'));
			}
			// ---------------------------------------------------------
			$order_code = absint($transaction['orderCode'] ?? 0);
			// Hai webhook cùng đơn chạy song song sẽ cùng đọc số tiền và mã giao dịch cũ: chỉ một request xử lý,
			// request kia nhận 409 để bên gửi gửi lại sau
			if (!$this->payos_lock_order($order_code)) {
				status_header(409);
				echo wp_json_encode(array('code' => 409, 'message' => 'Order is being processed'));
				die();
			}
			$order = wc_get_order($order_code);
			if (!$order || $this->id !== $order->get_payment_method()) {
				/* translators: %d: order code */
				throw new Exception(sprintf(__('Error when get order #%d', 'payos'), $order_code));
			}
			$payos_gateway_settings = get_option('payos_gateway_settings');
			// Webhook gửi lặp cùng một giao dịch: không cộng tiền lần nữa
			$reference = (string) ($transaction['reference'] ?? '');
			$processed = array_filter((array) $order->get_meta('_wc_order_payos_references', true));
			if ('' !== $reference && in_array($reference, $processed, true)) {
				echo esc_html__('Webhook delivered successfully', 'payos');
				die();
			}
			// Request này chạy quá TTL và request khác đã lấy khoá: không ghi vào đơn, để bên gửi gửi lại
			if (!$this->payos_renew_order_lock()) {
				status_header(409);
				echo wp_json_encode(array('code' => 409, 'message' => 'Order is being processed'));
				die();
			}
			if ('' !== $reference) {
				$processed[] = $reference;
				$order->update_meta_data('_wc_order_payos_references', array_values($processed));
			}
			// Xu ly du lieu giao dich
			$current_amount_paid = intval($order->get_meta('_wc_order_payos_amount_paid', true)) ?? 0;
			$new_transaction_amount = intval($transaction['amount']);
			$updated_amount_paid = $current_amount_paid + $new_transaction_amount;
			$order->update_meta_data('_wc_order_payos_amount_paid', $updated_amount_paid);

			// Calculate the remaining amount to be paid
			$rest_amount = intval($order->get_total()) - $updated_amount_paid;

			switch (true) {
				case $rest_amount <= 0:
					$order->payment_complete();
					wc_reduce_stock_levels($order->get_id());
					if (isset($payos_gateway_settings['order_status']['order_status_after_paid'])) {
						$order->update_status($payos_gateway_settings['order_status']['order_status_after_paid']);
					}
					if ($rest_amount < 0) {
						$order->add_order_note(__('Order has been overpaid', 'payos'));
					}
					break;

				case $rest_amount > 0:
					if (isset($payos_gateway_settings['order_status']['order_status_after_underpaid'])) {
						$order->update_status($payos_gateway_settings['order_status']['order_status_after_underpaid']);
						$order->add_order_note(__('Order has been underpaid', 'payos'));
					}
					break;

				default:
					break;
			}

			// Save the updated meta data
			$order->save();
			$transaction_note = sprintf(
				/* translators: 1: order code, 2: amount, 3: description, 4: account number, 5: reference */
				__('<b>Transaction Information:</b> <br> Order Code: %1$s <br> Amount: %2$s <br> Description: %3$s <br> Account Number: %4$s <br> Reference: %5$s', 'payos'),
				$transaction['orderCode'],
				$transaction['amount'],
				$transaction['description'],
				$transaction['accountNumber'],
				$transaction['reference']
			);
			$order->add_order_note($transaction_note);
			echo esc_html__('Webhook delivered successfully', 'payos');
			die();
		} catch (Exception $e) {
			$logger->error(wc_print_r($body, true), array('error' => $e->getMessage(), 'source' => 'payos-webhook'));
			$response = array('code' => $e->getCode(), 'message' => $e->getMessage());
			status_header(500);
			echo wp_json_encode($response);
			die();
		}
	}

	/**
	 * Khoá xử lý webhook theo đơn hàng, lưu trong wp_options với giá trị "thời điểm:mã riêng" của request giữ khoá.
	 * INSERT IGNORE là thao tác nguyên tử (add_option thì không: nó dùng ON DUPLICATE KEY UPDATE nên hai request
	 * cùng "thêm" được). Khoá cũ hơn PAYOS_WEBHOOK_LOCK_TTL giây (request chết giữa chừng) được lấy lại bằng UPDATE
	 * có điều kiện. Khi request kết thúc chỉ xoá khoá nếu vẫn là khoá của mình.
	 *
	 * @param int $order_id Order ID.
	 * @return bool true nếu request này giữ khoá.
	 */
	private function payos_lock_order($order_id)
	{
		global $wpdb;
		$name  = 'payos_webhook_lock_' . $order_id;
		$value = time() . ':' . wp_generate_uuid4();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- khoá cần thao tác nguyên tử, không dùng cache
		$locked = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')", $name, $value));
		if (!$locked) {
			$current = (string) $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $name));
			if (time() - (int) $current < self::$PAYOS_WEBHOOK_LOCK_TTL) {
				return false;
			}
			$locked = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", $value, $name, $current));
			if (!$locked) {
				return false;
			}
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery
		$this->payos_webhook_lock = array('name' => $name, 'value' => $value);
		register_shutdown_function(function () {
			$this->payos_unlock_order();
		});
		return true;
	}

	/**
	 * Gia hạn khoá ngay trước khi ghi vào đơn. Trả về false nếu request này chạy quá TTL và request khác
	 * đã lấy khoá: khi đó không được ghi gì vào đơn. Mỗi lần gia hạn dùng mã mới để UPDATE luôn đổi giá trị
	 * (MySQL không đếm dòng có giá trị không đổi).
	 *
	 * @return bool
	 */
	private function payos_renew_order_lock()
	{
		global $wpdb;
		if (!$this->payos_webhook_lock) {
			return false;
		}
		$value = time() . ':' . wp_generate_uuid4();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- so sánh rồi ghi nguyên tử
		$renewed = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", $value, $this->payos_webhook_lock['name'], $this->payos_webhook_lock['value']));
		if (!$renewed) {
			$this->payos_webhook_lock = null;
			return false;
		}
		$this->payos_webhook_lock['value'] = $value;
		return true;
	}

	/**
	 * Xoá khoá khi request kết thúc, chỉ khi khoá vẫn thuộc request này (không xoá khoá của request đã lấy lại).
	 */
	private function payos_unlock_order()
	{
		global $wpdb;
		if (!$this->payos_webhook_lock) {
			return;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- chỉ xoá đúng khoá của mình
		$wpdb->delete($wpdb->options, array('option_name' => $this->payos_webhook_lock['name'], 'option_value' => $this->payos_webhook_lock['value']));
		$this->payos_webhook_lock = null;
	}

	public function payos_payment_page($order_id)
	{
		$order = wc_get_order($order_id);
		if ($this->id === $order->get_payment_method()) {
			$payment_url = $this->get_payos_payment_url($order_id);
			header("Location: {$payment_url}");
			die();
		}
	}

	public function payos_create_webhook($settings)
	{
		$logger = wc_get_logger();
		$response = null;
		$webhook_url = self::get_webhook_url();
		$this->webhook_error = '';
		// payOS phải gọi được webhook qua Internet: báo rõ khi website đang chạy ở địa chỉ nội bộ
		if (self::payos_is_local_url($webhook_url)) {
			/* translators: %s: webhook URL */
			$this->webhook_error = sprintf(__('payOS needs to reach your website over the Internet to send payment notifications, but the webhook URL %s is a local address (localhost or LAN). Connect payOS from the public domain of your website. When testing on your computer, open the site through a tunnel (for example Cloudflare Tunnel or ngrok) and connect again.', 'payos'), $webhook_url);
			$logger->warning($this->webhook_error, array('source' => 'payos-webhook'));
			return null;
		}
		$body = array(
			"webhookUrl" => $webhook_url
		);

		$url  = self::$PAYOS_URL_CONFIRM_WEBHOOK;
		$args = array(
			'body'        => wp_json_encode($body),
			'headers' => array(
				"Content-Type"  => "application/json",
				"x-client-id"   => $settings['client_id'],
				"x-api-key"     => $settings['api_key']
			)
		);
		$response = wp_remote_post($url, $args);

		if (is_wp_error($response)) {
			/* translators: %s: lỗi kết nối */
			$this->webhook_error = sprintf(__('Could not connect to payOS: %s', 'payos'), $response->get_error_message());
			$logger->error(__('Webhook creation request failed: ', 'payos') . $response->get_error_message(), array('source' => 'payos-webhook'));
			return null;
		}

		$body_response = json_decode($response['body'], true);
		if (($response['response']['code'] == 200 || $response['response']['code'] == 201) && isset($body_response['code']) && $body_response['code'] == '00') {
			$logger->info(__('Webhook confirmed', 'payos'), array('source' => 'payos-webhook'));
			$body = wp_remote_retrieve_body($response);
			return $body;
		}
		$code = is_array($body_response) && isset($body_response['code']) ? (string) $body_response['code'] : '';
		$desc = is_array($body_response) && !empty($body_response['desc']) ? (string) $body_response['desc'] : 'HTTP ' . $response['response']['code'];
		if ('20' === $code) {
			/* translators: %s: webhook URL */
			$this->webhook_error = sprintf(__('payOS could not call the webhook URL %s. Make sure your website is reachable from the Internet over HTTPS and that payOS requests are not blocked by a firewall, maintenance mode or a security plugin.', 'payos'), $webhook_url);
		} else {
			/* translators: 1: thông báo lỗi từ payOS, 2: mã lỗi */
			$this->webhook_error = sprintf(__('payOS responded: %1$s (code %2$s). Check the Client ID and API Key of your payment channel.', 'payos'), $desc, '' !== $code ? $code : '-');
		}

		$logger->warning(__('Webhook creation response did not meet success criteria.', 'payos'), array('source' => 'payos-webhook', 'error' => $response));
		return null;
	}

	public function create_signature($data)
	{
		ksort($data);
		$data_str_arr = [];
		foreach ($data as $key => $value) {
			if ($value === "undefined" || $value === "null" || gettype($value) == "NULL") {
				$value = "";
			}
			if (is_array($value)) {
				$value_sorted_ele_obj = array_map(function ($ele) {
					// mảng giá trị đơn (dữ liệu không đúng định dạng): ksort() trên chuỗi gây lỗi Fatal
					if (is_array($ele)) {
						ksort($ele);
					}
					return $ele;
				}, $value);
				$value = wp_json_encode($value_sorted_ele_obj, JSON_UNESCAPED_UNICODE);
			}
			$data_str_arr[] = $key . "=" . $value;
		}
		$data_str = implode('&', $data_str_arr);
		$signature = hash_hmac('sha256', $data_str, $this->payos_gateway_settings['checksum_key']);
		return $signature;
	}

	public function create_signature_payment_request($amount, $cancel_url, $description, $order_code, $return_url)
	{
		$data_str = "amount={$amount}&cancelUrl={$cancel_url}&description={$description}&orderCode={$order_code}&returnUrl={$return_url}";
		$signature = hash_hmac('sha256', $data_str, $this->payos_gateway_settings['checksum_key']);
		return $signature;
	}

	/**
	 * Địa chỉ nội bộ mà payOS không gọi tới được: localhost, *.localhost, *.local, *.test, IP mạng LAN.
	 */
	public static function payos_is_local_url($url)
	{
		$host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
		$host = trim($host, '[]');
		if ('' === $host || 'localhost' === $host || preg_match('/\.(localhost|local|test|internal|lan|home\.arpa)$/', $host)) {
			return true;
		}
		if (filter_var($host, FILTER_VALIDATE_IP)) {
			return false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
		}
		return false;
	}

	static function get_webhook_url()
	{
		return WC()->api_request_url(self::$PAYOS_WEBHOOK_ENDPOINT);
	}

	public function handle_fetch_payos(string $url, string $method = 'GET', $data = null)
	{
		$client_id = $this->payos_gateway_settings['client_id'];
		$api_key = $this->payos_gateway_settings['api_key'];
		$args = [
			'method'    => $method,
			'headers'   => [
				'Content-Type' => 'application/json',
				'x-client-id'  => $client_id,
				'x-api-key'    => $api_key,
			],
			'body'      => $data ? wp_json_encode($data) : '',
			'timeout'   => 45,
		];

		$response = wp_remote_request($url, $args);

		if (is_wp_error($response)) {
			throw new Exception(esc_html($response->get_error_message()));
		}

		$body = wp_remote_retrieve_body($response);
		$result = json_decode($body, true);

		if ($result['code'] !== self::$PAYOS_SUCCESS) {
			throw new Exception(esc_html($result['desc']), intval($result['code']));
		}

		// Check data integrity by verifying the signature
		$checkout_response = $result['data'];
		$checkout_response_signature = $this->create_signature($checkout_response);

		if ($checkout_response_signature !== $result['signature']) { // Data integrity check failed
			throw new Exception(__('Data not integrity', 'payos'));
		}

		return $checkout_response;
	}
}
