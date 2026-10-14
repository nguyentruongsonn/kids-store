=== payOS ===
Contributors: diepmagik, locbt, hungndcasso
Tags: vietqr, woocommerce, payment gateway, cong thanh toan, deeplink
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.77
Version: 1.0.77
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Cổng thanh toán VietQR miễn phí cho WooCommerce: mã QR riêng cho từng đơn, tiền về thẳng tài khoản ngân hàng, đơn tự xác nhận.

== Description ==

= payOS: tiền về tài khoản, đơn tự xác nhận =

**Mỗi đơn hàng một mã VietQR riêng. Khách quét và chuyển khoản, payOS báo về website ngay khi tiền vào tài khoản: đơn hàng tự chuyển sang "đã thanh toán", không cần mở app ngân hàng kiểm tra từng giao dịch.**

[payOS](https://payos.vn) là cổng thanh toán **miễn phí** do Casso phát triển, dùng chính tài khoản ngân hàng của bạn để nhận tiền và xác nhận thanh toán. Không qua trung gian giữ tiền, không phí cổng thanh toán: tiền khách chuyển về **thẳng tài khoản ngân hàng của bạn**.

= Vì sao chủ shop chọn payOS =

* **Miễn phí** – không mất phí cổng thanh toán, tiết kiệm đến 99% chi phí so với cổng thanh toán truyền thống.
* **Đối soát chính xác 100%** – mỗi đơn có mã QR riêng với đúng số tiền và nội dung, payOS khớp giao dịch với đơn hàng tự động.
* **Giao hàng sớm hơn** – đơn được xác nhận vài giây sau khi tiền về, kể cả ban đêm và cuối tuần.
* **Tiền về thẳng tài khoản** – không qua ví trung gian, không chờ đối soát, rút tiền.
* **Đăng ký 5 phút** – cá nhân, hộ kinh doanh, doanh nghiệp đều dùng được, chỉ cần CCCD, không cần hợp đồng hay giấy phép kinh doanh.
* **An toàn** – dữ liệu webhook được ký bằng Checksum Key, hạ tầng trên Google Cloud, đáp ứng các tiêu chuẩn ISO/IEC, SOC 2.

= Khách hàng thanh toán thế nào? =

1. Khách chọn "Thanh toán VietQR qua cổng payOS" và đặt hàng.
2. Trang đặt hàng thành công hiện ngay trang thanh toán payOS: mã VietQR của đơn hàng, thông tin chuyển khoản có nút sao chép.
3. Trên điện thoại, khách bấm vào logo ngân hàng để mở app với số tài khoản, số tiền, nội dung đã điền sẵn (các app có tag **Tự động điền**); trên máy tính, khách quét mã QR bằng bất kỳ app ngân hàng hay ví điện tử nào hỗ trợ VietQR.
4. Tiền về tài khoản, payOS báo cho website, đơn hàng tự chuyển trạng thái và trang thanh toán báo "Đơn hàng đã thanh toán thành công".

= Tính năng =

**Thanh toán**

* Mã VietQR riêng cho từng đơn hàng, đúng số tiền và nội dung chuyển khoản
* Trang thanh toán payOS nhúng ngay trong trang đặt hàng thành công, khách không phải rời website
* Hai cách thanh toán: quét mã QR hoặc chuyển khoản theo thông tin (sao chép từng dòng), tải ảnh QR về máy
* Trên điện thoại: nút mở app ngân hàng, app có tag "Tự động điền" mở ra với giao dịch đã điền sẵn
* Hỗ trợ cả checkout cổ điển và WooCommerce Checkout Block

**Tự động xác nhận**

* Webhook payOS cập nhật trạng thái đơn ngay khi tiền về, chữ ký được kiểm tra trước khi xử lý
* Khách chuyển thiếu: đơn chuyển sang "Thanh toán thiếu"; khách chuyển bù đủ số tiền, đơn được hoàn tất
* Khách chuyển dư: đơn vẫn hoàn tất, kèm ghi chú
* Mỗi giao dịch được ghi vào ghi chú đơn hàng: số tiền, nội dung, mã tham chiếu ngân hàng
* Tuỳ chọn trạng thái đơn khi thanh toán thành công, thanh toán thiếu, thất bại
* Tuỳ chọn tự tải lại trang đặt hàng khi thanh toán thành công

**Cài đặt**

* Kết nối bằng Client ID, API Key, Checksum Key của kênh thanh toán payOS; plugin tự đăng ký webhook
* Tiền tố nội dung chuyển khoản tuỳ chỉnh (ví dụ `DH1024`)
* Hỗ trợ dịch, có sẵn tiếng Việt

== Installation ==

1. Đăng ký tài khoản tại [my.payos.vn](https://my.payos.vn), xác thực bằng CCCD và liên kết tài khoản ngân hàng nhận tiền.
2. Tạo kênh thanh toán trên payOS, lấy **Client ID**, **API Key** và **Checksum Key**.
3. Cài và kích hoạt plugin, vào WooCommerce > Cài đặt > Thanh toán > payOS.
4. Bấm "Nhập thông tin của payOS", dán 3 thông tin trên và bấm "Kiểm tra kết nối payOS". Khi thấy "Đã kết nối", plugin đã đăng ký webhook để nhận thông báo thanh toán.
5. Chọn tiền tố nội dung chuyển khoản và trạng thái đơn hàng sau khi thanh toán, rồi lưu.

Hướng dẫn chi tiết: [payos.vn/docs](https://payos.vn/docs/)

== Frequently Asked Questions ==

= payOS có miễn phí không? =

Plugin miễn phí. payOS là cổng thanh toán miễn phí, không thu phí trên từng giao dịch như cổng thanh toán truyền thống; xem chi tiết các gói tại [payos.vn](https://payos.vn).

= Tiền khách thanh toán về đâu? =

Về thẳng tài khoản ngân hàng bạn đã liên kết với payOS. payOS không giữ tiền của bạn.

= Cá nhân chưa có giấy phép kinh doanh có dùng được không? =

Có. Cá nhân đăng ký và xác thực payOS bằng CCCD.

= Khách dùng ngân hàng nào để thanh toán? =

Khách dùng bất kỳ app ngân hàng hay ví điện tử nào hỗ trợ quét mã VietQR (hầu hết ngân hàng tại Việt Nam). Trên điện thoại, nhiều app như MB Bank, VietinBank iPay, BIDV SmartBanking, ACB ONE, OCB OMNI mở ra với thông tin đã điền sẵn.

= Có môi trường thử nghiệm (sandbox) không? =

payOS chưa có sandbox riêng. Bạn có thể dùng tài khoản cá nhân và thanh toán thử đơn hàng giá trị nhỏ để kiểm tra toàn bộ luồng. Xem [môi trường test payOS](https://payos.vn/docs/moi-truong-test).

= Vì sao đơn hàng không tự xác nhận? =

Kiểm tra trạng thái "Đã kết nối" trong cài đặt payOS và bấm "Kiểm tra kết nối payOS" để đăng ký lại webhook. Website phải truy cập được từ Internet qua HTTPS (không dùng được ở localhost). Nội dung chuyển khoản phải giữ nguyên như trên mã QR.

= Số tài khoản trên trang thanh toán khác số tài khoản của tôi? =

Với tài khoản liên kết qua VietQR Pro, payOS hiển thị số tài khoản ảo dành riêng cho giao dịch; tiền vẫn về tài khoản ngân hàng của bạn.

== External services ==

Plugin dùng dịch vụ cổng thanh toán payOS (https://payos.vn) để tạo mã thanh toán và nhận thông báo giao dịch:

* `api-merchant.payos.vn` – tạo link thanh toán cho từng đơn hàng, lấy thông tin link thanh toán, đăng ký webhook. Dữ liệu gửi đi: mã đơn hàng, số tiền, nội dung chuyển khoản, tên và email người mua, danh sách sản phẩm (tên, số lượng, giá), địa chỉ trang đặt hàng thành công, địa chỉ webhook; Client ID và API Key để xác thực.
* `pay.payos.vn` – trang thanh toán (mã QR, thông tin chuyển khoản) hiển thị trong trang đặt hàng thành công.
* payOS gửi về webhook của website thông tin giao dịch đã nhận (mã đơn, số tiền, nội dung, số tài khoản, mã tham chiếu).

Khi dùng plugin, bạn đồng ý với [Thoả thuận sử dụng dịch vụ](https://payos.vn/thoa-thuan-su-dung/) và [Chính sách bảo mật](https://payos.vn/privacy-policy/) của payOS.

== Screenshots ==

1. Khách chọn "Thanh toán VietQR qua cổng payOS" ở bước thanh toán
2. Trên điện thoại: mã VietQR riêng của đơn hàng và nút mở app ngân hàng, app có tag "Tự động điền" mở ra với giao dịch đã điền sẵn
3. Trên máy tính: trang đặt hàng thành công hiện ngay mã QR để thanh toán
4. Tab "Chuyển khoản": số tài khoản, số tiền, nội dung kèm nút sao chép
5. Tiền về, trang báo "Đơn hàng đã thanh toán thành công"
6. Ghi chú đơn hàng: khách chuyển thiếu, rồi chuyển bù; đơn tự chuyển sang trạng thái đã thanh toán
7. Trang cài đặt: kết nối kênh thanh toán payOS, tiền tố nội dung, trạng thái đơn hàng

== Changelog ==

= 1.0.77 =
* Tăng cường bảo mật khi xử lý thông báo thanh toán
* Không xử lý trùng khi có nhiều thông báo thanh toán cùng lúc cho một đơn hàng
* Kiểm tra chặt hơn các trường trên trang cài đặt; tiền tố không được bắt đầu bằng FT, TF, TT, VQR

= 1.0.76 =
* Tăng cường bảo mật khi lưu cài đặt và xử lý thông báo thanh toán
* Trang cài đặt mới: trạng thái kết nối, thông tin kênh thanh toán và webhook URL hiển thị sẵn, hướng dẫn kết nối từng bước
* Báo rõ lý do khi kết nối payOS chưa thành công
* Trang quản trị nhẹ hơn

= 1.0.75 =
* Trang plugin mới bằng tiếng Việt: icon, banner, ảnh chụp màn hình
* Sửa link hướng dẫn lấy Client ID, API Key trong trang cài đặt
* Kiểm tra tương thích WordPress 7.1

= 1.0.74 =
* Tên mặc định mới "Thanh toán VietQR qua cổng payOS". Cửa hàng đang dùng tên mặc định cũ được tự cập nhật, tên tự đặt giữ nguyên
* Logo payOS lớn hơn ở trang thanh toán
* Bỏ ảnh thừa trong gói plugin

= 1.0.7 =
* Cập nhật hình ảnh

= 1.0.6 =
* Thêm tuỳ chọn tự tải lại trang khi thanh toán thành công

= 1.0.5 =
* Thêm trạng thái đơn hàng "Thanh toán thiếu"

= 1.0.4 =
* Sửa đường dẫn hình ảnh

= 1.0.3 =
* Sửa lỗi nhỏ, cập nhật bản dịch tiếng Việt

= 1.0.2 =
* Sửa lỗi nhỏ

= 1.0.1 =
* Bỏ bớt kiểm tra trạng thái đơn hàng không cần thiết khi tạo mã QR

= 1.0.0 =
* Phát hành lần đầu
