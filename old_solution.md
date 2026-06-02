# Letter System - File Paths

## Lưu trữ (User → DB)

| Vai trò | Path |
|---------|------|
| FE viết thư (nhập nội dung, xử lý overflow/emoji) | `/Users/lehoa/icetea/its/Dongil/application/views/letter/write.php` |
| API lưu thư vào DB (`setWrite`, line 616) | `/Users/lehoa/icetea/its/Dongil/application/controllers/UserApi.php` |

## Render để print (Admin)

| Vai trò | Path |
|---------|------|
| Controller lấy data từ DB (`getPostInfo()`, line 785) | `/Users/lehoa/icetea/its/Dongil/application/controllers/Admin.php` |
| View in 1 thư | `/Users/lehoa/icetea/its/Dongil/application/views/admin/postView.php` |
| View in hàng loạt | `/Users/lehoa/icetea/its/Dongil/application/views/admin/allPrintingPost.php` |
| View in ảnh | `/Users/lehoa/icetea/its/Dongil/application/views/admin/postImgView.php` |
| View in hàng loạt ảnh | `/Users/lehoa/icetea/its/Dongil/application/views/admin/allPrintingImage.php` |
| View in phong bì | `/Users/lehoa/icetea/its/Dongil/application/views/admin/signView.php` |
| View in hàng loạt phong bì | `/Users/lehoa/icetea/its/Dongil/application/views/admin/allPrintingSign.php` |

## Helper & CSS

| Vai trò | Path |
|---------|------|
| Set style cho letter (`setLetterInfo2()` line 534, `setLetterInfo3()` line 574) | `/Users/lehoa/icetea/its/Dongil/application/helpers/common_helper.php` |
| CSS user (viết thư) — `#letter .letterContent` line 615 | `/Users/lehoa/icetea/its/Dongil/assets/css/common.css` |
| CSS admin (print) — `#postView .letterContent` line 500, `.letterContent.tema` line 476 | `/Users/lehoa/icetea/its/Dongil/assets/css/admin.css` |
| CSS print chung — `#postView .letterContent` line 1871 | `/Users/lehoa/icetea/its/Dongil/assets/css/common.css` |
| Tính vị trí cursor (xử lý overflow) | `/Users/lehoa/icetea/its/Dongil/assets/js/textarea-caret-position.js` |
