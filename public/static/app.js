(self["webpackChunksolidtrack"] = self["webpackChunksolidtrack"] || []).push([["app"],{

/***/ "./assets/controllers sync recursive ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js! \\.[jt]sx?$"
/*!****************************************************************************************************************!*\
  !*** ./assets/controllers/ sync ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js! \.[jt]sx?$ ***!
  \****************************************************************************************************************/
(module, __unused_webpack_exports, __webpack_require__) {

var map = {
	"./activity_controller.js": "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/activity_controller.js",
	"./hello_controller.js": "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/hello_controller.js",
	"./time_tracker_controller.ts": "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/time_tracker_controller.ts",
	"./time_tracker_form_controller.ts": "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/time_tracker_form_controller.ts"
};


function webpackContext(req) {
	var id = webpackContextResolve(req);
	return __webpack_require__(id);
}
function webpackContextResolve(req) {
	if(!__webpack_require__.o(map, req)) {
		var e = new Error("Cannot find module '" + req + "'");
		e.code = 'MODULE_NOT_FOUND';
		throw e;
	}
	return map[req];
}
webpackContext.keys = function webpackContextKeys() {
	return Object.keys(map);
};
webpackContext.resolve = webpackContextResolve;
module.exports = webpackContext;
webpackContext.id = "./assets/controllers sync recursive ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js! \\.[jt]sx?$";

/***/ },

/***/ "./node_modules/@symfony/stimulus-bridge/dist/webpack/loader.js!./assets/controllers.json"
/*!************************************************************************************************!*\
  !*** ./node_modules/@symfony/stimulus-bridge/dist/webpack/loader.js!./assets/controllers.json ***!
  \************************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _solidworx_platform_controllers_csrf_protection_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @solidworx/platform/controllers/csrf_protection.js */ "./node_modules/@solidworx/platform/controllers/csrf_protection.js");
/* harmony import */ var _symfony_ux_autocomplete_dist_controller_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @symfony/ux-autocomplete/dist/controller.js */ "./node_modules/@symfony/ux-autocomplete/dist/controller.js");
/* harmony import */ var tom_select_dist_css_tom_select_default_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tom-select/dist/css/tom-select.default.css */ "./node_modules/tom-select/dist/css/tom-select.default.css");
/* harmony import */ var tom_select_dist_css_tom_select_bootstrap5_css__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tom-select/dist/css/tom-select.bootstrap5.css */ "./node_modules/tom-select/dist/css/tom-select.bootstrap5.css");
/* harmony import */ var _symfony_ux_chartjs_dist_controller_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @symfony/ux-chartjs/dist/controller.js */ "./node_modules/@symfony/ux-chartjs/dist/controller.js");
/* harmony import */ var _symfony_ux_live_component_dist_live_controller_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @symfony/ux-live-component/dist/live_controller.js */ "./node_modules/@symfony/ux-live-component/dist/live_controller.js");
/* harmony import */ var _symfony_ux_live_component_dist_live_min_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @symfony/ux-live-component/dist/live.min.css */ "./node_modules/@symfony/ux-live-component/dist/live.min.css");
/* harmony import */ var _symfony_ux_turbo_dist_turbo_controller_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @symfony/ux-turbo/dist/turbo_controller.js */ "./node_modules/@symfony/ux-turbo/dist/turbo_controller.js");
/* harmony import */ var _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");









/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  'solidworx--platform--modal': class extends _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_8__.Controller {
      constructor(context) {
          super(context);
          this.__stimulusLazyController = true;
      }
      initialize() {
          if (this.application.controllers.find((controller) => {
              return controller.identifier === this.identifier && controller.__stimulusLazyController;
          })) {
              return;
          }
          Promise.all(/*! import() */[__webpack_require__.e("vendors-node_modules_jquery_dist_jquery_js"), __webpack_require__.e("vendors-node_modules_solidworx_platform_controllers_modal_js")]).then(__webpack_require__.bind(__webpack_require__, /*! @solidworx/platform/controllers/modal.js */ "./node_modules/@solidworx/platform/controllers/modal.js")).then((controller) => {
              this.application.register(this.identifier, controller.default);
          });
      }
  },
  'solidworx--platform--csrf-protection': _solidworx_platform_controllers_csrf_protection_js__WEBPACK_IMPORTED_MODULE_0__["default"],
  'solidworx--platform--loading': class extends _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_8__.Controller {
      constructor(context) {
          super(context);
          this.__stimulusLazyController = true;
      }
      initialize() {
          if (this.application.controllers.find((controller) => {
              return controller.identifier === this.identifier && controller.__stimulusLazyController;
          })) {
              return;
          }
          __webpack_require__.e(/*! import() */ "node_modules_solidworx_platform_controllers_loading_js").then(__webpack_require__.bind(__webpack_require__, /*! @solidworx/platform/controllers/loading.js */ "./node_modules/@solidworx/platform/controllers/loading.js")).then((controller) => {
              this.application.register(this.identifier, controller.default);
          });
      }
  },
  'symfony--ux-autocomplete--autocomplete': _symfony_ux_autocomplete_dist_controller_js__WEBPACK_IMPORTED_MODULE_1__["default"],
  'symfony--ux-chartjs--chart': _symfony_ux_chartjs_dist_controller_js__WEBPACK_IMPORTED_MODULE_4__["default"],
  'live': _symfony_ux_live_component_dist_live_controller_js__WEBPACK_IMPORTED_MODULE_5__["default"],
  'symfony--ux-turbo--turbo-core': _symfony_ux_turbo_dist_turbo_controller_js__WEBPACK_IMPORTED_MODULE_7__["default"],
});

/***/ },

/***/ "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/activity_controller.js"
/*!*********************************************************************************************************************!*\
  !*** ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/activity_controller.js ***!
  \*********************************************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _default)
/* harmony export */ });
/* harmony import */ var _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }

var _default = /*#__PURE__*/function (_Controller) {
  function _default() {
    _classCallCheck(this, _default);
    return _callSuper(this, _default, arguments);
  }
  _inherits(_default, _Controller);
  return _createClass(_default, [{
    key: "connect",
    value: function connect() {
      this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
      this.element.addEventListener('chartjs:connect', this._onConnect);
    }
  }, {
    key: "disconnect",
    value: function disconnect() {
      // You should always remove listeners when the controller is disconnected to avoid side effects
      this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
      this.element.removeEventListener('chartjs:connect', this._onConnect);
    }
  }, {
    key: "_onPreConnect",
    value: function _onPreConnect(event) {
      // The chart is not yet created
      // You can access the config that will be passed to "new Chart()"
      console.log(event.detail.config);

      // For instance you can format Y axis
      // To avoid overriding existing config, you should distinguish 3 cases:
      // # 1. No existing scales config => add a new scales config
      event.detail.config.options.scales = {
        y: {
          ticks: {
            callback: function callback(value, index, values) {
              /* ... */
              console.log({
                value: value,
                index: index,
                values: values
              });
            }
          }
        }
      };
      // # 2. Existing scales config without Y axis config => add new Y axis config
      event.detail.config.options.scales.y = {
        ticks: {
          callback: function callback(value, index, values) {
            /* ... */
            console.log({
              value: value,
              index: index,
              values: values
            });
          }
        }
      };
      // # 3. Existing Y axis config => update it
      event.detail.config.options.scales.y.ticks = {
        callback: function callback(value, index, values) {
          /* ... */
          console.log({
            value: value,
            index: index,
            values: values
          });
        }
      };
    }
  }, {
    key: "_onConnect",
    value: function _onConnect(event) {
      // The chart was just created
      console.log(event.detail.chart); // You can access the chart instance using the event details

      // For instance you can listen to additional events
      event.detail.chart.options.onHover = function (mouseEvent) {
        /* ... */
      };
      event.detail.chart.options.onClick = function (mouseEvent) {
        /* ... */
      };
    }
  }]);
}(_hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__.Controller);


/***/ },

/***/ "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/hello_controller.js"
/*!******************************************************************************************************************!*\
  !*** ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/hello_controller.js ***!
  \******************************************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _default)
/* harmony export */ });
/* harmony import */ var _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }


/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
var _default = /*#__PURE__*/function (_Controller) {
  function _default() {
    _classCallCheck(this, _default);
    return _callSuper(this, _default, arguments);
  }
  _inherits(_default, _Controller);
  return _createClass(_default, [{
    key: "connect",
    value: function connect() {
      this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
    }
  }]);
}(_hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__.Controller);


/***/ },

/***/ "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/time_tracker_controller.ts"
/*!*************************************************************************************************************************!*\
  !*** ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/time_tracker_controller.ts ***!
  \*************************************************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ controller)
/* harmony export */ });
/* harmony import */ var _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");

const controller = class extends _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__.Controller {
    constructor(context) {
        super(context);
        this.__stimulusLazyController = true;
    }
    initialize() {
        if (this.application.controllers.find((controller) => {
            return controller.identifier === this.identifier && controller.__stimulusLazyController;
        })) {
            return;
        }
        __webpack_require__.e(/*! import() */ "assets_controllers_time_tracker_controller_ts").then(__webpack_require__.bind(__webpack_require__, /*! ./assets/controllers/time_tracker_controller.ts */ "./assets/controllers/time_tracker_controller.ts")).then((controller) => {
            this.application.register(this.identifier, controller.default);
        });
    }
};


/***/ },

/***/ "./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/time_tracker_form_controller.ts"
/*!******************************************************************************************************************************!*\
  !*** ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js!./assets/controllers/time_tracker_form_controller.ts ***!
  \******************************************************************************************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ controller)
/* harmony export */ });
/* harmony import */ var _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");

const controller = class extends _hotwired_stimulus__WEBPACK_IMPORTED_MODULE_0__.Controller {
    constructor(context) {
        super(context);
        this.__stimulusLazyController = true;
    }
    initialize() {
        if (this.application.controllers.find((controller) => {
            return controller.identifier === this.identifier && controller.__stimulusLazyController;
        })) {
            return;
        }
        __webpack_require__.e(/*! import() */ "assets_controllers_time_tracker_form_controller_ts").then(__webpack_require__.bind(__webpack_require__, /*! ./assets/controllers/time_tracker_form_controller.ts */ "./assets/controllers/time_tracker_form_controller.ts")).then((controller) => {
            this.application.register(this.identifier, controller.default);
        });
    }
};


/***/ },

/***/ "./assets/app.js"
/*!***********************!*\
  !*** ./assets/app.js ***!
  \***********************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   app: () => (/* binding */ app)
/* harmony export */ });
/* harmony import */ var _symfony_stimulus_bridge__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @symfony/stimulus-bridge */ "./node_modules/@symfony/stimulus-bridge/dist/index.js");
/* harmony import */ var _styles_app_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles/app.scss */ "./assets/styles/app.scss");



// Platform UI ships its own Stimulus app via the `_platform_ui` Encore entry; this
// second app registers SolidTrack-local controllers (assets/controllers/*) on top.
var app = (0,_symfony_stimulus_bridge__WEBPACK_IMPORTED_MODULE_0__.startStimulusApp)(__webpack_require__("./assets/controllers sync recursive ./node_modules/@symfony/stimulus-bridge/lazy-controller-loader.js! \\.[jt]sx?$"));

/***/ },

/***/ "./assets/styles/app.scss"
/*!********************************!*\
  !*** ./assets/styles/app.scss ***!
  \********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_symfony_stimulus-bridge_dist_index_js-node_modules_symfony_ux-live-compo-378519"], () => (__webpack_exec__("./assets/app.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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UUFBQSwwQkFBQUMsV0FBQTtFQUFBLFNBQUFELFNBQUE7SUFBQUUsZUFBQSxPQUFBRixRQUFBO0lBQUEsT0FBQUcsVUFBQSxPQUFBSCxRQUFBLEVBQUFJLFNBQUE7RUFBQTtFQUFBQyxTQUFBLENBQUFMLFFBQUEsRUFBQUMsV0FBQTtFQUFBLE9BQUFLLFlBQUEsQ0FBQU4sUUFBQTtJQUFBTyxHQUFBO0lBQUFDLEtBQUEsRUFVSSxTQUFBQyxPQUFPQSxDQUFBLEVBQUc7TUFDTixJQUFJLENBQUNDLE9BQU8sQ0FBQ3VCLFdBQVcsR0FBRyxtRUFBbUU7SUFDbEc7RUFBQztBQUFBLEVBSHdCbEMsMERBQVU7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDWFM7QUFDaEQsaUNBQWlDLDBEQUFVO0FBQzNDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQSxRQUFRLG9QQUFtRztBQUMzRztBQUNBLFNBQVM7QUFDVDtBQUNBOzs7Ozs7Ozs7Ozs7Ozs7OztBQ2hCZ0Q7QUFDaEQsaUNBQWlDLDBEQUFVO0FBQzNDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsU0FBUztBQUNUO0FBQ0E7QUFDQSxRQUFRLG1RQUF3RztBQUNoSDtBQUNBLFNBQVM7QUFDVDtBQUNBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7QUNoQjREO0FBQ2pDOztBQUUzQjtBQUNBO0FBQ08sSUFBTW9DLEdBQUcsR0FBR0QsMEVBQWdCLENBQUNFLHlJQUluQyxDQUFDLEM7Ozs7Ozs7Ozs7OztBQ1RGIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vc29saWR0cmFjay8gXFwuW2p0XXN4Iiwid2VicGFjazovL3NvbGlkdHJhY2svLi9hc3NldHMvY29udHJvbGxlcnMuanNvbiIsIndlYnBhY2s6Ly9zb2xpZHRyYWNrLy4vYXNzZXRzL2NvbnRyb2xsZXJzL2FjdGl2aXR5X2NvbnRyb2xsZXIuanMiLCJ3ZWJwYWNrOi8vc29saWR0cmFjay8uL2Fzc2V0cy9jb250cm9sbGVycy9oZWxsb19jb250cm9sbGVyLmpzIiwid2VicGFjazovL3NvbGlkdHJhY2svLi9hc3NldHMvY29udHJvbGxlcnMvdGltZV90cmFja2VyX2NvbnRyb2xsZXIudHM/ZDRkMyIsIndlYnBhY2s6Ly9zb2xpZHRyYWNrLy4vYXNzZXRzL2NvbnRyb2xsZXJzL3RpbWVfdHJhY2tlcl9mb3JtX2NvbnRyb2xsZXIudHM/OGYyNiIsIndlYnBhY2s6Ly9zb2xpZHRyYWNrLy4vYXNzZXRzL2FwcC5qcyIsIndlYnBhY2s6Ly9zb2xpZHRyYWNrLy4vYXNzZXRzL3N0eWxlcy9hcHAuc2Nzcz84ZjU5Il0sInNvdXJjZXNDb250ZW50IjpbInZhciBtYXAgPSB7XG5cdFwiLi9hY3Rpdml0eV9jb250cm9sbGVyLmpzXCI6IFwiLi9ub2RlX21vZHVsZXMvQHN5bWZvbnkvc3RpbXVsdXMtYnJpZGdlL2xhenktY29udHJvbGxlci1sb2FkZXIuanMhLi9hc3NldHMvY29udHJvbGxlcnMvYWN0aXZpdHlfY29udHJvbGxlci5qc1wiLFxuXHRcIi4vaGVsbG9fY29udHJvbGxlci5qc1wiOiBcIi4vbm9kZV9tb2R1bGVzL0BzeW1mb255L3N0aW11bHVzLWJyaWRnZS9sYXp5LWNvbnRyb2xsZXItbG9hZGVyLmpzIS4vYXNzZXRzL2NvbnRyb2xsZXJzL2hlbGxvX2NvbnRyb2xsZXIuanNcIixcblx0XCIuL3RpbWVfdHJhY2tlcl9jb250cm9sbGVyLnRzXCI6IFwiLi9ub2RlX21vZHVsZXMvQHN5bWZvbnkvc3RpbXVsdXMtYnJpZGdlL2xhenktY29udHJvbGxlci1sb2FkZXIuanMhLi9hc3NldHMvY29udHJvbGxlcnMvdGltZV90cmFja2VyX2NvbnRyb2xsZXIudHNcIixcblx0XCIuL3RpbWVfdHJhY2tlcl9mb3JtX2NvbnRyb2xsZXIudHNcIjogXCIuL25vZGVfbW9kdWxlcy9Ac3ltZm9ueS9zdGltdWx1cy1icmlkZ2UvbGF6eS1jb250cm9sbGVyLWxvYWRlci5qcyEuL2Fzc2V0cy9jb250cm9sbGVycy90aW1lX3RyYWNrZXJfZm9ybV9jb250cm9sbGVyLnRzXCJcbn07XG5cblxuZnVuY3Rpb24gd2VicGFja0NvbnRleHQocmVxKSB7XG5cdHZhciBpZCA9IHdlYnBhY2tDb250ZXh0UmVzb2x2ZShyZXEpO1xuXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhpZCk7XG59XG5mdW5jdGlvbiB3ZWJwYWNrQ29udGV4dFJlc29sdmUocmVxKSB7XG5cdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8obWFwLCByZXEpKSB7XG5cdFx0dmFyIGUgPSBuZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiICsgcmVxICsgXCInXCIpO1xuXHRcdGUuY29kZSA9ICdNT0RVTEVfTk9UX0ZPVU5EJztcblx0XHR0aHJvdyBlO1xuXHR9XG5cdHJldHVybiBtYXBbcmVxXTtcbn1cbndlYnBhY2tDb250ZXh0LmtleXMgPSBmdW5jdGlvbiB3ZWJwYWNrQ29udGV4dEtleXMoKSB7XG5cdHJldHVybiBPYmplY3Qua2V5cyhtYXApO1xufTtcbndlYnBhY2tDb250ZXh0LnJlc29sdmUgPSB3ZWJwYWNrQ29udGV4dFJlc29sdmU7XG5tb2R1bGUuZXhwb3J0cyA9IHdlYnBhY2tDb250ZXh0O1xud2VicGFja0NvbnRleHQuaWQgPSBcIi4vYXNzZXRzL2NvbnRyb2xsZXJzIHN5bmMgcmVjdXJzaXZlIC4vbm9kZV9tb2R1bGVzL0BzeW1mb255L3N0aW11bHVzLWJyaWRnZS9sYXp5LWNvbnRyb2xsZXItbG9hZGVyLmpzISBcXFxcLltqdF1zeD8kXCI7IiwiaW1wb3J0IGNvbnRyb2xsZXJfMCBmcm9tICdAc29saWR3b3J4L3BsYXRmb3JtL2NvbnRyb2xsZXJzL2NzcmZfcHJvdGVjdGlvbi5qcyc7XG5pbXBvcnQgY29udHJvbGxlcl8xIGZyb20gJ0BzeW1mb255L3V4LWF1dG9jb21wbGV0ZS9kaXN0L2NvbnRyb2xsZXIuanMnO1xuaW1wb3J0ICd0b20tc2VsZWN0L2Rpc3QvY3NzL3RvbS1zZWxlY3QuZGVmYXVsdC5jc3MnO1xuaW1wb3J0ICd0b20tc2VsZWN0L2Rpc3QvY3NzL3RvbS1zZWxlY3QuYm9vdHN0cmFwNS5jc3MnO1xuaW1wb3J0IGNvbnRyb2xsZXJfMiBmcm9tICdAc3ltZm9ueS91eC1jaGFydGpzL2Rpc3QvY29udHJvbGxlci5qcyc7XG5pbXBvcnQgY29udHJvbGxlcl8zIGZyb20gJ0BzeW1mb255L3V4LWxpdmUtY29tcG9uZW50L2Rpc3QvbGl2ZV9jb250cm9sbGVyLmpzJztcbmltcG9ydCAnQHN5bWZvbnkvdXgtbGl2ZS1jb21wb25lbnQvZGlzdC9saXZlLm1pbi5jc3MnO1xuaW1wb3J0IGNvbnRyb2xsZXJfNCBmcm9tICdAc3ltZm9ueS91eC10dXJiby9kaXN0L3R1cmJvX2NvbnRyb2xsZXIuanMnO1xuaW1wb3J0IHsgQ29udHJvbGxlciB9IGZyb20gJ0Bob3R3aXJlZC9zdGltdWx1cyc7XG5leHBvcnQgZGVmYXVsdCB7XG4gICdzb2xpZHdvcngtLXBsYXRmb3JtLS1tb2RhbCc6IGNsYXNzIGV4dGVuZHMgQ29udHJvbGxlciB7XG4gICAgICBjb25zdHJ1Y3Rvcihjb250ZXh0KSB7XG4gICAgICAgICAgc3VwZXIoY29udGV4dCk7XG4gICAgICAgICAgdGhpcy5fX3N0aW11bHVzTGF6eUNvbnRyb2xsZXIgPSB0cnVlO1xuICAgICAgfVxuICAgICAgaW5pdGlhbGl6ZSgpIHtcbiAgICAgICAgICBpZiAodGhpcy5hcHBsaWNhdGlvbi5jb250cm9sbGVycy5maW5kKChjb250cm9sbGVyKSA9PiB7XG4gICAgICAgICAgICAgIHJldHVybiBjb250cm9sbGVyLmlkZW50aWZpZXIgPT09IHRoaXMuaWRlbnRpZmllciAmJiBjb250cm9sbGVyLl9fc3RpbXVsdXNMYXp5Q29udHJvbGxlcjtcbiAgICAgICAgICB9KSkge1xuICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgfVxuICAgICAgICAgIGltcG9ydCgnQHNvbGlkd29yeC9wbGF0Zm9ybS9jb250cm9sbGVycy9tb2RhbC5qcycpLnRoZW4oKGNvbnRyb2xsZXIpID0+IHtcbiAgICAgICAgICAgICAgdGhpcy5hcHBsaWNhdGlvbi5yZWdpc3Rlcih0aGlzLmlkZW50aWZpZXIsIGNvbnRyb2xsZXIuZGVmYXVsdCk7XG4gICAgICAgICAgfSk7XG4gICAgICB9XG4gIH0sXG4gICdzb2xpZHdvcngtLXBsYXRmb3JtLS1jc3JmLXByb3RlY3Rpb24nOiBjb250cm9sbGVyXzAsXG4gICdzb2xpZHdvcngtLXBsYXRmb3JtLS1sb2FkaW5nJzogY2xhc3MgZXh0ZW5kcyBDb250cm9sbGVyIHtcbiAgICAgIGNvbnN0cnVjdG9yKGNvbnRleHQpIHtcbiAgICAgICAgICBzdXBlcihjb250ZXh0KTtcbiAgICAgICAgICB0aGlzLl9fc3RpbXVsdXNMYXp5Q29udHJvbGxlciA9IHRydWU7XG4gICAgICB9XG4gICAgICBpbml0aWFsaXplKCkge1xuICAgICAgICAgIGlmICh0aGlzLmFwcGxpY2F0aW9uLmNvbnRyb2xsZXJzLmZpbmQoKGNvbnRyb2xsZXIpID0+IHtcbiAgICAgICAgICAgICAgcmV0dXJuIGNvbnRyb2xsZXIuaWRlbnRpZmllciA9PT0gdGhpcy5pZGVudGlmaWVyICYmIGNvbnRyb2xsZXIuX19zdGltdWx1c0xhenlDb250cm9sbGVyO1xuICAgICAgICAgIH0pKSB7XG4gICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICB9XG4gICAgICAgICAgaW1wb3J0KCdAc29saWR3b3J4L3BsYXRmb3JtL2NvbnRyb2xsZXJzL2xvYWRpbmcuanMnKS50aGVuKChjb250cm9sbGVyKSA9PiB7XG4gICAgICAgICAgICAgIHRoaXMuYXBwbGljYXRpb24ucmVnaXN0ZXIodGhpcy5pZGVudGlmaWVyLCBjb250cm9sbGVyLmRlZmF1bHQpO1xuICAgICAgICAgIH0pO1xuICAgICAgfVxuICB9LFxuICAnc3ltZm9ueS0tdXgtYXV0b2NvbXBsZXRlLS1hdXRvY29tcGxldGUnOiBjb250cm9sbGVyXzEsXG4gICdzeW1mb255LS11eC1jaGFydGpzLS1jaGFydCc6IGNvbnRyb2xsZXJfMixcbiAgJ2xpdmUnOiBjb250cm9sbGVyXzMsXG4gICdzeW1mb255LS11eC10dXJiby0tdHVyYm8tY29yZSc6IGNvbnRyb2xsZXJfNCxcbn07IiwiaW1wb3J0IHsgQ29udHJvbGxlciB9IGZyb20gJ0Bob3R3aXJlZC9zdGltdWx1cyc7XG5cbmV4cG9ydCBkZWZhdWx0IGNsYXNzIGV4dGVuZHMgQ29udHJvbGxlciB7XG4gICAgY29ubmVjdCgpIHtcbiAgICAgICAgdGhpcy5lbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ2NoYXJ0anM6cHJlLWNvbm5lY3QnLCB0aGlzLl9vblByZUNvbm5lY3QpO1xuICAgICAgICB0aGlzLmVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2hhcnRqczpjb25uZWN0JywgdGhpcy5fb25Db25uZWN0KTtcbiAgICB9XG5cbiAgICBkaXNjb25uZWN0KCkge1xuICAgICAgICAvLyBZb3Ugc2hvdWxkIGFsd2F5cyByZW1vdmUgbGlzdGVuZXJzIHdoZW4gdGhlIGNvbnRyb2xsZXIgaXMgZGlzY29ubmVjdGVkIHRvIGF2b2lkIHNpZGUgZWZmZWN0c1xuICAgICAgICB0aGlzLmVsZW1lbnQucmVtb3ZlRXZlbnRMaXN0ZW5lcignY2hhcnRqczpwcmUtY29ubmVjdCcsIHRoaXMuX29uUHJlQ29ubmVjdCk7XG4gICAgICAgIHRoaXMuZWxlbWVudC5yZW1vdmVFdmVudExpc3RlbmVyKCdjaGFydGpzOmNvbm5lY3QnLCB0aGlzLl9vbkNvbm5lY3QpO1xuICAgIH1cblxuICAgIF9vblByZUNvbm5lY3QoZXZlbnQpIHtcbiAgICAgICAgLy8gVGhlIGNoYXJ0IGlzIG5vdCB5ZXQgY3JlYXRlZFxuICAgICAgICAvLyBZb3UgY2FuIGFjY2VzcyB0aGUgY29uZmlnIHRoYXQgd2lsbCBiZSBwYXNzZWQgdG8gXCJuZXcgQ2hhcnQoKVwiXG4gICAgICAgIGNvbnNvbGUubG9nKGV2ZW50LmRldGFpbC5jb25maWcpO1xuXG4gICAgICAgIC8vIEZvciBpbnN0YW5jZSB5b3UgY2FuIGZvcm1hdCBZIGF4aXNcbiAgICAgICAgLy8gVG8gYXZvaWQgb3ZlcnJpZGluZyBleGlzdGluZyBjb25maWcsIHlvdSBzaG91bGQgZGlzdGluZ3Vpc2ggMyBjYXNlczpcbiAgICAgICAgLy8gIyAxLiBObyBleGlzdGluZyBzY2FsZXMgY29uZmlnID0+IGFkZCBhIG5ldyBzY2FsZXMgY29uZmlnXG4gICAgICAgIGV2ZW50LmRldGFpbC5jb25maWcub3B0aW9ucy5zY2FsZXMgPSB7XG4gICAgICAgICAgICB5OiB7XG4gICAgICAgICAgICAgICAgdGlja3M6IHtcbiAgICAgICAgICAgICAgICAgICAgY2FsbGJhY2s6IGZ1bmN0aW9uICh2YWx1ZSwgaW5kZXgsIHZhbHVlcykge1xuICAgICAgICAgICAgICAgICAgICAgICAgLyogLi4uICovXG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyh7dmFsdWUsIGluZGV4LCB2YWx1ZXN9KVxuICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB9LFxuICAgICAgICB9O1xuICAgICAgICAvLyAjIDIuIEV4aXN0aW5nIHNjYWxlcyBjb25maWcgd2l0aG91dCBZIGF4aXMgY29uZmlnID0+IGFkZCBuZXcgWSBheGlzIGNvbmZpZ1xuICAgICAgICBldmVudC5kZXRhaWwuY29uZmlnLm9wdGlvbnMuc2NhbGVzLnkgPSB7XG4gICAgICAgICAgICB0aWNrczoge1xuICAgICAgICAgICAgICAgIGNhbGxiYWNrOiBmdW5jdGlvbiAodmFsdWUsIGluZGV4LCB2YWx1ZXMpIHtcbiAgICAgICAgICAgICAgICAgICAgLyogLi4uICovXG4gICAgICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKHt2YWx1ZSwgaW5kZXgsIHZhbHVlc30pXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIH0sXG4gICAgICAgIH07XG4gICAgICAgIC8vICMgMy4gRXhpc3RpbmcgWSBheGlzIGNvbmZpZyA9PiB1cGRhdGUgaXRcbiAgICAgICAgZXZlbnQuZGV0YWlsLmNvbmZpZy5vcHRpb25zLnNjYWxlcy55LnRpY2tzID0ge1xuICAgICAgICAgICAgY2FsbGJhY2s6IGZ1bmN0aW9uICh2YWx1ZSwgaW5kZXgsIHZhbHVlcykge1xuICAgICAgICAgICAgICAgIC8qIC4uLiAqL1xuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKHt2YWx1ZSwgaW5kZXgsIHZhbHVlc30pXG4gICAgICAgICAgICB9LFxuICAgICAgICB9O1xuICAgIH1cblxuICAgIF9vbkNvbm5lY3QoZXZlbnQpIHtcbiAgICAgICAgLy8gVGhlIGNoYXJ0IHdhcyBqdXN0IGNyZWF0ZWRcbiAgICAgICAgY29uc29sZS5sb2coZXZlbnQuZGV0YWlsLmNoYXJ0KTsgLy8gWW91IGNhbiBhY2Nlc3MgdGhlIGNoYXJ0IGluc3RhbmNlIHVzaW5nIHRoZSBldmVudCBkZXRhaWxzXG5cbiAgICAgICAgLy8gRm9yIGluc3RhbmNlIHlvdSBjYW4gbGlzdGVuIHRvIGFkZGl0aW9uYWwgZXZlbnRzXG4gICAgICAgIGV2ZW50LmRldGFpbC5jaGFydC5vcHRpb25zLm9uSG92ZXIgPSAobW91c2VFdmVudCkgPT4ge1xuICAgICAgICAgICAgLyogLi4uICovXG4gICAgICAgIH07XG4gICAgICAgIGV2ZW50LmRldGFpbC5jaGFydC5vcHRpb25zLm9uQ2xpY2sgPSAobW91c2VFdmVudCkgPT4ge1xuICAgICAgICAgICAgLyogLi4uICovXG4gICAgICAgIH07XG4gICAgfVxufVxuIiwiaW1wb3J0IHsgQ29udHJvbGxlciB9IGZyb20gJ0Bob3R3aXJlZC9zdGltdWx1cyc7XG5cbi8qXG4gKiBUaGlzIGlzIGFuIGV4YW1wbGUgU3RpbXVsdXMgY29udHJvbGxlciFcbiAqXG4gKiBBbnkgZWxlbWVudCB3aXRoIGEgZGF0YS1jb250cm9sbGVyPVwiaGVsbG9cIiBhdHRyaWJ1dGUgd2lsbCBjYXVzZVxuICogdGhpcyBjb250cm9sbGVyIHRvIGJlIGV4ZWN1dGVkLiBUaGUgbmFtZSBcImhlbGxvXCIgY29tZXMgZnJvbSB0aGUgZmlsZW5hbWU6XG4gKiBoZWxsb19jb250cm9sbGVyLmpzIC0+IFwiaGVsbG9cIlxuICpcbiAqIERlbGV0ZSB0aGlzIGZpbGUgb3IgYWRhcHQgaXQgZm9yIHlvdXIgdXNlIVxuICovXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBleHRlbmRzIENvbnRyb2xsZXIge1xuICAgIGNvbm5lY3QoKSB7XG4gICAgICAgIHRoaXMuZWxlbWVudC50ZXh0Q29udGVudCA9ICdIZWxsbyBTdGltdWx1cyEgRWRpdCBtZSBpbiBhc3NldHMvY29udHJvbGxlcnMvaGVsbG9fY29udHJvbGxlci5qcyc7XG4gICAgfVxufVxuIiwiaW1wb3J0IHsgQ29udHJvbGxlciB9IGZyb20gJ0Bob3R3aXJlZC9zdGltdWx1cyc7XG5jb25zdCBjb250cm9sbGVyID0gY2xhc3MgZXh0ZW5kcyBDb250cm9sbGVyIHtcbiAgICBjb25zdHJ1Y3Rvcihjb250ZXh0KSB7XG4gICAgICAgIHN1cGVyKGNvbnRleHQpO1xuICAgICAgICB0aGlzLl9fc3RpbXVsdXNMYXp5Q29udHJvbGxlciA9IHRydWU7XG4gICAgfVxuICAgIGluaXRpYWxpemUoKSB7XG4gICAgICAgIGlmICh0aGlzLmFwcGxpY2F0aW9uLmNvbnRyb2xsZXJzLmZpbmQoKGNvbnRyb2xsZXIpID0+IHtcbiAgICAgICAgICAgIHJldHVybiBjb250cm9sbGVyLmlkZW50aWZpZXIgPT09IHRoaXMuaWRlbnRpZmllciAmJiBjb250cm9sbGVyLl9fc3RpbXVsdXNMYXp5Q29udHJvbGxlcjtcbiAgICAgICAgfSkpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgICBpbXBvcnQoJy9Vc2Vycy9waWVycmUvcHJvamVjdHMvU29saWRXb3J4L1NvbGlkVHJhY2svYXNzZXRzL2NvbnRyb2xsZXJzL3RpbWVfdHJhY2tlcl9jb250cm9sbGVyLnRzJykudGhlbigoY29udHJvbGxlcikgPT4ge1xuICAgICAgICAgICAgdGhpcy5hcHBsaWNhdGlvbi5yZWdpc3Rlcih0aGlzLmlkZW50aWZpZXIsIGNvbnRyb2xsZXIuZGVmYXVsdCk7XG4gICAgICAgIH0pO1xuICAgIH1cbn07XG5leHBvcnQgeyBjb250cm9sbGVyIGFzIGRlZmF1bHQgfTsiLCJpbXBvcnQgeyBDb250cm9sbGVyIH0gZnJvbSAnQGhvdHdpcmVkL3N0aW11bHVzJztcbmNvbnN0IGNvbnRyb2xsZXIgPSBjbGFzcyBleHRlbmRzIENvbnRyb2xsZXIge1xuICAgIGNvbnN0cnVjdG9yKGNvbnRleHQpIHtcbiAgICAgICAgc3VwZXIoY29udGV4dCk7XG4gICAgICAgIHRoaXMuX19zdGltdWx1c0xhenlDb250cm9sbGVyID0gdHJ1ZTtcbiAgICB9XG4gICAgaW5pdGlhbGl6ZSgpIHtcbiAgICAgICAgaWYgKHRoaXMuYXBwbGljYXRpb24uY29udHJvbGxlcnMuZmluZCgoY29udHJvbGxlcikgPT4ge1xuICAgICAgICAgICAgcmV0dXJuIGNvbnRyb2xsZXIuaWRlbnRpZmllciA9PT0gdGhpcy5pZGVudGlmaWVyICYmIGNvbnRyb2xsZXIuX19zdGltdWx1c0xhenlDb250cm9sbGVyO1xuICAgICAgICB9KSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIGltcG9ydCgnL1VzZXJzL3BpZXJyZS9wcm9qZWN0cy9Tb2xpZFdvcngvU29saWRUcmFjay9hc3NldHMvY29udHJvbGxlcnMvdGltZV90cmFja2VyX2Zvcm1fY29udHJvbGxlci50cycpLnRoZW4oKGNvbnRyb2xsZXIpID0+IHtcbiAgICAgICAgICAgIHRoaXMuYXBwbGljYXRpb24ucmVnaXN0ZXIodGhpcy5pZGVudGlmaWVyLCBjb250cm9sbGVyLmRlZmF1bHQpO1xuICAgICAgICB9KTtcbiAgICB9XG59O1xuZXhwb3J0IHsgY29udHJvbGxlciBhcyBkZWZhdWx0IH07IiwiaW1wb3J0IHsgc3RhcnRTdGltdWx1c0FwcCB9IGZyb20gJ0BzeW1mb255L3N0aW11bHVzLWJyaWRnZSc7XG5pbXBvcnQgJy4vc3R5bGVzL2FwcC5zY3NzJztcblxuLy8gUGxhdGZvcm0gVUkgc2hpcHMgaXRzIG93biBTdGltdWx1cyBhcHAgdmlhIHRoZSBgX3BsYXRmb3JtX3VpYCBFbmNvcmUgZW50cnk7IHRoaXNcbi8vIHNlY29uZCBhcHAgcmVnaXN0ZXJzIFNvbGlkVHJhY2stbG9jYWwgY29udHJvbGxlcnMgKGFzc2V0cy9jb250cm9sbGVycy8qKSBvbiB0b3AuXG5leHBvcnQgY29uc3QgYXBwID0gc3RhcnRTdGltdWx1c0FwcChyZXF1aXJlLmNvbnRleHQoXG4gICAgJ0BzeW1mb255L3N0aW11bHVzLWJyaWRnZS9sYXp5LWNvbnRyb2xsZXItbG9hZGVyIS4vY29udHJvbGxlcnMnLFxuICAgIHRydWUsXG4gICAgL1xcLltqdF1zeD8kLyxcbikpO1xuIiwiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sIm5hbWVzIjpbIkNvbnRyb2xsZXIiLCJfZGVmYXVsdCIsIl9Db250cm9sbGVyIiwiX2NsYXNzQ2FsbENoZWNrIiwiX2NhbGxTdXBlciIsImFyZ3VtZW50cyIsIl9pbmhlcml0cyIsIl9jcmVhdGVDbGFzcyIsImtleSIsInZhbHVlIiwiY29ubmVjdCIsImVsZW1lbnQiLCJhZGRFdmVudExpc3RlbmVyIiwiX29uUHJlQ29ubmVjdCIsIl9vbkNvbm5lY3QiLCJkaXNjb25uZWN0IiwicmVtb3ZlRXZlbnRMaXN0ZW5lciIsImV2ZW50IiwiY29uc29sZSIsImxvZyIsImRldGFpbCIsImNvbmZpZyIsIm9wdGlvbnMiLCJzY2FsZXMiLCJ5IiwidGlja3MiLCJjYWxsYmFjayIsImluZGV4IiwidmFsdWVzIiwiY2hhcnQiLCJvbkhvdmVyIiwibW91c2VFdmVudCIsIm9uQ2xpY2siLCJkZWZhdWx0IiwidGV4dENvbnRlbnQiLCJzdGFydFN0aW11bHVzQXBwIiwiYXBwIiwicmVxdWlyZSIsImNvbnRleHQiXSwic291cmNlUm9vdCI6IiJ9