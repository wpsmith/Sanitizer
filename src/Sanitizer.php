<?php
/**
 * Sanitizer Admin Class.
 *
 * Settings sanitization class. Provides methods for sanitizing data.
 *
 * You may copy, distribute and modify the software as long as you track
 * changes/dates in source files. Any modifications to or software including
 * (via compiler) GPL-licensed code must also be made available under the GPL
 * along with build & install instructions.
 *
 * PHP Version 7.2
 *
 * @package   WPS\Plugins\Fundraising
 * @author    Travis Smith <t@wpsmith.net>
 * @copyright 2018-2019 Travis Smith
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link      https://github.com/akamai/wp-akamai
 * @since     0.2.0
 */

namespace WPS\WP\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Sanitizer' ) ) {
	/**
	 * Class Sanitizer.
	 *
	 * @package WPS\Plugins\Fundraising\Admin
	 */
	class Sanitizer extends \WPS\Core\Singleton {

		/**
		 * Returns a 1 or 0, for all truthy / falsy values.
		 *
		 * Uses double casting. First, we cast to bool, then to integer.
		 *
		 * @since 2.6.0
		 *
		 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in.
		 *
		 * @return int `1` or `0`.
		 */
		public static function one_zero( $new_value ) {

			return (int) (bool) $new_value;

		}

		/**
		 * Returns a positive integer value.
		 *
		 * @since 2.6.0
		 *
		 * @param mixed $new_value Should ideally be a positive integer.
		 *
		 * @return int Positive integer.
		 */
		public static function absint( $new_value ) {

			return absint( $new_value );

		}

		/**
		 * Removes HTML tags from string.
		 *
		 * @since 2.6.0
		 *
		 * @param string $new_value String, possibly with HTML in it.
		 *
		 * @return string String without HTML in it.
		 */
		public static function no_html( $new_value ) {

			return strip_tags( $new_value );

		}

		/**
		 * Makes URLs safe
		 *
		 * @since 2.6.0
		 *
		 * @param string $new_value String, a URL, possibly unsafe.
		 *
		 * @return string String a safe URL.
		 */
		public static function url( $new_value ) {

			return esc_url_raw( $new_value );

		}

		/**
		 * Makes Email Addresses safe, via sanitize_email()
		 *
		 * @since 2.6.0
		 *
		 * @param string $new_value String, an email address, possibly unsafe.
		 *
		 * @return string String a safe email address.
		 */
		public static function email_address( $new_value ) {

			return sanitize_email( $new_value );

		}

		/**
		 * Removes unsafe HTML tags, via wp_kses_post().
		 *
		 * @since 2.6.0
		 *
		 * @param string $new_value String with potentially unsafe HTML in it.
		 *
		 * @return string String with only safe HTML in it.
		 */
		public static function safe_html( $new_value ) {

			return wp_kses_post( $new_value );

		}

		/**
		 * Keeps the option from being updated if the user lacks unfiltered_html
		 * capability.
		 *
		 * @since 2.6.0
		 *
		 * @param string $new_value New value.
		 * @param string $old_value Previous value.
		 *
		 * @return string New or previous value, depending if user has correct
		 *                capability or not.
		 */
		public static function requires_unfiltered_html( $new_value, $old_value ) {

			if ( current_user_can( 'unfiltered_html' ) ) {
				return $new_value;
			}

			return $old_value;
		}

		/**
		 * Removes unsafe HTML tags when user does not have unfiltered_html
		 * capability.
		 *
		 * @since 2.6.0
		 *
		 * @param string $new_value New value.
		 * @param string $old_value Previous value.
		 *
		 * @return string New or safe HTML value, depending if user has correct
		 *                capability or not.
		 */
		public static function unfiltered_or_safe_html( $new_value, $old_value ) {

			if ( current_user_can( 'unfiltered_html' ) ) {
				return $new_value;
			}

			return wp_kses_post( $new_value );

		}

	}
}