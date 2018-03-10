<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://ThinkDualBrain.com
 * @since      1.0.0
 *
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/admin
 * @author     Dual Brain <info@thinkdualbrain.com>
 */
class Wineshop_Label_Designer_Admin {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wineshop_Label_Designer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wineshop_Label_Designer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wineshop-label-designer-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wineshop_Label_Designer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wineshop_Label_Designer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wineshop-label-designer-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function wc_add_custom_fields()
	{
		global $woocommerce, $post;

		echo '<div class="wineshop-options-group options-group options_group">';

		// Custom fields will be created here...
		// Number Field
		woocommerce_wp_text_input(
			array(
				'id'                => '_wineshop_label_count',
				'label'             => __( '# of Custom Labels', 'woocommerce' ),
				'placeholder'       => '',
				'description'       => __( 'Define the number of custom labels this product allows. Leave at 0 or blank to disable custom labels.', 'woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' 	=> 'any',
					'min'	=> '0'
				)
			)
		);

		echo '</div>';
	}

	public function wc_save_custom_fields( $post_id )
	{
		$woocommerce_number_field = $_POST['_wineshop_label_count'];
		if( !empty( $woocommerce_number_field ) )
			update_post_meta( $post_id, '_wineshop_label_count', esc_attr( $woocommerce_number_field ) );
	}

	public function can_checkout()
	{
		$online_order_id = WC()->session->get('bydesign_online_order_id');

		if( $online_order_id )
		{

			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				$product = wc_get_product($values['product_id']);
				$product_id = $values['product_id'];
				$product_name = $product->get_name();
				$labels = get_post_meta( $product_id, '_wineshop_label_count', true );
				if( !empty($labels) )
				{
					// We have a label requirement. Now, let's see if it's been met.
					$upload_dir = wp_upload_dir();
					$base_dir = sprintf( '%s/wineshop-labels', $upload_dir['basedir'] );
					$json_file = sprintf( '%s/%s-%s.json', $base_dir, $online_order_id, $product_id );
					$jpg_file = sprintf( '%s/%s-%s.jpg', $base_dir, $online_order_id, $product_id );
					$png_file = sprintf( '%s/%s-%s.png', $base_dir, $online_order_id, $product_id );
					if( !file_exists( $json_file ) || !file_exists( $jpg_file ) || !file_exists( $png_file ) ) {
						// Files are required, but missing. The user must complete the label customizations before proceeding to checkout!
						wc_add_notice( sprintf('You must customize your %s for <strong>%s</strong> before completing checkout!', $labels > 1 ? 'labels' : 'label', $product_name ), 'error' );
						remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
					}

				}

			}


		}


	}

	public function add_label_metadata($order_id, $online_order_id, $user)
	{
		if( !empty($order_id) && !empty($online_order_id) && $user )
		{
			$label_post = wp_insert_post(array(
				'post_type' => ''
			));
		}
	}

	function post_row_actions($actions, $post)
	{
		if ($post->post_type === 'wineshop-label')
		{
			$actions = array();

			$order_id = get_post_meta($post->ID, 'bydesign_online_order_id', true);
			$product_id = get_post_meta($post->ID, 'bydesign_product_id', true);
			$label_count = get_post_meta($post->ID, 'label_count', true);

			$thumb = Wineshop_Label_Post_Type::get_thumbnail($order_id, $product_id);

			$print_url = add_query_arg(
				array(
					'post_id' => $post->ID,
					'order_id' => $order_id,
					'product_id' => $product_id,
					'content' => content_url(),
					'image' => $thumb,
					'count' => $label_count ?: 0,
					'TB_iframe' => true,
					'width' => 500,
					'height' => 360,
				),
				DUALBRAIN_WINESHOP_LABEL_DESIGNER_PLUGIN_URL.'/admin/partials/wineshop-label-designer-admin-display.php'
			);
			$actions['print'] = '<a href="'.esc_url( $print_url ).'" title="" class="thickbox">Print</a>';

			$designer_url = add_query_arg(
				array(
					'order_id' => $order_id,
					'product_id' => $product_id,
					'TB_iframe' => true,
				),
				DUALBRAIN_WINESHOP_LABEL_DESIGNER_PLUGIN_URL.'public/index.html'
			);
			$actions['designer'] = '<a href="'.esc_url( $designer_url ).'" title="" class="thickbox thickbox-fullscreen">Open Designer</a>';

			// Remove the unecessary actions

		}
		return $actions;
	}

	function check_custom_actions(){
		if ( isset( $_REQUEST['wineshop_label_action'] ) )
		{

			switch($_REQUEST['wineshop_label_action'])
			{
				case 'print_pdf':

					$post_id = $_REQUEST['post_id'];
					$slots = $_REQUEST['slots'];
					$count = $_REQUEST['count'];
					$this->generate_pdf($post_id, $slots, $count);
					break;
			}

		}

	}

	public function add_list_thickbox($which)
	{
		if ( 'top' === $which )
			add_thickbox();

		if( 'bottom' === $which )
		{
			?>
			<script type="text/javascript">
                function wineshop_resize_thickbox()
                {
                    var thickbox_width = jQuery(window).width()-80;
                    var thickbox_height = jQuery(window).height()-80;
                    jQuery('.thickbox-fullscreen').attr("href", jQuery('.thickbox-fullscreen').attr("href") + "&width="+thickbox_width+"&height="+thickbox_height );
                }

                jQuery( window ).on( 'ready load resize orientationchange', function() {
                    wineshop_resize_thickbox();
                });
			</script>
			<?php
			echo '';
		}

	}

	public function ajax_wineshop_label_load()
	{
		$order_id = $_REQUEST['order_id'];
		$product_id = $_REQUEST['product_id'];

		$label_post = $this->get_matching_post($order_id, $product_id);

		// TODO: Add additional safety checks. Users can only edit Drafts. Only Admins can edit published.
		// TODO: Add Nonce token/verification on load

		wp_die();
	}

	public function ajax_wineshop_label_save()
	{
		// TODO: Add Nonce token/verification on load

		$order_id = $_POST['order_id'];
		$product_id = $_POST['product_id'];

		$file_name = sprintf("%s-%s", $order_id, $product_id );
		$data_jpeg = preg_replace('#^data:image/\w+;base64,#i', '', $_POST['jpeg']);
		$data_png = preg_replace('#^data:image/\w+;base64,#i', '', $_POST['png']);
		$data_json = urldecode(preg_replace('#^data:text/json;charset=utf-8,#i', '', $_POST['json']));
		$upload_dir = wp_upload_dir();
		$abs_path = $upload_dir['basedir'] . '/wineshop-labels/';

		//if(file_exists($abs_path.$file_name.".json")){
		file_put_contents($abs_path.$file_name.".json", $data_json);
		file_put_contents($abs_path.$file_name.".jpeg", base64_decode($data_jpeg));
		file_put_contents($abs_path.$file_name.".png", base64_decode($data_png));

		// Create or update the post
		$label_post = $this->get_matching_post($order_id, $product_id);

		if( !$label_post )
		{
			// No existing post. Create one.
			$label_post = array(
				'post_type' => 'wineshop-label',
				'post_name' => sprintf('wineshop-label-%s-%s', $order_id, $product_id),
				'post_title' => sprintf('Order: %s, Product: %s', $order_id, $product_id),
				'post_status' => 'draft'
			);
		}

		// Insert or update
		$label_post_id = wp_insert_post($label_post);

		// Update Metadata
		update_post_meta( $label_post_id, 'bydesign_online_order_id', $order_id );
		update_post_meta( $label_post_id, 'bydesign_product_id', $product_id );

		wp_die();
	}

	public function generate_pdf($label_id, $slots, $count)
	{
		if( !$label_id )
			return;



		require_once( DUALBRAIN_WINESHOP_LABEL_DESIGNER_PLUGIN_DIR . 'includes/class-wineshop-label-pdf.php');

		$label = get_post($label_id);
		if($label)
		{
			$custom = get_post_custom($label_id);

			// Make sure we have a Order and Product ID stored as meta
			$order_id = $custom['bydesign_online_order_id'][0] ?: false;
			$product_id = $custom['bydesign_product_id'][0] ?: false;
			if( $order_id && $product_id )
			{
				// Check if a PNG exists for this
				$upload_dir = wp_upload_dir();
				$labels_path = $upload_dir['basedir'] . '/wineshop-labels';
				$label_file = sprintf("%s/wineshop-labels/%s-%s.png", $upload_dir['basedir'], $order_id, $product_id);

				if ( file_exists( $label_file ) )
				{
					$pdf = new Wineshop_Label_PDF($label_file, $slots, $count);
					$pdf->Output('D', sprintf("WineShop Label (Order ID %s, Product ID %s).pdf", $order_id, $product_id ));
				}

			}

		}
		exit;

	}

	private function get_matching_post($order_id, $product_id)
	{
		$label_post = get_posts(array(
			'post_type' => 'wineshop-label',
			'posts_per_page' => 1,
			'post_status' => 'any',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'bydesign_online_order_id',
					'value' => $order_id,
					'compare' => '='
				),
				array(
					'key' => 'bydesign_product_id',
					'value' => $product_id,
					'compare' => '='
				),
			)
		));
		
		if($label_post)
			return array_shift($label_post);
	}

	public function add_modal_link($post){
		if($post->post_type == 'wineshop-label'){
			$order_id = get_post_meta($post->ID, 'bydesign_online_order_id', true);
			$product_id = get_post_meta($post->ID, 'bydesign_product_id', true);
			add_thickbox();
			echo '<a href="'.plugin_dir_url(dirname(__FILE__)).'public/index.html?order_id='.$order_id.'&product_id='.$product_id.'&TB_iframe=true" class="thickbox edit_label button">Edit Label</a>';
			echo '<script type="text/javascript">
				jQuery(document).ready(function($){
					var edit_label_link = $(".edit_label").attr("href");
					edit_label_link = $(".edit_label").attr("href", edit_label_link+"&width="+($(window).width()-20)+"&height="+($(window).height()-40));
				})
			</script>';
		}
	}

}
