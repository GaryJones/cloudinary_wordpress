<?php

namespace Cloudinary;

use WP_Error;
use stdClass;

/**
 * Derived work.
 *
 * @version   1.6
 * @author    Joachim Kudish <info@jkudish.com>
 * @link      http://jkudish.com
 * @package   WP_GitHub_Updater
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @copyright Copyright (c) 2011-2013, Joachim Kudish
 *
 * GNU General Public License, Free Software Foundation
 * <http://creativecommons.org/licenses/GPL/2.0/>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
class Updater {

	/**
	 * GitHub Updater version.
	 */
	const VERSION = 1.6;

	/**
	 * @var array $config The config for the updater.
	 * @access public
	 */
	public $config;

	/**
	 * @var array $missing_config Any config that is missing from the initialization of this instance.
	 * @access public
	 */
	public $missing_config;

	/**
	 * @var array $github_data Temporarily store the data fetched from GitHub, allows us to only load the data once per
	 *      class instance.
	 * @access private
	 */
	private $github_data;

	/**
	 * Class Constructor
	 *
	 * @param array $config the configuration required for the updater to work
	 *
	 * @return void
	 * @see   has_minimum_config()
	 * @since 1.0
	 */
	public function __construct( $config = array() ) {

		$defaults = array(
			'slug'               => plugin_basename( __FILE__ ),
			'proper_folder_name' => dirname( plugin_basename( __FILE__ ) ),
			'sslverify'          => true,
			'access_token'       => '',
		);

		$this->config = wp_parse_args( $config, $defaults );

		// if the minimum config isn't set, issue a warning and bail
		if ( ! $this->has_minimum_config() ) {
			$message = 'The GitHub Updater was initialized without the minimum required configuration, please check the config in your plugin. The following params are missing: ';
			$message .= implode( ',', $this->missing_config );
			_doing_it_wrong( __CLASS__, $message, self::VERSION );

			return;
		}

		$this->set_defaults();

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'api_check' ) );

		// Hook into the plugin details screen
		add_filter( 'plugins_api', array( $this, 'get_plugin_info' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 3 );

		// set timeout
		add_filter( 'http_request_timeout', array( $this, 'http_request_timeout' ) );

		// set sslverify for zip download
		add_filter( 'http_request_args', array( $this, 'http_request_sslverify' ), 10, 2 );
	}

	public function has_minimum_config() {

		$this->missing_config = array();

		$required_config_params = array(
			'api_url',
			'raw_url',
			'github_url',
			'zip_url',
			'requires',
			'tested',
			'readme',
		);

		foreach ( $required_config_params as $required_param ) {
			if ( empty( $this->config[ $required_param ] ) ) {
				$this->missing_config[] = $required_param;
			}
		}

		return ( empty( $this->missing_config ) );
	}

	/**
	 * Check whether or not the transients need to be overruled and API needs to be called for every single page load.
	 *
	 * @return bool Overrule or not.
	 */
	public function overrule_transients() {
		return ( defined( 'WP_GITHUB_FORCE_UPDATE' ) && WP_GITHUB_FORCE_UPDATE );
	}

	/**
	 * Set defaults
	 *
	 * @return void
	 * @since 1.2
	 */
	public function set_defaults() {
		if ( ! empty( $this->config['access_token'] ) ) {
			// See Downloading a zip (private repo) https://help.github.com/articles/downloading-files-from-the-command-line
			$parsed_url = parse_url( $this->config['zip_url'] );
			$zip_url    = '';

			if ( is_array( $parsed_url ) ) {
				$zip_url = $parsed_url['scheme'] . '://api.github.com/repos' . $parsed_url['path'];
				$zip_url = add_query_arg( array( 'access_token' => $this->config['access_token'] ), $zip_url );
			}

			$this->config['zip_url'] = $zip_url;
		}

		if ( ! isset( $this->config['new_version'] ) ) {
			$this->config['new_version'] = $this->get_new_version();
		}

		if ( ! isset( $this->config['last_updated'] ) ) {
			$this->config['last_updated'] = $this->get_date();
		}

		if ( ! isset( $this->config['description'] ) ) {
			$this->config['description'] = $this->get_description();
		}

		$plugin_data = $this->get_plugin_data();
		if ( ! isset( $this->config['plugin_name'] ) ) {
			$this->config['plugin_name'] = $plugin_data['Name'];
		}

		if ( ! isset( $this->config['version'] ) ) {
			$this->config['version'] = $plugin_data['Version'];
		}

		if ( ! isset( $this->config['author'] ) ) {
			$this->config['author'] = $plugin_data['Author'];
		}

		if ( ! isset( $this->config['homepage'] ) ) {
			$this->config['homepage'] = $plugin_data['PluginURI'];
		}

		if ( ! isset( $this->config['readme'] ) ) {
			$this->config['readme'] = 'README.md';
		}
	}

	/**
	 * Callback fn for the http_request_timeout filter
	 *
	 * @return int timeout value
	 * @since 1.0
	 */
	public function http_request_timeout() {
		return 2;
	}

	/**
	 * Callback fn for the http_request_args filter
	 *
	 * @param array  $args
	 * @param string $url
	 *
	 * @return array
	 */
	public function http_request_sslverify( $args, $url ) {
		if ( $url === $this->config['zip_url'] ) {
			$args['sslverify'] = $this->config['sslverify'];
		}

		return $args;
	}


	/**
	 * Get New Version from GitHub
	 *
	 * @return int $version the version number
	 * @since 1.0
	 */
	public function get_new_version() {
		$version = get_site_transient( md5( $this->config['slug'] ) . '_new_version' );

		if ( ! isset( $version ) || ! $version || '' === $version || $this->overrule_transients() ) {
			$raw_response = $this->remote_get( trailingslashit( $this->config['raw_url'] ) . $this->config['readme'] );

			if ( is_wp_error( $raw_response ) ) {
				return $version;
			}

			$body = json_decode( $raw_response['body'], true );
			if ( empty( $body['content'] ) ) {
				return '';
			}

			$version_readme = trim( base64_decode( $body['content'] ) );

			if ( - 1 === version_compare( $version, $version_readme ) ) {
				$version = $version_readme;
			}
		}

		// refresh every hour
		if ( false !== $version ) {
			set_site_transient( md5( $this->config['slug'] ) . '_new_version', $version, HOUR_IN_SECONDS );
		}

		return $version;
	}


	/**
	 * Interact with GitHub
	 *
	 * @param string $query
	 *
	 * @return array|WP_Error
	 * @since 1.6
	 */
	public function remote_get( $query ) {
		if ( ! empty( $this->config['access_token'] ) ) {
			$query = add_query_arg( array( 'access_token' => $this->config['access_token'] ), $query );
		}

		return wp_remote_get(
			$query,
			array(
				'sslverify' => $this->config['sslverify']
			)
		);
	}


	/**
	 * Get GitHub Data from the specified repository
	 *
	 * @return array|false The data.
	 * @since 1.0
	 */
	public function get_github_data() {
		if ( isset( $this->github_data ) && ! empty( $this->github_data ) ) {
			$github_data = $this->github_data;
		} else {
			$github_data = get_site_transient( md5( $this->config['slug'] ) . '_github_data' );
			if ( ! isset( $github_data ) || ! $github_data || '' === $github_data || $this->overrule_transients() ) {
				$github_data = $this->remote_get( $this->config['api_url'] );

				if ( is_wp_error( $github_data ) ) {
					return false;
				}

				$github_data = json_decode( $github_data['body'], false );

				// refresh every hour
				set_site_transient( md5( $this->config['slug'] ) . '_github_data', $github_data, HOUR_IN_SECONDS );
			}

			// Store the data in this class instance for future calls
			$this->github_data = $github_data;
		}

		return $github_data;
	}


	/**
	 * Get update date
	 *
	 * @return string $date the date
	 * @since 1.0
	 */
	public function get_date() {
		$_date = $this->get_github_data();

		return ( ! empty( $_date->updated_at ) ) ? date( 'Y-m-d', strtotime( $_date->updated_at ) ) : false;
	}


	/**
	 * Get plugin description
	 *
	 * @return string|false The description.
	 * @since 1.0
	 */
	public function get_description() {
		$_description = $this->get_github_data();

		return ( ! empty( $_description->description ) ) ? $_description->description : false;
	}


	/**
	 * Get Plugin data
	 *
	 * @return array The data.
	 * @since 1.0
	 */
	public function get_plugin_data() {
		include_once ABSPATH . '/wp-admin/includes/plugin.php';

		return get_plugin_data( WP_PLUGIN_DIR . '/' . $this->config['slug'] );
	}


	/**
	 * Hook into the plugin update check and connect to GitHub
	 *
	 * @param object $transient the plugin data transient
	 *
	 * @return object $transient updated plugin data transient
	 * @since 1.0
	 */
	public function api_check( $transient ) {

		// Check if the transient contains the 'checked' information
		// If not, just return its value without hacking it
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// check the version and decide if it's new
		$update = version_compare( $this->config['new_version'], $this->config['version'] );

		if ( 1 === $update ) {
			$response              = new stdClass;
			$response->new_version = $this->config['new_version'];
			$response->slug        = $this->config['proper_folder_name'];
			$response->url         = add_query_arg( array( 'access_token' => $this->config['access_token'] ), $this->config['github_url'] );
			$response->package     = $this->config['zip_url'];

			$transient->response[ $this->config['slug'] ] = $response;
		}

		return $transient;
	}


	/**
	 * Get Plugin info.
	 *
	 * @param bool     $false    Always false.
	 * @param string   $action   The API function being performed.
	 * @param stdClass $response
	 *
	 * @return object|false The plugin info.
	 * @since 1.0
	 */
	public function get_plugin_info( $false, $action, $response ) {

		// Check if this call API is for the right plugin
		if ( ! isset( $response->slug ) || $response->slug !== $this->config['slug'] ) {
			return false;
		}

		$response->slug          = $this->config['slug'];
		$response->plugin_name   = $this->config['plugin_name'];
		$response->version       = $this->config['new_version'];
		$response->author        = $this->config['author'];
		$response->homepage      = $this->config['homepage'];
		$response->requires      = $this->config['requires'];
		$response->tested        = $this->config['tested'];
		$response->downloaded    = 0;
		$response->last_updated  = $this->config['last_updated'];
		$response->sections      = array( 'description' => $this->config['description'] );
		$response->download_link = $this->config['zip_url'];

		return $response;
	}


	/**
	 * Upgrader/Updater
	 * Move & activate the plugin, echo the update message
	 *
	 * @param boolean $true       always true
	 * @param mixed   $hook_extra not used
	 * @param array   $result     the result of the move
	 *
	 * @return array $result the result of the move
	 * @since 1.0
	 */
	public function upgrader_post_install( $true, $hook_extra, $result ) {

		global $wp_filesystem;

		// Move & Activate
		$proper_destination = WP_PLUGIN_DIR . '/' . $this->config['proper_folder_name'];
		$wp_filesystem->move( $result['destination'], $proper_destination );
		$result['destination'] = $proper_destination;
		$activate              = activate_plugin( WP_PLUGIN_DIR . '/' . $this->config['slug'] );

		// Output the update message
		$fail    = __( 'The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'github_plugin_updater' );
		$success = __( 'Plugin reactivated successfully.', 'github_plugin_updater' );
		echo is_wp_error( $activate ) ? $fail : $success;

		return $result;
	}
}
