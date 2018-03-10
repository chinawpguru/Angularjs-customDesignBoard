<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://ThinkDualBrain.com
 * @since      1.0.0
 *
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/includes
 * @author     Dual Brain <info@thinkdualbrain.com>
 */
class Wineshop_Label_Designer {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wineshop_Label_Designer_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wineshop-label-designer';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wineshop_Label_Designer_Loader. Orchestrates the hooks of the plugin.
	 * - Wineshop_Label_Designer_i18n. Defines internationalization functionality.
	 * - Wineshop_Label_Designer_Admin. Defines all hooks for the admin area.
	 * - Wineshop_Label_Designer_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wineshop-label-designer-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wineshop-label-designer-i18n.php';

		/**
		 * The class that registers the Custom Post Type for storing completed labels
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wineshop-label-post-type.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wineshop-label-designer-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wineshop-label-designer-public.php';



		$this->loader = new Wineshop_Label_Designer_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wineshop_Label_Designer_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wineshop_Label_Designer_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wineshop_Label_Designer_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'edit_form_top', $plugin_admin, 'add_modal_link' );

		$this->loader->add_action( 'woocommerce_product_options_general_product_data', $plugin_admin, 'wc_add_custom_fields' );
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'wc_save_custom_fields' );
		$this->loader->add_action( 'woocommerce_check_cart_items', $plugin_admin, 'can_checkout' );

		$this->loader->add_action( 'dualbrain_bydesign_woocommerce_payment_complete', $plugin_admin, 'add_label_metadata', 10, 3 );

		// Post Actions
		$this->loader->add_action( 'post_row_actions', $plugin_admin, 'post_row_actions', 10, 2 );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'check_custom_actions' );
		$this->loader->add_action( 'manage_posts_extra_tablenav', $plugin_admin, 'add_list_thickbox' );

		/**
		 * AJAX Functions
		 */
		// Load Label JSON/Images
		$this->loader->add_action( 'wp_ajax_wineshop_label_load', $plugin_admin, 'ajax_wineshop_label_load' );
		$this->loader->add_action( 'wp_ajax_nopriv_wineshop_label_load', $plugin_admin, 'ajax_wineshop_label_load' );

		// Save Label JSON/Images
		$this->loader->add_action( 'wp_ajax_wineshop_label_save', $plugin_admin, 'ajax_wineshop_label_save' );
		$this->loader->add_action( 'wp_ajax_nopriv_wineshop_label_save', $plugin_admin, 'ajax_wineshop_label_save' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wineshop_Label_Designer_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wineshop_Label_Designer_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
