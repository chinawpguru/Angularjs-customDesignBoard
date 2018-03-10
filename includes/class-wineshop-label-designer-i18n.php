<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://ThinkDualBrain.com
 * @since      1.0.0
 *
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/includes
 * @author     Dual Brain <info@thinkdualbrain.com>
 */
class Wineshop_Label_Designer_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wineshop-label-designer',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
