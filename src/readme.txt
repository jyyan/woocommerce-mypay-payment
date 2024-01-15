﻿=== MYPay Payment for WooCommerce ===
Contributors: mypaytechsupport
Tags: ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, payment, mypay
Requires at least: wordpress 5.3, WooCommerce 3.5
Tested up to: wordpress 5.5.1, WooCommerce 4.6.1
Requires PHP: 7.2 or later
Stable tag: 1.2.20240115
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

高鉅科技金流外掛套件，提供合作特店以及個人會員使用開放原始碼商店系統時，無須自行處理複雜的檢核，直接透過安裝設定外掛套件，便可以較快速的方式介接高鉅科技的金流系統。

== Description ==

高鉅科技金流外掛套件，提供合作特店以及個人會員使用開放原始碼商店系統時，無須自行處理複雜的檢核，直接透過安裝設定外掛套件，便可以較快速的方式介接高鉅科技的金流系統。

= 全方位金流 =
- 信用卡(一次付清、分期付款、定期定額)、ATM櫃員機、網路ATM、四大超商代碼/條碼，免註冊即可使用。
- 無需和個別銀行申請信用卡刷卡服務，只要註冊高鉅科技即可享有多種收款方式。
- 可依需求設定要顯示給消費者單一或多種金流。

= 方便整合高鉅科技物流服務、高鉅科技電子發票服務 =
- 凡具備高鉅科技會員資格即可免費申請。
- 全台7-ELEVEN、全家、萊爾富通路 皆可使用超商寄/取貨付款服務。
- 提供黑貓/大嘴鳥宅配服務(無貨到付款)
- 24H隨時查詢電子發票明細
- 提供電子發票管理及明細下載

= 更安全的付款方式 =
以簡單、安全且保障隱私的方式付款，符合國際PCI DSS 認證，保護每一個持卡人的交易安全，執行嚴謹的網路軟體硬體防護措施，加倍安心。

= 收款方式清單 =
- 信用卡(一次付清、分期付款、定期定額)
- 網路ATM
- ATM櫃員機
- 超商代碼
- 超商條碼
- Apple Pay

= 注意事項 =
- 1.若須同時使用高鉅科技WooCommerce物流模組，除了更新高鉅科技WooCommerce金流模組外，高鉅科技WooCommerce物流模組也請同步更新才能正常使用。
- 2.本模組訂單扣庫存數量是在付款完成後才進行扣除，所以如果付款方式為非即時完成，例如：超商代碼、ATM，庫存會於消費者實際繳款後才扣除。限量商品請避免使用非即時金流收款。

= 聯絡我們 =
  高鉅技術客服信箱: techsupport@mypay.com.tw


== Installation ==

= 系統需求 =

- PHP version 7.2 or greater, php_openssl module
- MySQL version 5.5 or greater


= 自動安裝 =
1. 登入至您的 WordPress dashboard，拜訪 "Plugins menu" 並點擊 "Add"。
2. 在"search field"中輸入"MYPay Payment for WooCommerce"，然後點擊搜尋。
3. 點擊 "安裝" 即可進行安裝。

= 手動安裝 =
詳細說明請參閱 [高鉅科技金流外掛套件安裝導引文件](https://github.com/MYPay/WooCommerce_Payment)。

== Frequently Asked Questions ==

== Changelog ==

v1.1.0901
Official release

v1.1.0911
電子發票開立備註欄增加信用卡卡號後4碼,需搭配https://github.com/MYPay/WooCommerce_Invoice V1.1.0911使用

v1.1.1115
定期定額後台功能 新增 「新增/刪除功能」

v1.1.1124
金流優化 ,ATM ＆CVS 取號結果通知，同步到使用者會員中心訂單中

v1.1.1201
調整信用卡定期定額設定


v1.1.180313
1.調整金流成功交易，無法返回感謝頁，造成GA無法偵測問題，
2.調整get_return_url 問題。

v1.2.20201023
1. FIX: php7.x 金流 API 加密方式問題
2. 必要模組 php_openssl 模組

v1.2.20201114
1. FIX: 支援：新版本測試區，於測試模式使用信用卡測試交易時 UserID 限制
2. UPDATE: wordpress / php 版本需求顯示
3. ADD: 顯示『非即時交易、即時交易』 webhook API notify URL

v1.2.20201115
1. UPDATE: DOC
2. FIX: package path typo

v1.2.20210102
1. FIX: 運費、折扣 issue

v1.2.20210305
1. ADD: 『後付款』支付方式
2. UPDATE: PO/MO 語系檔

v1.2.20220321
1. FIX: 於 test mode 模式中，MyPay API 不再支援 DoSuccess 操作

v1.2.20240115.2
1. FIX: 重新計算折扣金額、其他交易費用
1. FIX: 單選部份 PFN 無法正確完成交易問題
1. ADD: 金流交易資訊記錄於訂單備註資訊中
