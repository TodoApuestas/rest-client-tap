<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.linkedin.com/in/mrbrazzi/
 * @since      1.0.0
 *
 * @package    RestClientTap
 * @subpackage RestClientTap/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    RestClientTap
 * @subpackage RestClientTap/includes
 * @author     Alain Sanchez <luka.ghost@gmail.com>
 */
class RestClientTapActivator {

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		add_option('TAP_BOOKIES', array());
		add_option('TAP_DEPORTES', array());
		add_option('TAP_COMPETICIONES', array());
	}

}
