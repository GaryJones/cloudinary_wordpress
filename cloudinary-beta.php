<?php
/**
 * Plugin Name:     Cloudinary Beta Enabler
 * Plugin URI:      https://cloudinary.com
 * Description:     This plugin enables Cloudinary features under development and that are in Beta phase.
 * Author:          Cloudinary Ltd., XWP
 * Author URI:      https://cloudinary.com/
 * Text Domain:     cloudinary-beta
 * Domain Path:     /languages
 * Version:         0.2.0
 *
 * @package         Cloudinary_Beta
 */

namespace Cloudinary;

use Cloudinary\Beta_Enabler;
use Cloudinary\Updater;

define( 'CLDN_BETA', __FILE__ );

require_once __DIR__ . '/php/class-beta-enabler.php';

new Beta_Enabler();

