<?php
/**
 * Manages Gallery Widget and Block settings.
 *
 * @package Cloudinary
 */

namespace Cloudinary\Media;

use Cloudinary\Media;
use Cloudinary\REST_API;
use Cloudinary\Utils;

/**
 * Class Gallery.
 *
 * Handles gallery.
 */
class Gallery {

	/**
	 * The enqueue script handle for the gallery widget lib.
	 *
	 * @var string
	 */
	const GALLERY_LIBRARY_HANDLE = 'cld-gallery';

	/**
	 * The gallery widget lib cdn url.
	 *
	 * @var string
	 */
	const GALLERY_LIBRARY_URL = 'https://product-gallery.cloudinary.com/all.js';

	/**
	 * The default config in case no settings are saved.
	 *
	 * @var array
	 */
	public static $default_config = array(
		'mediaAssets'      => array(),
		'transition'       => 'fade',
		'aspectRatio'      => '3:4',
		'navigation'       => 'always',
		'zoom'             => true,
		'carouselLocation' => 'top',
		'carouselOffset'   => 5,
		'carouselStyle'    => 'thumbnails',
		'displayProps'     => array( 'mode' => 'classic' ),
		'indicatorProps'   => array( 'shape' => 'round' ),
		'themeProps'       => array(
			'primary'   => '#cf2e2e',
			'onPrimary' => '#000000',
			'active'    => '#777777',
		),
		'zoomProps'        => array(
			'type'           => 'popup',
			'viewerPosition' => 'bottom',
			'trigger'        => 'click',
		),
		'thumbnailProps'   => array(
			'width'                  => 64,
			'height'                 => 64,
			'navigationShape'        => 'radius',
			'selectedStyle'          => 'gradient',
			'selectedBorderPosition' => 'all',
			'selectedBorderWidth'    => 4,
			'mediaSymbolShape'       => 'round',
		),
	);

	/**
	 * Holds instance of the Media class.
	 *
	 * @var Media
	 */
	protected $media;

	/**
	 * Holds the current config.
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Init gallery.
	 *
	 * @param Media $media Media class instance.
	 */
	public function __construct( Media $media ) {
		$this->media = $media;

		$config = ! empty( $media->plugin->config['settings']['gallery'] ) ? $media->plugin->config['settings']['gallery'] : wp_json_encode( self::$default_config );

		$this->config = json_decode( $config, true );

		$this->setup_hooks();
	}

	/**
	 * Gets the gallery settings in the expected json format.
	 *
	 * @return array
	 */
	public function get_config() {
		$config = Utils::array_filter_recursive( $this->config ); // Remove empty values.

		$config['cloudName'] = $this->media->plugin->components['connect']->get_cloud_name();

		/**
		 * Filter the gallery HTML container.
		 *
		 * @param string $selector The target HTML selector.
		 */
		$config['container'] = apply_filters( 'cloudinary_gallery_html_container', '' );

		/**
		 * Filter the gallery configuration.
		 *
		 * @param array $config The current gallery config.
		 */
		return apply_filters( 'cloudinary_gallery_config', $config );
	}

	/**
	 * Register frontend assets for the gallery.
	 */
	public function enqueue_gallery_library() {
		wp_enqueue_script(
			self::GALLERY_LIBRARY_HANDLE,
			self::GALLERY_LIBRARY_URL,
			array(),
			$this->media->plugin->version,
			true
		);

		$json_config = wp_json_encode( $this->get_config() );
		wp_add_inline_script( self::GALLERY_LIBRARY_HANDLE, "var cloudinaryGalleryConfig = JSON.parse( '{$json_config}' );" );

		$post         = get_post();
		$post_content = $post ? "'" . implode( '', explode( "\n", $post->post_content ) ) . "'" : 'null';

		wp_add_inline_script( self::GALLERY_LIBRARY_HANDLE, "var cloudinaryPostContent = {$post_content};" );

		wp_enqueue_script(
			'cloudinary-gallery-init',
			$this->media->plugin->dir_url . 'js/gallery-init.js',
			array( self::GALLERY_LIBRARY_HANDLE ),
			$this->media->plugin->version,
			true
		);
	}

	/**
	 * Register blocked editor assets for the gallery.
	 */
	public function block_editor_scripts_styles() {
		$this->enqueue_gallery_library();

		wp_enqueue_style(
			'cloudinary-gallery-block-css',
			$this->media->plugin->dir_url . 'css/gallery-block.css',
			array(),
			$this->media->plugin->version
		);

		wp_enqueue_script(
			'cloudinary-gallery-block-js',
			$this->media->plugin->dir_url . 'js/gallery-block.js',
			array( 'wp-blocks', 'wp-editor', 'wp-element', self::GALLERY_LIBRARY_HANDLE ),
			$this->media->plugin->version,
			true
		);

		wp_localize_script(
			'cloudinary-gallery-block-js',
			'cloudinaryGalleryApi',
			array(
				'endpoint' => rest_url( REST_API::BASE . '/image_data' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Checks if the Cloudinary Gallery Widget is enabled.
	 *
	 * @return bool
	 */
	public function gallery_enabled() {
		return true; // @TODO: reimplement this.
	}

	/**
	 * Fetches image public id and transformations.
	 *
	 * @param array|int[]|array[] $images An array of image IDs or a multi-dimensional array with url and id keys.
	 *
	 * @return array
	 */
	public function get_image_data( array $images ) {
		$image_data = array();

		foreach ( $images as $index => $image ) {
			$image_id = is_int( $image ) ? $image : $image['id'];

			if ( ! $this->media->sync->is_synced( $image_id ) ) {
				continue;
			}

			$image_url = is_int( $image ) ? $this->media->cloudinary_url( $image_id ) : $image['url'];

			$image_data[ $index ]             = array();
			$image_data[ $index ]['publicId'] = $this->media->get_public_id( $image_id, true );

			$transformations = $this->media->get_transformations_from_string( $image_url );

			if ( $transformations ) {
				$image_data[ $index ]['transformation'] = array( 'transformation' => $transformations );
			}
		}

		return $image_data;
	}

	/**
	 * This rest endpoint handler will fetch the public_id and transformations off of a list of images.
	 *
	 * @param \WP_REST_Request $request The request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rest_cloudinary_image_data( \WP_REST_Request $request ) {
		$request_body = json_decode( $request->get_body(), true );

		if ( empty( $request_body['images'] ) ) {
			return new \WP_Error( 400, 'The "images" key must be present in the request body.' );
		}

		$image_data = $this->get_image_data( $request_body['images'] );

		return new \WP_REST_Response( $image_data );
	}

	/**
	 * Add endpoints to the \Cloudinary\REST_API::$endpoints array.
	 *
	 * @param array $endpoints Endpoints from the filter.
	 *
	 * @return array
	 */
	public function rest_endpoints( $endpoints ) {

		$endpoints['image_data'] = array(
			'method'              => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'rest_cloudinary_image_data' ),
			'args'                => array(),
			'permission_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		);

		return $endpoints;
	}

	/**
	 * Setup hooks for the gallery.
	 */
	public function setup_hooks() {
		add_filter( 'cloudinary_api_rest_endpoints', array( $this, 'rest_endpoints' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_scripts_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_gallery_library' ) );
	}
}
