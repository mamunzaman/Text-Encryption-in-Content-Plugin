<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MmDedicatedAssetsManager {

public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'conditionally_enqueue_encrypted_assets'], 90);
    }
public function conditionally_enqueue_encrypted_assets() {
    wp_enqueue_style('mm-encrypted-text', plugins_url( '../assets/css/mm-encrypted-css.css', __FILE__ ));
}

}

new MmDedicatedAssetsManager();