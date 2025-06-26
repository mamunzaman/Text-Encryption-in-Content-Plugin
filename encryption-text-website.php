<?php
/**
 * Plugin Name: Encrypt/Decrypt texts in content Shortcode
 * Description: Outputs encoded text, email and phone to hide from bots using a shortcode. HTML not allowed
 * Version: 1.1
 * Author: Md mamunuzzaman
 * Text Domain: mm-encrypted-content
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path(__FILE__) . 'inc/class-encrypt-text.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-dedicated-asset-manager.php';
require_once plugin_dir_path(__FILE__) . 'inc/class-fontawesome-handler.php';

class MmSecureEncryptedTextPlugin {

    private $encrypt_text;
    private $fa_handler;

    public function __construct() {
        $this->encrypt_text = new MmEncryptText();
        $this->fa_handler = new MmFontAwesomeHandler();
    }
}

new MmSecureEncryptedTextPlugin();