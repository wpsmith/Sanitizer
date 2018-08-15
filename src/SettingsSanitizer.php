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
 * @copyright 2018 Travis Smith
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link      https://github.com/akamai/wp-akamai
 * @since     0.2.0
 */

namespace WPS\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Admin\SettingsSanitizer' ) ) {

	/**
	 * Class SettingsSanitizer.
	 *
	 * @package WPS\Plugins\Fundraising\Admin
	 */
	class SettingsSanitizer extends \WPS\Core\Singleton {

		/**
		 * The sanitizer object.
		 *
		 * @var Sanitizer
		 */
		public $sanitizer;

		/**
		 * Holds list of all options as array.
		 *
		 * @var array Options
		 */
		public $options = array();

		/**
		 * Constructor.
		 *
		 * @since 1.7.0
		 */
		public function __construct( Sanitizer $sanitizer ) {

			$this->sanitizer = $sanitizer;

			/**
			 * Fires when SettingsSanitizer is initialized.
			 *
			 * @param SettingsSanitizer $this The SettingsSanitizer object.
			 */
			do_action_ref_array( 'wps_settings_sanitizer_init', array( &$this ) );

		}

		/**
		 * Add sanitization filters to options.
		 *
		 * Associates a sanitization filter to each option (or sub options if they
		 * exist) before adding a reference to run the option through that
		 * sanitizer at the right time.
		 *
		 * @param string       $filter    Sanitization filter type.
		 * @param string       $option    Option key.
		 * @param array|string $suboption Optional. Sub-option key.
		 *
		 * @return bool True when complete.
		 */
		public function add_filter( $filter, $option, $suboption = null ) {

			if ( is_array( $suboption ) ) {
				foreach ( $suboption as $so ) {
					$this->options[ $option ][ $so ] = $filter;
				}
			} elseif ( null === $suboption ) {
				$this->options[ $option ] = $filter;
			} else {
				$this->options[ $option ][ $suboption ] = $filter;
			}

			add_filter( 'sanitize_option_' . $option, array($this, 'sanitize'), 10, 2 );

			return true;

		}

		/**
		 * Checks sanitization filter exists, and if so, passes the value through it.
		 *
		 * @param string $filter    Sanitization filter type.
		 * @param string $new_value New value.
		 * @param string $old_value Previous value.
		 *
		 * @return mixed Filtered value, or submitted value if value is unfiltered.
		 */
		public function do_filter( $filter, $new_value, $old_value ) {

			$available_filters = $this->get_available_filters();

			if ( ! array_key_exists( $filter, $available_filters ) ) {
				return $new_value;
			}

			return call_user_func( $available_filters[ $filter ], $new_value, $old_value );

		}

		/**
		 * Return array of known sanitization filter types.
		 *
		 * Array can be filtered via 'wps_available_sanitizer_filters' to let
		 * child themes and plugins add their own sanitization filters.
		 *
		 * @return array Keys of sanitization types, and values of the
		 *               filter function name as a callback.
		 */
		public function get_available_filters() {

			$default_filters = array(
				'one_zero'                 => array( $this->sanitizer, 'one_zero' ),
				'no_html'                  => array( $this->sanitizer, 'no_html' ),
				'absint'                   => array( $this->sanitizer, 'absint' ),
				'safe_html'                => array( $this->sanitizer, 'safe_html' ),
				'requires_unfiltered_html' => array( $this->sanitizer, 'requires_unfiltered_html' ),
				'unfiltered_or_safe_html'  => array( $this->sanitizer, 'unfiltered_or_safe_html' ),
				'url'                      => array( $this->sanitizer, 'url' ),
				'email_address'            => array( $this->sanitizer, 'email_address' ),
			);

			/**
			 * Filter the available sanitization filter types.
			 *
			 * @param array $default_filters Array with keys of sanitization types, and values of the filter function name as a callback
			 */
			return apply_filters( 'wps_available_sanitizer_filters', $default_filters );

		}

		/**
		 * Sanitize a value, via the sanitization filter type associated with an
		 * option.
		 *
		 * @param mixed  $new_value New value.
		 * @param string $option    Name of the option.
		 *
		 * @return mixed Filtered, or unfiltered value.
		 */
		public function sanitize( $new_value, $option ) {

			if ( ! isset( $this->options[ $option ] ) ) {
				// We are not filtering this option at all.
				return $new_value;
			}

			if ( is_string( $this->options[ $option ] ) ) {
				// Single option value.
				return $this->do_filter( $this->options[ $option ], $new_value, get_option( $option ) );
			}

			if ( is_array( $this->options[ $option ] ) ) {
				// Array of sub-option values to loop through.
				$old_value = get_option( $option );
				foreach ( $this->options[ $option ] as $suboption => $filter ) {
					$old_value[ $suboption ] = isset( $old_value[ $suboption ] ) ? $old_value[ $suboption ] : '';
					$new_value[ $suboption ] = isset( $new_value[ $suboption ] ) ? $new_value[ $suboption ] : '';
					$new_value[ $suboption ] = $this->do_filter( $filter, $new_value[ $suboption ], $old_value[ $suboption ] );
				}

				return $new_value;
			}

			// Should never hit this.
			return $new_value;
		}

	}
}