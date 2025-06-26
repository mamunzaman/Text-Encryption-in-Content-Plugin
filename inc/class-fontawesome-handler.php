<?php
if (!defined('ABSPATH')) exit;

class MmFontAwesomeHandler {

    private $fa_folder;
    private $fa_css_file;
    private $fa_version = '6.5.0';

    public function __construct() {
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_post_mm_fa_download', [$this, 'handle_fa_download']);
        add_action('admin_notices', [$this, 'maybe_show_notice']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_local_font_awesome'], 90);
    }

    public function register_settings_page() {
        add_options_page(
            __('Encrypted Content Settings', 'mm-encrypted-content'),
            __('Encrypted Content', 'mm-encrypted-content'),
            'manage_options',
            'mm-encrypted-content-settings',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        $upload = wp_upload_dir();
        $fa_css_file = trailingslashit($upload['basedir']) . 'font-awesome-local/css/all.min.css';
        $fa_installed = file_exists($fa_css_file);

        ?>
        <div class="wrap">
            <h1><?php _e('Encrypted Content Settings', 'mm-encrypted-content'); ?></h1>

            <?php if ($fa_installed): ?>
                <p><strong style="color:green;"><?php _e('Font Awesome is already downloaded and ready to use.', 'mm-encrypted-content'); ?></strong></p>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="mm_fa_download">
                <?php
                submit_button(
                    __('Download Font Awesome Locally', 'mm-encrypted-content'),
                    'primary',
                    'submit',
                    false,
                    $fa_installed ? ['disabled' => 'disabled', 'style' => 'opacity:0.5;cursor:not-allowed;'] : []
                );
                ?>
            </form>
        </div>
        <?php
    }

    public function handle_fa_download() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'mm-encrypted-content'));
        }

        $upload = wp_upload_dir();
        $this->fa_folder = trailingslashit($upload['basedir']) . 'font-awesome-local/';
        $this->fa_css_file = $this->fa_folder . 'css/all.min.css';

        $success = false;

        if (!file_exists($this->fa_css_file)) {
            $success = $this->download_font_awesome();
        } else {
            $success = true;
        }

        $flag = $success ? '1' : '0';
        wp_redirect(admin_url("options-general.php?page=mm-encrypted-content-settings&downloaded={$flag}"));
        exit;
    }

    private function download_font_awesome() {
        $zip_url = "https://use.fontawesome.com/releases/v{$this->fa_version}/fontawesome-free-{$this->fa_version}-web.zip";
        $tmp_file = download_url($zip_url);

        if (is_wp_error($tmp_file)) {
            error_log('[FA Download] Download failed: ' . $tmp_file->get_error_message());
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
        global $wp_filesystem;

        $upload_dir = wp_upload_dir();
        $this->fa_folder = trailingslashit($upload_dir['basedir']) . 'font-awesome-local/';

        // ✅ Create target folder
        if (!file_exists($this->fa_folder)) {
            if (!wp_mkdir_p($this->fa_folder)) {
                error_log('[FA Download] Failed to create folder: ' . $this->fa_folder);
                unlink($tmp_file);
                return false;
            }
        }

        // ✅ Unzip downloaded ZIP into the target folder
        $unzip_result = unzip_file($tmp_file, $this->fa_folder);
        unlink($tmp_file); // Always remove temporary ZIP

        if (is_wp_error($unzip_result)) {
            error_log('[FA Download] Unzip failed: ' . $unzip_result->get_error_message());
            return false;
        }

        // ✅ Check extracted folder and move its contents
        $unzipped_subfolder = $this->fa_folder . "fontawesome-free-{$this->fa_version}-web/";
        if (!file_exists($unzipped_subfolder)) {
            error_log('[FA Download] Expected unzipped folder not found: ' . $unzipped_subfolder);
            return false;
        }

        $copy_result = copy_dir($unzipped_subfolder, $this->fa_folder);
        if (is_wp_error($copy_result)) {
            error_log('[FA Download] Copy failed: ' . $copy_result->get_error_message());
            return false;
        }

        // Clean up original unzipped subfolder after copy
        $wp_filesystem->delete($unzipped_subfolder, true);

        // ✅ Final path to CSS file
        $this->fa_css_file = $this->fa_folder . 'css/all.min.css';

        if (file_exists($this->fa_css_file)) {
            error_log('[FA Download] Font Awesome downloaded successfully. CSS file: ' . $this->fa_css_file);
            return true;
        } else {
            error_log('[FA Download] CSS file not found: ' . $this->fa_css_file);
            return false;
        }
    }


    public function maybe_show_notice() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'mm-encrypted-content-settings') return;
        if (!isset($_GET['downloaded'])) return;

        if ($_GET['downloaded'] == '1') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Font Awesome was downloaded and installed successfully.', 'mm-encrypted-content') . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Font Awesome download failed. Check permissions or internet access.', 'mm-encrypted-content') . '</p></div>';
        }
    }

    public function enqueue_local_font_awesome() {
        global $wp_styles;
         /*echo '<pre>';
                print_r($wp_styles);
                   echo '</pre>';*/
        $found = false;
        if (!empty($wp_styles->registered)) {
            foreach ($wp_styles->registered as $style) {
                if (strpos($style->src, 'font-awesome') !== false || strpos($style->src, 'fontawesome') !== false) {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) return;

        $upload = wp_upload_dir();
        $url = trailingslashit($upload['baseurl']) . 'font-awesome-local/css/all.min.css';
        $file = trailingslashit($upload['basedir']) . 'font-awesome-local/css/all.min.css';

        if (file_exists($file)) {
            wp_enqueue_style('mm-fa-local', $url, [], $this->fa_version);
        }
    }
}
