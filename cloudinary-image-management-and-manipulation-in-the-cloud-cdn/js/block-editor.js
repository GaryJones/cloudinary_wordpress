/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./js/src/blocks.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./js/src/blocks.js":
/*!**************************!*\
  !*** ./js/src/blocks.js ***!
  \**************************/
/*! exports provided: cloudinaryBlocks */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "cloudinaryBlocks", function() { return cloudinaryBlocks; });
/* harmony import */ var _components_video__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/video */ "./js/src/components/video.js");
/* global window */

/**
 * Main JS.
 */
// Components
 // jQuery, because reasons.

var $ = window.$ = window.jQuery; // Global Constants

var cloudinaryBlocks = {
  Video: _components_video__WEBPACK_IMPORTED_MODULE_0__["default"]
};

/***/ }),

/***/ "./js/src/components/video.js":
/*!************************************!*\
  !*** ./js/src/components/video.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);


/* global window wp */



var Video = {
  _init: function _init() {
    if (typeof CLD_VIDEO_PLAYER !== 'undefined') {
      // Gutenberg Video Settings
      wp.hooks.addFilter('blocks.registerBlockType', 'Cloudinary/Media/Video', function (settings, name) {
        if (name === 'core/video') {
          if ('off' !== CLD_VIDEO_PLAYER.video_autoplay_mode) {
            settings.attributes.autoplay.default = true;
          }

          if ('on' === CLD_VIDEO_PLAYER.video_loop) {
            settings.attributes.loop.default = true;
          }

          if ('off' === CLD_VIDEO_PLAYER.video_controls) {
            settings.attributes.controls.default = false;
          }
        }

        return settings;
      });
    }
  }
};
/* harmony default export */ __webpack_exports__["default"] = (Video); // Init.

Video._init();

var cldAddToggle = function cldAddToggle(settings, name) {
  if ('core/image' === name || 'core/video' === name) {
    if (!settings.attributes) {
      settings.attributes = {};
    }

    settings.attributes.overwrite_transformations = {
      type: 'boolean'
    };
    settings.attributes.transformations = {
      type: 'boolean'
    };
  }

  return settings;
};

wp.hooks.addFilter('blocks.registerBlockType', 'cloudinary/addAttributes', cldAddToggle);
/**
 * Get AMP Lightbox toggle control.
 *
 * @param {Object} props Props.
 *
 * @return {Component} Element.
 */

var TransformationsToggle = function TransformationsToggle(props) {
  var _props$attributes = props.attributes,
      overwrite_transformations = _props$attributes.overwrite_transformations,
      transformations = _props$attributes.transformations,
      setAttributes = props.setAttributes;

  if (!transformations) {
    return null;
  }

  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["PanelBody"], {
    title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Transformations', 'cloudinary')
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__["ToggleControl"], {
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Overwrite Transformations', 'cloudinary'),
    checked: overwrite_transformations,
    onChange: function onChange(value) {
      setAttributes({
        overwrite_transformations: value
      });
    }
  }));
};

var cldFilterBlocksEdit = function cldFilterBlocksEdit(BlockEdit) {
  var EnhancedBlockEdit = function EnhancedBlockEdit(props) {
    var name = props.name;
    var inspectorControls;

    if ('core/image' === name || 'core/video' === name) {
      inspectorControls = cldImageInspectorControls(props);
    }

    return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, inspectorControls, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(BlockEdit, props));
  };

  return EnhancedBlockEdit;
};

var cldImageInspectorControls = function cldImageInspectorControls(props) {
  var id = props.attributes.id,
      setAttributes = props.setAttributes,
      isSelected = props.isSelected;

  if (!isSelected || !id) {
    return null;
  }

  var media = wp.data.select('core').getMedia(id);
  var InspectorControls = wp.editor.InspectorControls;

  if (media && media.transformations) {
    setAttributes({
      transformations: true
    });
  }

  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(InspectorControls, null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(TransformationsToggle, props));
};

wp.hooks.addFilter('editor.BlockEdit', 'cloudinary/filterEdit', cldFilterBlocksEdit, 20);

var cldfilterBlocksSave = function cldfilterBlocksSave(element, blockType, attributes) {
  if ('core/image' === blockType.name && attributes.overwrite_transformations) {
    var children = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["cloneElement"])(element.props.children);
    var classname = children.props.children[0].props.className ? children.props.children[0].props.className : '';
    var child = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["cloneElement"])(children.props.children[0], {
      className: classname + ' cld-overwrite'
    });
    var neChildren = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["cloneElement"])(children, {
      children: [child, false]
    });
    return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["cloneElement"])(element, {
      children: neChildren
    });
  }

  if ('core/video' === blockType.name && attributes.overwrite_transformations) {
    var _children = Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["cloneElement"])(element.props.children[0], {
      className: ' cld-overwrite'
    });

    return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["cloneElement"])(element, {
      children: _children
    });
  }

  return element;
};

wp.hooks.addFilter('blocks.getSaveElement', 'cloudinary/filterSave', cldfilterBlocksSave);

/***/ }),

/***/ "@wordpress/components":
/*!*********************************************!*\
  !*** external {"this":["wp","components"]} ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["components"]; }());

/***/ }),

/***/ "@wordpress/element":
/*!******************************************!*\
  !*** external {"this":["wp","element"]} ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["element"]; }());

/***/ }),

/***/ "@wordpress/i18n":
/*!***************************************!*\
  !*** external {"this":["wp","i18n"]} ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = this["wp"]["i18n"]; }());

/***/ })

/******/ });
//# sourceMappingURL=block-editor.js.map