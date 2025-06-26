<?php
if (!defined('ABSPATH')) exit;

class MmEncryptText {

    public function __construct() {
        add_shortcode('mm_encrypted_content_email', [$this, 'render_mm_encrypted_content_email']);
        add_shortcode('mm_encrypted_content_phone', [$this, 'render_mm_encrypted_content_phone']);

        add_action('wp_footer', [$this, 'output_script'], 100);
        add_action('plugins_loaded', [$this, 'load_textdomain'], 100);
    }

    public function load_textdomain() {
        load_plugin_textdomain('mm-encrypted-content', false, dirname(plugin_basename(__FILE__)) . '/../languages');
    }

    private function encode_to_js_array($string) {
        return json_encode(array_map('ord', str_split($string)));
    }

    public function render_mm_encrypted_content_email($atts) {
        static $id_counter = 0;
        $id_counter++;

        $atts = shortcode_atts([
            'email' => '',
            'before' => '',
            'after' => '',
            'icon' => '',
            'text_before' => '',
        ], $atts, 'mm_encrypted_content_email');
        $icon_text= '';
        $text_before = '';
        $output = '';
        //$output = '<div class="mm-content-encryption">';
        if(!empty($atts['icon'])){
            $icon_symbol = $atts['icon'];
            $icon_text= '<span class="mm-encrypted-text-icon"><i class="fa-solid '.$icon_symbol.'"></i></span>';
        }elseif(!empty($atts['text_before'])){
            $text_before = $atts['text_before'];
            $icon_text= '';
        }

        if (!empty($atts['email'])) {
            $email_id = 'mm-content-encryption-email-' . $id_counter;
            $email_array = $this->encode_to_js_array($atts['email']);


            //$atts['text_before']
            $output .= "{$text_before}{$icon_text} <span id='{$email_id}' data-type='email'
            data-encoded='{$email_array}'></span>";
        }

        /*if (!empty($atts['phone'])) {
            $phone_id = 'mm-content-encryption-phone-' . $id_counter;
            $phone_array = $this->encode_to_js_array($atts['phone']);
            $output .= "<p>" . __('Phone:', 'mm-encrypted-content') . " <span id='{$phone_id}' data-type='phone' data-encoded='{$phone_array}'></span></p>";
        }*/

        //$output .= '</div>';

        return $output;
    }


public function render_mm_encrypted_content_phone($atts) {
        static $id_counter = 0;
        $id_counter++;

        $atts = shortcode_atts([
            'phone' => '',
            'before' => '',
            'after' => '',
            'icon' => '',
            'text_before' => '',
        ], $atts, 'mm_encrypted_content_phone');
        $icon_text= '';
        $text_before = '';
        $output = '';
        //$output = '<div class="mm-content-encryption">';
        if(!empty($atts['icon'])){
            $icon_symbol = $atts['icon'];
            $icon_text= '<span class="mm-encrypted-text-icon"><i class="fa-solid '.$icon_symbol.'"></i></span>';
        }elseif(!empty($atts['text_before'])){
            $text_before = $atts['text_before'];
            $icon_text= '';
        }

        if (!empty($atts['phone'])) {
            $email_id = 'mm-content-encryption-phone-' . $id_counter;
            $email_array = $this->encode_to_js_array($atts['phone']);


            //$atts['text_before']
            $output .= "{$text_before}{$icon_text} <span id='{$email_id}' data-type='phone'
            data-encoded='{$email_array}'></span>";
        }

        /*if (!empty($atts['phone'])) {
            $phone_id = 'mm-content-encryption-phone-' . $id_counter;
            $phone_array = $this->encode_to_js_array($atts['phone']);
            $output .= "<p>" . __('Phone:', 'mm-encrypted-content') . " <span id='{$phone_id}' data-type='phone' data-encoded='{$phone_array}'></span></p>";
        }*/

        //$output .= '</div>';

        return $output;
    }

    public function output_script() {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('span[data-encoded]').forEach(function (el) {
                const encoded = JSON.parse(el.getAttribute('data-encoded'));
                const type = el.getAttribute('data-type');
                const decoded = encoded.map(c => String.fromCharCode(c)).join('');
                if (type === 'email') {
                    el.innerHTML = `<a href="mailto:${decoded}">${decoded}</a>`;
                } else if (type === 'phone') {
                    el.innerHTML = `<a href="tel:${decoded}">${decoded}</a>`;
                }
            });
        });
        </script>
        <?php
    }
}
