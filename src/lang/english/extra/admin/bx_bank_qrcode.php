<?php
/* -----------------------------------------------------------------------------------------
	$Id: /lang/english/extra/admin/bx_bank_qrcode.php 1000 2026-03-04 12:00:00Z benax $
	
	modified eCommerce Shopsoftware
	http://www.modified-shop.org
	
	Copyright (c) 2009 - 2013 [www.modified-shop.org]
	-----------------------------------------------------------------------------------------
	Released under the GNU General Public License
	---------------------------------------------------------------------------------------*/
	
  define('MODULE_BANK_QRCODE_IBAN_HEADING', 'IBAN in QR Code');
  define('MODULE_BANK_QRCODE_IBAN_TEXT', 'Display IBAN in QR Code (if available)');
  define('MODULE_BANK_QRCODE_BIC_HEADING', 'BIC in QR Code');
  define('MODULE_BANK_QRCODE_BIC_TEXT', 'Display BIC in QR Code (if available)');
  define('MODULE_BANK_QRCODE_RECIPIENT_HEADING', 'Recipient Name in QR Code');
  define('MODULE_BANK_QRCODE_RECIPIENT_TEXT', 'Display recipient name in QR Code (if available)');

	define('MODULE_BANK_QRCODE_PAGE_TITLE', 'Bank App QRCode');
	define('MODULE_BANK_QRCODE_PAGE_SUBTITLE', 'Transfer by code: customers can pay quickly with their banking app.');
	define('MODULE_BANK_QRCODE_BANNER_TITLE', '🚀 Bank App QRCode v'.(defined('MODULE_BX_BANK_QRCODE_VERSION') ? MODULE_BX_BANK_QRCODE_VERSION : ''));
	define('MODULE_BANK_QRCODE_BANNER_SUBTITLE', 'Professional solution for QR code based bank transfers');

	define('MODULE_BANK_QRCODE_SECURITY_HEADING', '🔐 Bank Data Security');
	define('MODULE_BANK_QRCODE_SECURITY_STORAGE_HEADING', 'Encrypted Storage');
	define('MODULE_BANK_QRCODE_SECURITY_STORAGE_TEXT', 'IBAN, BIC and recipient name are never stored in plain text and are saved encrypted in the database.');
	define('MODULE_BANK_QRCODE_SECURITY_INTEGRITY_HEADING', 'Tamper Protection');
	define('MODULE_BANK_QRCODE_SECURITY_INTEGRITY_TEXT', 'Each stored value includes an integrity check (HMAC) so unauthorized modifications can be detected.');
	define('MODULE_BANK_QRCODE_SECURITY_DECRYPT_HEADING', 'Decryption on Demand');
	define('MODULE_BANK_QRCODE_SECURITY_DECRYPT_TEXT', 'Data is decrypted only for admin display and QR code generation.');
	define('MODULE_BANK_QRCODE_SECURITY_KEY_HEADING', 'Key Management');
	define('MODULE_BANK_QRCODE_SECURITY_KEY_TEXT', 'The key is read primarily from BX_BANK_QRCODE_CRYPTO_KEY (includes/extra/configure/bx_bank_qrcode.php); PASSWORD_HMAC is fallback only.');
	define('MODULE_BANK_QRCODE_SECURITY_ACCESS_HEADING', 'Access and Transport');
	define('MODULE_BANK_QRCODE_SECURITY_ACCESS_TEXT', 'Access is limited to authorized admin users; connections should be protected with HTTPS/TLS at all times.');
	define('MODULE_BANK_QRCODE_SECURITY_TECH_HEADING', 'Technical (Admin/GDPR)');
	define('MODULE_BANK_QRCODE_SECURITY_TECH_TEXT', 'Method: AES-256-CBC with separate integrity verification (HMAC-SHA256), storage format: bxenc:v1:...; decryption only for display and QR creation.');
	define('MODULE_BANK_QRCODE_SECURITY_DOC_HEADING', 'Documentation Note');
	define('MODULE_BANK_QRCODE_SECURITY_DOC_TEXT', 'For records of processing activities: purpose "Bank data for EPC-QR", data categories "IBAN/BIC/Recipient", TOMs "Encryption, access control, TLS, logging", retention according to accounting requirements.');

	define('TEXT_SAVE', 'Save Settings');
  