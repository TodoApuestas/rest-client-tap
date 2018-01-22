<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.linkedin.com/in/mrbrazzi/
 * @since      1.0.0
 *
 * @package    RestClientTap
 * @subpackage RestClientTap/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    RestClientTap
 * @subpackage RestClientTap/includes
 * @author     Alain Sanchez <luka.ghost@gmail.com>
 */
class RestClientTapDeactivator {

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		delete_option('TAP_BOOKIES');
		delete_option('TAP_DEPORTES');
		delete_option('TAP_COMPETICIONES');
	}

}
