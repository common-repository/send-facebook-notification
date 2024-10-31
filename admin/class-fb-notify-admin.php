<?php
session_start();
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       apurba.me
 * @since      1.0.0
 *
 * @package    Fb_Notify
 * @subpackage Fb_Notify/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fb_Notify
 * @subpackage Fb_Notify/admin
 * @author     Apurba <hello@apurba.me>
 */

require plugin_dir_path(__FILE__) . '../includes/class.settings-api.php';
require plugin_dir_path(__FILE__) . '/partials/class-admin-table.php';
require plugin_dir_path(__FILE__) . '../includes/vendor/autoload.php';
if (!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

class Fb_Notify_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * get error message
     */
    public $output_message = array();

    /**
     * app creadential
     */

    private $txp_app_credential;

    /**
     * Get collected fb dtat
     */

    private $txp_fb_collected_data;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('admin_menu', array($this, 'add_settings_page'));
        $this->settings_api = new WeDevs_Settings_API;
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_ajax_notification_data', array($this, 'txp_notification_data_ajax'));
        add_action('wp_ajax_delete_data', array($this, 'txp_delete_data_ajax'));
        $this->txp_app_credential = get_option('txp_fb_app_id');//credential
        $this->txp_fb_collected_data = get_option('txp_fb_data');//saved data
        $this->redirection_on_condision();
        $this->get_csv_data();


    }

    /**
     * Add an setting page
     *
     * @since  1.0.0
     */
    public function add_settings_page()
    {
        $page_title = esc_html__('Fb Notification', 'txp');
        $menu_title = esc_html__('FB Notify', 'txp');
        $capability = 'manage_options';
        $menu_slug = $this->plugin_name;
        $function = array($this, 'display_options_page_main');
        $icon_url = '';
        $position = 100;
        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    }

    /**
     * Display Admin Page
     */

    public function display_options_page_main()
    {
        ?>
        <div class="bubblingG" style="display: none">
           <span id="bubblingG_1"></span>
           <span id="bubblingG_2"></span>
           <span id="bubblingG_3"></span>
            <div id="loader"></div>
        </div>
        <?php
        echo '<div class="wrap">';
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</div>';
        echo '<div class="txp-table-div">';
        $myListTable = new My_List_Table();
        $myListTable->views();
        $myListTable->prepare_items($this->txp_fb_collected_data);
        $myListTable->display();
        echo '</div>';
        ?>

        <form action="#" id="txp-notification-form" method="post">
            <table class="form-table txp-table">
                <?php wp_nonce_field('notification_data', 'txp_admin_ajax'); ?>
                <tr class="id">
                    <td><input type="hidden" name="id" value=""></td>
                </tr>
                <tr class="txt">
                    <th><?php esc_html_e('Notification Text', 'txp') ?></th>
                    <td>
                        <textarea min="180" id="txp_notification_text" name="txp_notification_text"></textarea>
                        <p class="txp-txt-length"></p>
                    </td>
                </tr>
                <tr class="link">
                    <th><?php esc_html_e('Notification link', 'txp') ?></th>
                    <td>
                        <input type="text" name="txp_notification_link">
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button
                            class="send-notification button button-secondary"><?php esc_html_e('Send', 'txp') ?></button>
                    </td>
                </tr>
            </table>
        </form>

        <div class="txp-notification-output">

        </div>

        <div class="txp-support">
            <h4><?php esc_html_e('Do you need any Support related WordPress or Woocommerce? Please','txp')?> <a href="<?php echo esc_url('https://www.apurba.me/') ?>"><?php esc_html_e('Contact Me','txp') ?></a>. <?php esc_html_e('Your problem will be solved within short time.','txp') ?> </h4>
        </div>

        <?php


    }


    /**
     * Initial Ajdx to send Notification
     */

    function txp_notification_data_ajax()
    {
        //data retrieve
        $nonce = $_POST['nonce'];
        $ids = json_decode(stripslashes(sanitize_text_field($_POST['id'])));
        $text = sanitize_textarea_field($_POST['notification_text']);
        $link = '';
        if ($_POST['notification_link']) {
            $link = sanitize_text_field($_POST['notification_link']);
            $link = urlencode(esc_url($link));
        }

        if (!$nonce && !wp_verify_nonce($nonce, 'notification_data')) die("nonce Varification Failed");
        $fb = new Facebook\Facebook([
            'app_id' => $this->txp_app_credential['txp_fb_app_id_text'],
            'app_secret' => $this->txp_app_credential['txp_fb_app_secret_text'],
            'default_graph_version' => 'v2.9',
        ]);
        $app_token = $this->txp_app_credential['txp_fb_app_id_text'] . '|' . $this->txp_app_credential['txp_fb_app_secret_text'];
        $ids = ($ids) ? $ids : array_column($this->txp_fb_collected_data, 'id');

        if (strlen(trim($text)) > 0 && (strlen(trim($text)) < 181)) {
            $success = 0;
            foreach ($ids as $index => $id) {
                $success++;
                try {
                    $sendNotification = $fb->post('/' . $id . '/notifications/', array('href' => '?fburl=' . $link, 'template' => esc_html($text)), $app_token);
                    //var_dump($sendNotification);
                } catch (Facebook\Exceptions\FacebookResponseException $e) {
                    $success--;
                    array_map(function ($data) use ($id, $index) {
                        if ($data['id'] == $id) {
                            $this->output_message[$index]['name'] = $data['first_name'] . ' ' . $data['last_name'] . '- ';
                        }

                    }, $this->txp_fb_collected_data);

                    $this->output_message[$index]['message'] = $e->getMessage();

                }
                $this->output_message['message']['success'] = $success . esc_html__('Notification Sucessfully Send', 'txp');;
            }
        } else {
            $this->output_message[0]['message'] = esc_html__('Text Character length should be grater than 0 and less than 180', 'txp');
        }


        echo json_encode($this->output_message, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        die();
    }

    /**
     * delete data
     */
    function txp_delete_data_ajax()
    {
        //data retrieve
        $nonce = $_POST['nonce'];
        $ids = json_decode(stripslashes(sanitize_text_field($_POST['id'])));
        if (!$nonce && !wp_verify_nonce($nonce, 'notification_data')) die("nonce Varification Failed");
        foreach ($ids as $index => $id) {
            array_map(function ($data) use ($id, $index) {
                if ($data['id'] == $id) {
                    $key = array_search($data, $this->txp_fb_collected_data);
                    unset($this->txp_fb_collected_data[$key]);
                }

            }, $this->txp_fb_collected_data);

        }

        update_option('txp_fb_data', $this->txp_fb_collected_data);
        $myListTable = new My_List_Table();
        $myListTable->views();
        $myListTable->prepare_items($this->txp_fb_collected_data);
        echo $myListTable->display();

        die();
    }

    function redirection_on_condision()
    {

        if (array_key_exists("fburl", $_GET) && !empty($_GET['fburl'])) {
            $url = esc_url($_GET['fburl']);
            ?>
            <script>
                window.top.location.href = "<?php echo esc_url($url); ?>"
            </script>
            <?php
        }

    }


    function admin_init()
    {
        //set the settings
        $this->settings_api->set_sections($this->get_settings_sections());
        $this->settings_api->set_fields($this->get_settings_fields());
        //initialize settings
        $this->settings_api->admin_init();
    }

    function get_settings_sections()
    {
        $sections = array(
            array(
                'id' => 'txp_fb_app_id',
                'title' => __('Facebook App Credential', 'txp')
            )

        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields()
    {
        $settings_fields = array(
            'txp_fb_app_id' => array(
                array(
                    'name' => 'txp_fb_app_id_text',
                    'label' => __('Facebook App ID', 'txp'),
                    'desc' => __('Please paste your Facebook App Id here', 'txp'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name' => 'txp_fb_app_secret_text',
                    'label' => __('Facebook App Secret', 'txp'),
                    'desc' => __('Please paste your Facebook App Secret here', 'txp'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
            ),

        );
        return $settings_fields;
    }


    /**
     * return csv file
     */

    public function get_csv_data()
    {

        if (isset($_GET['download_report']) && current_user_can('manage_options')) {
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="Fb Notify.csv"');

// do not cache the file
            header('Pragma: no-cache');
            header('Expires: 0');

// create a file pointer connected to the output stream
            $file = fopen('php://output', 'w');

            $stored_data = get_option('txp_fb_data');

// send the column headers
            fputcsv($file, array('Id', 'First Name', 'Last Name', 'email', 'Profile Url'));

// Sample data. This can be fetched from mysql too
            $data = array_map(function ($v) {
                return array(
                    'id' => $v['id'],
                    'first_name' => $v['first_name'],
                    'last_name' => $v['last_name'],
                    'email' => $v['email'],
                    'profile_link' => $v['link'],
                );
            }, $this->txp_fb_collected_data);

// output each row of the data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            exit();
        }
    }


    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Fb_Notify_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Fb_Notify_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/fb-notify-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Fb_Notify_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Fb_Notify_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/fb-notify-admin.js', array('jquery'), $this->version, false);

        wp_localize_script($this->plugin_name, 'admin_data', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'fb_app_id' => (array_key_exists('txp_fb_app_id_text',(array)$this->txp_app_credential))?$this->txp_app_credential['txp_fb_app_id_text']:''
        ));

    }

}
