!function(t){var e={};function i(n){if(e[n])return e[n].exports;var r=e[n]={i:n,l:!1,exports:{}};return t[n].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=t,i.c=e,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(n,r,function(e){return t[e]}.bind(null,r));return n},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="",i(i.s=2)}([function(t,e,i){var n=i(3),r=i(4),o=i(5),s=i(6);t.exports=function(t){return n(t)||r(t)||o(t)||s()},t.exports.default=t.exports,t.exports.__esModule=!0},function(t,e){t.exports=function(t,e){(null==e||e>t.length)&&(e=t.length);for(var i=0,n=new Array(e);i<e;i++)n[i]=t[i];return n},t.exports.default=t.exports,t.exports.__esModule=!0},function(t,e,i){"use strict";i.r(e);var n=i(0),r=i.n(n),o={deviceDensity:window.devicePixelRatio?window.devicePixelRatio:"auto",density:null,images:[],debounce:null,config:CLDLB||{},lazyThreshold:0,_init:function(){var t=this;this._calcThreshold();var e=document.querySelectorAll("noscript[data-image]");r()(e).forEach((function(e){var i=JSON.parse(e.dataset.image),n=document.createElement("img");for(var r in i)n.setAttribute(r,i[r]);n.originalWidth=i["data-size"][0],n.originalHeight=i["data-size"][1],t.images.push(n),e.parentNode.replaceChild(n,e)})),r()(document.images).forEach((function(e){if(e.dataset.publicId){var i=e.dataset.size.split(" ");e.originalWidth=i[0],e.originalHeight=i[1],t.images.push(e)}})),window.addEventListener("resize",(function(e){t._debounceBuild()})),window.addEventListener("scroll",(function(e){t._debounceBuild()})),setTimeout((function(){return t._build()}),0)},_calcThreshold:function(){var t=this.config.lazy_threshold.replace(/[^0-9]/g,""),e=0;switch(this.config.lazy_threshold.replace(/[0-9]/g,"").toLowerCase()){case"em":e=parseFloat(getComputedStyle(document.body).fontSize)*t;break;case"rem":e=parseFloat(getComputedStyle(document.documentElement).fontSize)*t;break;case"vh":e=window.innerHeight/t*100;break;default:e=t}this.lazyThreshold=window.innerHeight+parseInt(e,10)},_debounceBuild:function(){var t=this;this.debounce&&clearTimeout(this.debounce),this.debounce=setTimeout((function(){t._build()}),100)},_getDensity:function(){if(this.density)return this.density;var t=this.config.dpr?this.config.dpr.replace("X",""):"off";if("off"===t)return this.density=1,1;var e=this.deviceDensity;return"max"!==t&&"auto"!==e&&(t=parseFloat(t),e=e>Math.ceil(t)?t:e),this.density=e,e},_build:function(){var t=this;this.images.forEach((function(e){t.buildSize(e)}))},_shouldRebuild:function(t){var e=this.scaleSize(t.originalWidth,t.width,this.config.pixel_step),i=t.getBoundingClientRect(),n="auto"!==this.density?this._getDensity():1;return i.top<this.lazyThreshold&&(e>t.naturalWidth/n||!t.cld_loaded)},_shouldPlacehold:function(t){var e=this.scaleSize(t.originalWidth,t.width,this.config.pixel_step),i=t.getBoundingClientRect(),n="auto"!==this.density?this._getDensity():1;return this.config.placeholder&&!t.cld_loaded&&i.top<2*this.lazyThreshold&&(e>t.naturalWidth/n||!t.cld_placehold)},getResponsiveSteps:function(t){return Math.ceil(t.dataset.breakpoints?t.originalWidth/t.dataset.breakpoints:this.responsiveStep)},scaleSize:function(t,e,i){var n=t-i*Math.floor((t-e)/i);return n>t?n=t:this.config.max_width<n?n=this.config.max_width:this.config.min_width>n&&(n=this.config.min_width),n},buildSize:function(t){this._shouldRebuild(t)?t.dataset.srcset?(t.cld_loaded=!0,t.srcset=t.dataset.srcset):t.src=this.getSizeURL(t):this._shouldPlacehold(t)&&(t.src=this.getPlaceholderURL(t))},getSizeURL:function(t){if(t.cld_loaded=!0,t.dataset.srcset)return t.srcset=t.dataset.srcset,delete t.dataset.srcset,"";var e=this.scaleSize(t.originalWidth,t.width,this.config.pixel_step),i=t.originalWidth/t.originalHeight,n=Math.round(e/i),r=this._getDensity(),o=t.dataset.publicId.split("/").pop(),s=[];return e&&(s.push("w_"+e),n&&(s.push("h_"+n),o+="-".concat(e,"x").concat(n)),1!==r&&s.push("dpr_"+r)),[this.config.base_url,"images",s.join(","),t.dataset.transformations,t.dataset.publicId,o].filter(this.empty).join("/")},getPlaceholderURL:function(t){t.cld_placehold=!0;var e=this.scaleSize(t.originalWidth,t.width,this.config.pixel_step),i=[];return e&&i.push("w_"+e),[this.config.base_url,"images",i.join(","),this.config.placeholder,t.dataset.publicId,"responsive"].filter(this.empty).join("/")},empty:function(t){return 0!==t.length}};window.addEventListener("load",(function(){o._init()}))},function(t,e,i){var n=i(1);t.exports=function(t){if(Array.isArray(t))return n(t)},t.exports.default=t.exports,t.exports.__esModule=!0},function(t,e){t.exports=function(t){if("undefined"!=typeof Symbol&&null!=t[Symbol.iterator]||null!=t["@@iterator"])return Array.from(t)},t.exports.default=t.exports,t.exports.__esModule=!0},function(t,e,i){var n=i(1);t.exports=function(t,e){if(t){if("string"==typeof t)return n(t,e);var i=Object.prototype.toString.call(t).slice(8,-1);return"Object"===i&&t.constructor&&(i=t.constructor.name),"Map"===i||"Set"===i?Array.from(t):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?n(t,e):void 0}},t.exports.default=t.exports,t.exports.__esModule=!0},function(t,e){t.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")},t.exports.default=t.exports,t.exports.__esModule=!0}]);