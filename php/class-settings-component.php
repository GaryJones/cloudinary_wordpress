<?php
/**
 * Cloudinary Settings Component Abstract.
 *
 * @package Cloudinary
 */

namespace Cloudinary;

use \Cloudinary\Component\Settings;
use \Cloudinary\Settings\Setting;

/**
 * Plugin Exception class.
 */
abstract class Settings_Component implements Settings {

	/**
	 * Holds the settings object for this Class.
	 *
	 * @var Setting
	 */
	protected $settings;

	/**
	 * Holds the settings slug.
	 *
	 * @var string
	 */
	protected $settings_slug;

	/**
	 * Holds the core plugin.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Component constructor.
	 *
	 * @param Plugin $plugin Global instance of the main plugin.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Init the settings object.
	 *
	 * @param Setting $setting The setting object to init onto.
	 */
	public function init_settings( $setting ) {

		if ( ! $this->settings_slug ) {
			$class               = strtolower( get_class( $this ) );
			$this->settings_slug = substr( strrchr( $class, '\\' ), 1 );
		}

		$this->settings = $setting->get_setting( $this->settings_slug );
	}

	/**
	 * Get the setting object.
	 *
	 * @return Setting
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Setup Settings.
	 */
	public function register_settings() {
		$this->settings->setup_setting( $this->settings() );
	}

	/**
	 * Returns the setting definitions.
	 *
	 * @return array
	 */
	abstract public function settings();
}
