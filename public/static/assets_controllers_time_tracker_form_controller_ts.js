"use strict";
(self["webpackChunksolidtrack"] = self["webpackChunksolidtrack"] || []).push([["assets_controllers_time_tracker_form_controller_ts"],{

/***/ "./assets/controllers/time_tracker_form_controller.ts"
/*!************************************************************!*\
  !*** ./assets/controllers/time_tracker_form_controller.ts ***!
  \************************************************************/
(__unused_webpack_module, exports, __webpack_require__) {



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
Object.defineProperty(exports, "__esModule", ({
  value: true
}));
var stimulus_1 = __webpack_require__(/*! @hotwired/stimulus */ "./node_modules/@hotwired/stimulus/dist/stimulus.js");
// Clears the TomSelect-enhanced project field when the server emits
// `time-tracker:cleared` (after Stop). The autocomplete column is wrapped in
// `data-live-ignore` so Live's morph cannot replace its contents; this listener
// bridges that gap by talking to TomSelect's own API.
/* stimulusFetch: 'lazy' */
var default_1 = /*#__PURE__*/function (_stimulus_1$Controlle) {
  function default_1() {
    _classCallCheck(this, default_1);
    return _callSuper(this, default_1, arguments);
  }
  _inherits(default_1, _stimulus_1$Controlle);
  return _createClass(default_1, [{
    key: "clear",
    value: function clear() {
      var select = this.element.querySelector('select');
      if (!select) {
        return;
      }
      if (select.tomselect) {
        select.tomselect.clear();
        return;
      }
      select.value = '';
      select.dispatchEvent(new Event('change', {
        bubbles: true
      }));
    }
  }]);
}(stimulus_1.Controller);
exports["default"] = default_1;

/***/ }

}]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXNzZXRzX2NvbnRyb2xsZXJzX3RpbWVfdHJhY2tlcl9mb3JtX2NvbnRyb2xsZXJfdHMuanMiLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7O0FBQWE7O0FBQUEsU0FBQUEsUUFBQUMsQ0FBQSxzQ0FBQUQsT0FBQSx3QkFBQUUsTUFBQSx1QkFBQUEsTUFBQSxDQUFBQyxRQUFBLGFBQUFGLENBQUEsa0JBQUFBLENBQUEsZ0JBQUFBLENBQUEsV0FBQUEsQ0FBQSx5QkFBQUMsTUFBQSxJQUFBRCxDQUFBLENBQUFHLFdBQUEsS0FBQUYsTUFBQSxJQUFBRCxDQUFBLEtBQUFDLE1BQUEsQ0FBQUcsU0FBQSxxQkFBQUosQ0FBQSxLQUFBRCxPQUFBLENBQUFDLENBQUE7QUFBQSxTQUFBSyxnQkFBQUMsQ0FBQSxFQUFBQyxDQUFBLFVBQUFELENBQUEsWUFBQUMsQ0FBQSxhQUFBQyxTQUFBO0FBQUEsU0FBQUMsa0JBQUFDLENBQUEsRUFBQUMsQ0FBQSxhQUFBQyxDQUFBLE1BQUFBLENBQUEsR0FBQUQsQ0FBQSxDQUFBRSxNQUFBLEVBQUFELENBQUEsVUFBQVosQ0FBQSxHQUFBVyxDQUFBLENBQUFDLENBQUEsR0FBQVosQ0FBQSxDQUFBYyxVQUFBLEdBQUFkLENBQUEsQ0FBQWMsVUFBQSxRQUFBZCxDQUFBLENBQUFlLFlBQUEsa0JBQUFmLENBQUEsS0FBQUEsQ0FBQSxDQUFBZ0IsUUFBQSxRQUFBQyxNQUFBLENBQUFDLGNBQUEsQ0FBQVIsQ0FBQSxFQUFBUyxjQUFBLENBQUFuQixDQUFBLENBQUFvQixHQUFBLEdBQUFwQixDQUFBO0FBQUEsU0FBQXFCLGFBQUFYLENBQUEsRUFBQUMsQ0FBQSxFQUFBQyxDQUFBLFdBQUFELENBQUEsSUFBQUYsaUJBQUEsQ0FBQUMsQ0FBQSxDQUFBTixTQUFBLEVBQUFPLENBQUEsR0FBQUMsQ0FBQSxJQUFBSCxpQkFBQSxDQUFBQyxDQUFBLEVBQUFFLENBQUEsR0FBQUssTUFBQSxDQUFBQyxjQUFBLENBQUFSLENBQUEsaUJBQUFNLFFBQUEsU0FBQU4sQ0FB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Q0E7QUFDQTtBQUFBLElBQ01DLFNBQVMsMEJBQUFDLHFCQUFBO0VBQUEsU0FBQUQsVUFBQTtJQUFBN0MsZUFBQSxPQUFBNkMsU0FBQTtJQUFBLE9BQUF0QixVQUFBLE9BQUFzQixTQUFBLEVBQUFFLFNBQUE7RUFBQTtFQUFBVCxTQUFBLENBQUFPLFNBQUEsRUFBQUMscUJBQUE7RUFBQSxPQUFBOUIsWUFBQSxDQUFBNkIsU0FBQTtJQUFBOUIsR0FBQTtJQUFBeUIsS0FBQSxFQUNYLFNBQUFRLEtBQUtBLENBQUEsRUFBRztNQUNKLElBQU1DLE1BQU0sR0FBRyxJQUFJLENBQUNDLE9BQU8sQ0FBQ0MsYUFBYSxDQUFDLFFBQVEsQ0FBQztNQUNuRCxJQUFJLENBQUNGLE1BQU0sRUFBRTtRQUNUO01BQ0o7TUFDQSxJQUFJQSxNQUFNLENBQUNHLFNBQVMsRUFBRTtRQUNsQkgsTUFBTSxDQUFDRyxTQUFTLENBQUNKLEtBQUssQ0FBQyxDQUFDO1FBQ3hCO01BQ0o7TUFDQUMsTUFBTSxDQUFDVCxLQUFLLEdBQUcsRUFBRTtNQUNqQlMsTUFBTSxDQUFDSSxhQUFhLENBQUMsSUFBSUMsS0FBSyxDQUFDLFFBQVEsRUFBRTtRQUFFQyxPQUFPLEVBQUU7TUFBSyxDQUFDLENBQUMsQ0FBQztJQUNoRTtFQUFDO0FBQUEsRUFabUJaLFVBQVUsQ0FBQ2EsVUFBVTtBQWM3Q2Qsa0JBQWUsR0FBR0csU0FBUyxDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vc29saWR0cmFjay8uL2Fzc2V0cy9jb250cm9sbGVycy90aW1lX3RyYWNrZXJfZm9ybV9jb250cm9sbGVyLnRzIl0sInNvdXJjZXNDb250ZW50IjpbIlwidXNlIHN0cmljdFwiO1xuT2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIFwiX19lc01vZHVsZVwiLCB7IHZhbHVlOiB0cnVlIH0pO1xuY29uc3Qgc3RpbXVsdXNfMSA9IHJlcXVpcmUoXCJAaG90d2lyZWQvc3RpbXVsdXNcIik7XG4vLyBDbGVhcnMgdGhlIFRvbVNlbGVjdC1lbmhhbmNlZCBwcm9qZWN0IGZpZWxkIHdoZW4gdGhlIHNlcnZlciBlbWl0c1xuLy8gYHRpbWUtdHJhY2tlcjpjbGVhcmVkYCAoYWZ0ZXIgU3RvcCkuIFRoZSBhdXRvY29tcGxldGUgY29sdW1uIGlzIHdyYXBwZWQgaW5cbi8vIGBkYXRhLWxpdmUtaWdub3JlYCBzbyBMaXZlJ3MgbW9ycGggY2Fubm90IHJlcGxhY2UgaXRzIGNvbnRlbnRzOyB0aGlzIGxpc3RlbmVyXG4vLyBicmlkZ2VzIHRoYXQgZ2FwIGJ5IHRhbGtpbmcgdG8gVG9tU2VsZWN0J3Mgb3duIEFQSS5cbi8qIHN0aW11bHVzRmV0Y2g6ICdsYXp5JyAqL1xuY2xhc3MgZGVmYXVsdF8xIGV4dGVuZHMgc3RpbXVsdXNfMS5Db250cm9sbGVyIHtcbiAgICBjbGVhcigpIHtcbiAgICAgICAgY29uc3Qgc2VsZWN0ID0gdGhpcy5lbGVtZW50LnF1ZXJ5U2VsZWN0b3IoJ3NlbGVjdCcpO1xuICAgICAgICBpZiAoIXNlbGVjdCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIGlmIChzZWxlY3QudG9tc2VsZWN0KSB7XG4gICAgICAgICAgICBzZWxlY3QudG9tc2VsZWN0LmNsZWFyKCk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cbiAgICAgICAgc2VsZWN0LnZhbHVlID0gJyc7XG4gICAgICAgIHNlbGVjdC5kaXNwYXRjaEV2ZW50KG5ldyBFdmVudCgnY2hhbmdlJywgeyBidWJibGVzOiB0cnVlIH0pKTtcbiAgICB9XG59XG5leHBvcnRzLmRlZmF1bHQgPSBkZWZhdWx0XzE7XG4iXSwibmFtZXMiOlsiX3R5cGVvZiIsIm8iLCJTeW1ib2wiLCJpdGVyYXRvciIsImNvbnN0cnVjdG9yIiwicHJvdG90eXBlIiwiX2NsYXNzQ2FsbENoZWNrIiwiYSIsIm4iLCJUeXBlRXJyb3IiLCJfZGVmaW5lUHJvcGVydGllcyIsImUiLCJyIiwidCIsImxlbmd0aCIsImVudW1lcmFibGUiLCJjb25maWd1cmFibGUiLCJ3cml0YWJsZSIsIk9iamVjdCIsImRlZmluZVByb3BlcnR5IiwiX3RvUHJvcGVydHlLZXkiLCJrZXkiLCJfY3JlYXRlQ2xhc3MiLCJpIiwiX3RvUHJpbWl0aXZlIiwidG9QcmltaXRpdmUiLCJjYWxsIiwiU3RyaW5nIiwiTnVtYmVyIiwiX2NhbGxTdXBlciIsIl9nZXRQcm90b3R5cGVPZiIsIl9wb3NzaWJsZUNvbnN0cnVjdG9yUmV0dXJuIiwiX2lzTmF0aXZlUmVmbGVjdENvbnN0cnVjdCIsIlJlZmxlY3QiLCJjb25zdHJ1Y3QiLCJhcHBseSIsIl9hc3NlcnRUaGlzSW5pdGlhbGl6ZWQiLCJSZWZlcmVuY2VFcnJvciIsIkJvb2xlYW4iLCJ2YWx1ZU9mIiwic2V0UHJvdG90eXBlT2YiLCJnZXRQcm90b3R5cGVPZiIsImJpbmQiLCJfX3Byb3RvX18iLCJfaW5oZXJpdHMiLCJjcmVhdGUiLCJ2YWx1ZSIsIl9zZXRQcm90b3R5cGVPZiIsImV4cG9ydHMiLCJzdGltdWx1c18xIiwicmVxdWlyZSIsImRlZmF1bHRfMSIsIl9zdGltdWx1c18xJENvbnRyb2xsZSIsImFyZ3VtZW50cyIsImNsZWFyIiwic2VsZWN0IiwiZWxlbWVudCIsInF1ZXJ5U2VsZWN0b3IiLCJ0b21zZWxlY3QiLCJkaXNwYXRjaEV2ZW50IiwiRXZlbnQiLCJidWJibGVzIiwiQ29udHJvbGxlciJdLCJzb3VyY2VSb290IjoiIn0=