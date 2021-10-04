<?php
/**
 * Optimisation Level UI Component.
 *
 * @package Cloudinary
 */

namespace Cloudinary\UI\Component;

use \Cloudinary\Settings;
use function Cloudinary\get_plugin_instance;

/**
 * Class Opt_Level
 *
 * @package Cloudinary\UI
 */
class Opt_Level extends Line_Stat {

	/**
	 * Holds the components build blueprint.
	 *
	 * @var string
	 */
	protected $blueprint = 'wrap|title|titles|used/|avail/|/titles|line/|/title|enabled/|/wrap';

	/**
	 * Holds the core plugin settings.
	 *
	 * @var Settings
	 */
	protected $plugin_settings;
	/**
	 * Holds the list of settings slugs that make up the different optimisations.
	 *
	 * @var string[]
	 */
	protected $settings_slugs = array(
		'image_settings.image_optimization',
		'video_settings.video_optimization',
		'connect.cache.enable',
		'lazy_loading.use_lazy_load',
		'responsive.enable_breakpoints',
	);

	/**
	 * Setup the component.
	 */
	public function setup() {
		$this->plugin_settings = get_plugin_instance()->settings;
		$this->setting->set_param( 'icon', 'dashicons-yes-alt' );
		parent::setup();
	}

	/**
	 * Filter the enabled part structure.
	 *
	 * @param array $struct The array structure.
	 *
	 * @return array
	 */
	protected function enabled( $struct ) {

		$struct['element'] = 'div';
		foreach ( $this->settings_slugs as $slug ) {
			$struct['children'][ $slug ] = $this->get_setting_line( $slug );
		}

		return $struct;
	}

	/**
	 * Get the setting line for the list of enabled items.
	 *
	 * @param string $slug The setting slug.
	 *
	 * @return array
	 */
	protected function get_setting_line( $slug ) {

		$setting                      = $this->plugin_settings->get_setting( $slug );
		$row                          = $this->get_part( 'div' );
		$row['attributes']['class'][] = 'cld-optimisation';
		// Get the link.
		$link                          = $this->get_part( 'a' );
		$link['attributes']['class'][] = 'cld-optimisation-item';
		$link['attributes']['href']    = $this->get_url( $slug );
		$link['content']               = $setting->get_param( 'optimisation_title', $setting->get_param( 'title' ) );
		$row['children']['link']       = $link;

		// Get the status.
		if ( 'on' === $setting->get_value() ) {
			$row['children']['active'] = $this->get_active_badge();
		}

		return $row;
	}

	/**
	 * Get an activated badge.
	 *
	 * @return array
	 */
	protected function get_active_badge() {
		$badge                          = $this->get_part( 'span' );
		$badge['attributes']['class'][] = 'cld-optimisation-item-active';
		$icon                           = $this->dashicon( $this->get_part( 'span' ) );
		$icon['render']                 = true;
		$badge['children']['icon']      = $icon;
		$text                           = $this->get_part( null );
		$text['content']                = __( 'Activated', 'cloudinary' );
		$badge['children']['text']      = $text;

		return $badge;
	}

	/**
	 * Get the url to the root settings page.
	 *
	 * @param string $slug The setting slug.
	 *
	 * @return string
	 */
	protected function get_url( $slug ) {
		$page = $this->plugin_settings->get_storage_key( strstr( $slug, $this->plugin_settings->separator, true ) );
		$url  = add_query_arg( 'page', $page, 'admin.php' );

		return self_admin_url( $url );
	}

	/**
	 * Set the end texts.
	 */
	protected function set_texts() {

		$used_percent = round( $this->used / $this->limit * 100 );
		/* translators: %s is the percentage optimised. */
		$this->used_text = sprintf( '%s optimised', $used_percent . '%' );

		$unused_percent = round( 100 - $used_percent );
		/* translators: %s is the amount available. */
		$this->avail_text = sprintf( '%s unoptimised', $unused_percent . '%' );
	}

	/**
	 * Set the usage stats.
	 */
	protected function set_stats() {
		$this->used_percent = $this->calculate_percentage();
	}

	/**
	 * Gets the title structs.
	 *
	 * @param array $struct The title struct.
	 *
	 * @return array
	 */
	protected function title( $struct ) {
		$struct['content']               = $this->setting->get_param( 'title' );
		$struct['attributes']['class'][] = 'cld-progress-header';

		return $struct;
	}

	/**
	 * Calculate the used percentage.
	 *
	 * @return float|int
	 */
	public function calculate_percentage() {

		$this->limit = count( $this->settings_slugs );
		$enabled     = 0;
		foreach ( $this->settings_slugs as $slug ) {
			if ( 'on' === $this->plugin_settings->get_value( $slug ) ) {
				$enabled ++;
			}
		}

		return $enabled / $this->limit * 100;
	}
}
