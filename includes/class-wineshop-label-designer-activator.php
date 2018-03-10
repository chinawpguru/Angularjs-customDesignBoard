<?php

/**
 * Fired during plugin activation
 *
 * @link       http://ThinkDualBrain.com
 * @since      1.0.0
 *
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wineshop_Label_Designer
 * @subpackage Wineshop_Label_Designer/includes
 * @author     Dual Brain <info@thinkdualbrain.com>
 */
class Wineshop_Label_Designer_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$upload_dir      = wp_upload_dir();
		wp_mkdir_p($upload_dir['basedir'] . '/wineshop-labels');
	}

}
