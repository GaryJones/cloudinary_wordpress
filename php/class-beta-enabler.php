<?php

namespace Cloudinary;

use WP_Admin_Bar;

/**
 * Class Beta.
 * Enables Beta features to be tested.
 *
 * @package Cloudinary
 */
class Beta_Enabler {

	/**
	 * Holds the plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Holds the beta features.
	 *
	 * @var array
	 */
	protected $features = array();

	/**
	 * Beta constructor.
	 */
	public function __construct() {
		add_action(
			'plugins_loaded',
			function () {
				if ( is_callable( 'Cloudinary\get_plugin_instance' ) ) {
					$this->maybe_load_hooks();
				} else {
					add_action( 'admin_notices', array( $this, 'enable_cloudinary_notice' ) );
				}
			},
			0
		);
	}

	/**
	 * Load hooks if conditions are meet to enable the Beta Enabler plugin.
	 */
	public function maybe_load_hooks() {
		$plugin = get_plugin_instance();

		if ( 1 > version_compare( $plugin->version, '2.7.3' ) ) {
			add_action( 'admin_notices', array( $this, 'enable_cloudinary_notice_min_version_notice' ) );
			deactivate_plugins( plugin_basename( CLDN_BETA ) );

			return;
		}

		$this->load_hooks();
		$this->init_updater();
	}

	/**
	 * Load WordPress hooks.
	 */
	public function load_hooks() {
		add_action( 'cloudinary_beta', array( $this, 'beta_features' ), 10, 3 );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_item' ), 100 );
		add_action( 'admin_head', array( $this, 'styles' ) );
		add_action( 'wp_head', array( $this, 'styles' ) );
		add_action( 'init', array( $this, 'maybe_toggle_feature' ), 5 );
		add_action( 'init', array( $this, 'setup_properties' ) );
		add_action( 'admin_notices', array( $this, 'maybe_inactive_features_notice' ) );
	}

	/**
	 * Init the plugin updater.
	 */
	public function init_updater() {
		require_once 'class-updater.php';

		if ( is_admin() ) {
			$config = array(
				'slug'               => plugin_basename( CLDN_BETA ),
				'proper_folder_name' => 'cloudinary-beta',
				'api_url'            => 'https://api.github.com/repos/cloudinary/cloudinary_wordpress',
				'raw_url'            => 'https://api.github.com/repos/cloudinary/cloudinary_wordpress/contents',
				'github_url'         => 'https://github.com/cloudinary/cloudinary_wordpress',
				'zip_url'            => 'https://github.com/cloudinary/cloudinary_wordpress/zipball/beta/enabler-plugin',
				'sslverify'          => true,
				'requires'           => '5.7.2',
				'tested'             => '5.7.2',
				'readme'             => '.version?ref=beta/enabler-plugin',
			);

			new Updater( $config );
		}
	}

	/**
	 * Deactivated plugin notice.
	 */
	public function enable_cloudinary_notice() {
		$class   = 'notice notice-error';
		$message = __( 'It seems that Cloudinary plugin is deactivated. Please ensure it is installed and active.', 'cloudinary-beta' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Incompatible Cloudinary plugin notice.
	 */
	public function enable_cloudinary_notice_min_version_notice() {
		$class   = 'notice notice-error';
		$message = __( 'The Cloudinary Beta Enabler plugin is not compatible with the installed Cloudinary plugin. Please upgrade first the base plugin and try again. The Beta Enabler plugin is deactivated.', 'cloudinary-beta' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Inactive Beta features notice.
	 */
	public function maybe_inactive_features_notice() {
		if ( empty( $this->features ) ) {
			return;
		}

		$inactive = array();
		foreach ( $this->features as $feature => $data ) {
			if ( ! $this->is_feature_enabled( $feature ) ) {
				$inactive[] = $data['name'];
			}
		}

		if ( ! empty( $inactive ) ) {
			$class   = 'notice notice-info';
			$message = __( 'The following Cloudinary Beta features are inactive: %s.', 'cloudinary-beta' );

			printf(
				'<div class="%1$s"><p>%2$s</p></div>',
				esc_attr( $class ),
				esc_html(
					sprintf(
						$message,
						implode( ' ', $inactive )
					)
				)
			);
		}
	}

	/**
	 * Filter the beta feature.
	 *
	 * @param bool   $value   The default value.
	 * @param string $feature The beta feature.
	 * @param array  $data    The feature data.
	 *
	 * @return bool
	 */
	public function beta_features( $value, $feature, $data ) {
		$this->features[ $feature ] = $data;

		if ( $this->is_feature_enabled( $feature ) ) {
			$value = true;
		}

		return $value;
	}

	/**
	 * Add Cloudinary Beta menu to admin bar.
	 *
	 * @param WP_Admin_Bar $admin_bar
	 */
	public function admin_bar_item( $admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $this->features ) ) {
			return;
		}

		$options = array(
			'parent' => array(
				'id'    => 'cloudinary-beta',
				'title' => __( 'Cloudinary Beta', 'cloudinary-beta' ),
				'meta'  => array(
					'title' => __( 'Cloudinary Beta', 'cloudinary-beta' ),
				)
			),
		);

		foreach ( $this->features as $feature => $data ) {
			$parent = 'cloudinary-beta';
			if ( ! empty( $data['deps'] ) ) {
				$parent = "cloudinary-beta-{$data['deps'][0]}";
			}

			$options[ $feature ] = array(
				'id'     => "cloudinary-beta-{$feature}",
				'parent' => $parent,
				'title'  => sprintf(
					__( '%s [Inactive]', 'cloudinary-beta' ),
					$data['name']
				),
				'href'   => "?cloudinary-beta-{$feature}=on",
				'meta'   => array(
					'title' => sprintf(
						__( 'Activate %s', 'cloudinary-beta' ),
						$data['name']
					),
					'class' => 'cloudinary-off',
				)
			);

			$enabled = $this->is_feature_enabled( $feature );
			if ( $enabled ) {
				$options[ $feature ]['title'] = sprintf(
					__( '%s [Active]', 'cloudinary-beta' ),
					$data['name']
				);
				$options[ $feature ]['href']  = "?cloudinary-beta-{$feature}=off";
				$options[ $feature ]['meta']  = array(
					'title' => sprintf(
						__( 'Deactivate %s', 'cloudinary-beta' ),
						$data['name']
					),
					'class' => 'cloudinary-on',
				);
			}
		}

		foreach ( $options as $option ) {
			$admin_bar->add_menu( $option );
		}
	}

	/**
	 * Add basic styling.
	 */
	public function styles() {
		?>
		<style>
			.cloudinary-on a,
			.cloudinary-off a {
				position: relative;
				text-indent: 1em;
			}

			.cloudinary-on a::before,
			.cloudinary-off a::before {
				border-radius: 50%;
				bottom: 0;
				content: '';
				display: block;
				height: .4em;
				margin: auto;
				padding: 0 !important;
				position: absolute !important;
				top: 0;
				width: .4em;
			}

			.cloudinary-on a::before {
				background-color: lime;
			}

			.cloudinary-off a::before {
				background-color: red;
			}
		</style>
		<?php
	}

	/**
	 * Toggle the beta feature.
	 */
	public function maybe_toggle_feature() {
		foreach ( $this->features as $feature => $data ) {
			$update = filter_input( INPUT_GET, "cloudinary-beta-{$feature}", FILTER_SANITIZE_STRING );
			if ( ! empty( $update ) ) {
				if ( 'on' === $update ) {
					update_option( "cloudinary-beta-{$feature}", true, false );
				} else {
					delete_option( "cloudinary-beta-{$feature}" );
				}
				wp_redirect( wp_get_referer(), 302 );
				exit();
			}
		}
	}

	/**
	 * Setup class properties
	 */
	public function setup_properties() {
		$this->plugin = get_plugin_instance();
	}

	/**
	 * If beta feature enabled.
	 *
	 * @param string $feature The feature slug.
	 *
	 * @return bool
	 */
	protected function is_feature_enabled( $feature ) {
		$enabled = false;

		if ( ! empty( get_option( "cloudinary-beta-{$feature}" ) ) ) {
			$enabled = true;
		}

		return $enabled;
	}
}
