<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       apurba.me
 * @since      1.0.0
 *
 * @package    Fb_Notify
 * @subpackage Fb_Notify/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fb_Notify
 * @subpackage Fb_Notify/public
 * @author     Apurba <hello@apurba.me>
 */
class Fb_Notify_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	private $facebook_app_id ;

	/**
	get new id on ajax
	 */


	private $txp_previous_saved_data;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_shortcode('txp-notification',array($this,'txp_notification_callback'));
		add_action('wp_ajax_noti_get', array($this,'txp_noti_callback_ajax'));
		add_action('wp_ajax_nopriv_noti_get',array($this,'txp_noti_callback_ajax'));
		add_option( 'txp_fb_data', array());
		$this->facebook_app_id= get_option('txp_fb_app_id');
		if(array_key_exists('txp_fb_app_id_text',(array)$this->facebook_app_id)){
			$this->facebook_app_id= $this->facebook_app_id['txp_fb_app_id_text'];
		}
		$this->txp_previous_saved_data = get_option('txp_fb_data');

	}


	function txp_noti_callback_ajax (){

		if(!wp_verify_nonce($_POST['nonce'],'txp_frontend_data_'.__FILE__))
			die('nonce vefied false');
		$fb_data = json_decode(stripslashes(sanitize_text_field($_POST['fb_data'])));
		$fb_data = (array)$fb_data;
		$existed_id_array = array_column($this->txp_previous_saved_data, 'id');
		$current_id = $fb_data['id'];
		$key = in_array($current_id, $existed_id_array);
		if(!$key){
			$this->txp_previous_saved_data[]= $fb_data;
			update_option('txp_fb_data',$this->txp_previous_saved_data);
		}
		die();
	}

	/**
	 * @return string
	 */

	function txp_notification_callback($atts){
		
		$atts = shortcode_atts(array(
				'btn_txt'=>'Get Notification'
				),$atts);

		ob_start();
		?>
		<div id="status"></div>

		<!-- Facebook login or logout button -->
		<a href="javascript:void(0);" class="ph-button ph-btn-blue"  id="fbLink"><?php echo esc_html($atts['btn_txt']); ?></a>

		<!-- Display user profile data -->
		<div id="userData"></div>
		<?php

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fb-notify-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fb-notify-public.js', array( 'jquery' ), $this->version, true );


		wp_localize_script($this->plugin_name, 'admin_data', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'fb_app_id'=>$this->facebook_app_id,
			'nonce' => wp_create_nonce('txp_frontend_data_'.__FILE__)
		));

	}

}
