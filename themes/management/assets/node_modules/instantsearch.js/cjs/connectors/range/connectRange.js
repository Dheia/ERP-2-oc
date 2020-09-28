"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = connectRange;

var _utils = require("../../lib/utils");

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === "[object Arguments]")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var withUsage = (0, _utils.createDocumentationMessageGenerator)({
  name: 'range-input',
  connector: true
}, {
  name: 'range-slider',
  connector: true
});
var $$type = 'ais.range';
/**
 * @typedef {Object} CustomRangeWidgetOptions
 * @property {string} attribute Name of the attribute for faceting.
 * @property {number} [min = undefined] Minimal range value, default to automatically computed from the result set.
 * @property {number} [max = undefined] Maximal range value, default to automatically computed from the result set.
 * @property {number} [precision = 2] Number of digits after decimal point to use.
 */

/**
 * @typedef {Object} RangeRenderingOptions
 * @property {function(Array<number, number>)} refine Sets a range to filter the results on. Both values
 * are optional, and will default to the higher and lower bounds. You can use `undefined` to remove a
 * previously set bound or to set an infinite bound.
 * @property {{min: number, max: number}} range Results bounds without the current range filter.
 * @property {Array<number, number>} start Current numeric bounds of the search.
 * @property {{from: function, to: function}} formatter Transform for the rendering `from` and/or `to` values.
 * Both functions take a `number` as input and should output a `string`.
 * @property {Object} widgetParams All original `CustomRangeWidgetOptions` forwarded to the `renderFn`.
 */

/**
 * **Range** connector provides the logic to create custom widget that will let
 * the user refine results using a numeric range.
 *
 * This connectors provides a `refine()` function that accepts bounds. It will also provide
 * information about the min and max bounds for the current result set.
 * @type {Connector}
 * @param {function(RangeRenderingOptions, boolean)} renderFn Rendering function for the custom **Range** widget.
 * @param {function} unmountFn Unmount function called when the widget is disposed.
 * @return {function(CustomRangeWidgetOptions)} Re-usable widget factory for a custom **Range** widget.
 */

function connectRange(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : _utils.noop;
  (0, _utils.checkRendering)(renderFn, withUsage());
  return function () {
    var widgetParams = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var attribute = widgetParams.attribute,
        minBound = widgetParams.min,
        maxBound = widgetParams.max,
        _widgetParams$precisi = widgetParams.precision,
        precision = _widgetParams$precisi === void 0 ? 0 : _widgetParams$precisi;
    var hasMinBound = (0, _utils.isFiniteNumber)(minBound);
    var hasMaxBound = (0, _utils.isFiniteNumber)(maxBound);

    if (!attribute) {
      throw new Error(withUsage('The `attribute` option is required.'));
    }

    if (hasMinBound && hasMaxBound && minBound > maxBound) {
      throw new Error(withUsage("The `max` option can't be lower than `min`."));
    }

    var formatToNumber = function formatToNumber(v) {
      return Number(Number(v).toFixed(precision));
    };

    var rangeFormatter = {
      from: function from(v) {
        return v;
      },
      to: function to(v) {
        return formatToNumber(v).toLocaleString();
      }
    }; // eslint-disable-next-line complexity

    var getRefinedState = function getRefinedState(helper, currentRange, nextMin, nextMax) {
      var resolvedState = helper.state;
      var currentRangeMin = currentRange.min,
          currentRangeMax = currentRange.max;

      var _ref = resolvedState.getNumericRefinement(attribute, '>=') || [],
          _ref2 = _slicedToArray(_ref, 1),
          min = _ref2[0];

      var _ref3 = resolvedState.getNumericRefinement(attribute, '<=') || [],
          _ref4 = _slicedToArray(_ref3, 1),
          max = _ref4[0];

      var isResetMin = nextMin === undefined || nextMin === '';
      var isResetMax = nextMax === undefined || nextMax === '';
      var nextMinAsNumber = !isResetMin ? parseFloat(nextMin) : undefined;
      var nextMaxAsNumber = !isResetMax ? parseFloat(nextMax) : undefined;
      var newNextMin;

      if (!hasMinBound && currentRangeMin === nextMinAsNumber) {
        newNextMin = undefined;
      } else if (hasMinBound && isResetMin) {
        newNextMin = minBound;
      } else {
        newNextMin = nextMinAsNumber;
      }

      var newNextMax;

      if (!hasMaxBound && currentRangeMax === nextMaxAsNumber) {
        newNextMax = undefined;
      } else if (hasMaxBound && isResetMax) {
        newNextMax = maxBound;
      } else {
        newNextMax = nextMaxAsNumber;
      }

      var isResetNewNextMin = newNextMin === undefined;
      var isValidNewNextMin = (0, _utils.isFiniteNumber)(newNextMin);
      var isValidMinCurrentRange = (0, _utils.isFiniteNumber)(currentRangeMin);
      var isGreaterThanCurrentRange = isValidMinCurrentRange && currentRangeMin <= newNextMin;
      var isMinValid = isResetNewNextMin || isValidNewNextMin && (!isValidMinCurrentRange || isGreaterThanCurrentRange);
      var isResetNewNextMax = newNextMax === undefined;
      var isValidNewNextMax = (0, _utils.isFiniteNumber)(newNextMax);
      var isValidMaxCurrentRange = (0, _utils.isFiniteNumber)(currentRangeMax);
      var isLowerThanRange = isValidMaxCurrentRange && currentRangeMax >= newNextMax;
      var isMaxValid = isResetNewNextMax || isValidNewNextMax && (!isValidMaxCurrentRange || isLowerThanRange);
      var hasMinChange = min !== newNextMin;
      var hasMaxChange = max !== newNextMax;

      if ((hasMinChange || hasMaxChange) && isMinValid && isMaxValid) {
        resolvedState = resolvedState.removeNumericRefinement(attribute);

        if (isValidNewNextMin) {
          resolvedState = resolvedState.addNumericRefinement(attribute, '>=', formatToNumber(newNextMin));
        }

        if (isValidNewNextMax) {
          resolvedState = resolvedState.addNumericRefinement(attribute, '<=', formatToNumber(newNextMax));
        }

        return resolvedState;
      }

      return null;
    };

    var sendEventWithRefinedState = function sendEventWithRefinedState(refinedState, instantSearchInstance, helper) {
      var eventName = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'Filter Applied';
      var filters = (0, _utils.convertNumericRefinementsToFilters)(refinedState, attribute);

      if (filters && filters.length > 0) {
        instantSearchInstance.sendEventToInsights({
          insightsMethod: 'clickedFilters',
          widgetType: $$type,
          eventType: 'click',
          payload: {
            eventName: eventName,
            index: helper.getIndex(),
            filters: filters
          }
        });
      }
    };

    var createSendEvent = function createSendEvent(instantSearchInstance, helper, currentRange) {
      return function () {
        for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
          args[_key] = arguments[_key];
        }

        if (args.length === 1) {
          instantSearchInstance.sendEventToInsights(args[0]);
          return;
        }

        var eventType = args[0],
            facetValue = args[1],
            eventName = args[2];

        if (eventType !== 'click') {
          return;
        }

        var _facetValue = _slicedToArray(facetValue, 2),
            nextMin = _facetValue[0],
            nextMax = _facetValue[1];

        var refinedState = getRefinedState(helper, currentRange, nextMin, nextMax);
        sendEventWithRefinedState(refinedState, instantSearchInstance, helper, eventName);
      };
    };

    return {
      $$type: $$type,
      _getCurrentRange: function _getCurrentRange(stats) {
        var pow = Math.pow(10, precision);
        var min;

        if (hasMinBound) {
          min = minBound;
        } else if ((0, _utils.isFiniteNumber)(stats.min)) {
          min = stats.min;
        } else {
          min = 0;
        }

        var max;

        if (hasMaxBound) {
          max = maxBound;
        } else if ((0, _utils.isFiniteNumber)(stats.max)) {
          max = stats.max;
        } else {
          max = 0;
        }

        return {
          min: Math.floor(min * pow) / pow,
          max: Math.ceil(max * pow) / pow
        };
      },
      _getCurrentRefinement: function _getCurrentRefinement(helper) {
        var _ref5 = helper.getNumericRefinement(attribute, '>=') || [],
            _ref6 = _slicedToArray(_ref5, 1),
            minValue = _ref6[0];

        var _ref7 = helper.getNumericRefinement(attribute, '<=') || [],
            _ref8 = _slicedToArray(_ref7, 1),
            maxValue = _ref8[0];

        var min = (0, _utils.isFiniteNumber)(minValue) ? minValue : -Infinity;
        var max = (0, _utils.isFiniteNumber)(maxValue) ? maxValue : Infinity;
        return [min, max];
      },
      _refine: function _refine(instantSearchInstance, helper, currentRange) {
        // eslint-disable-next-line complexity
        return function () {
          var _ref9 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [],
              _ref10 = _slicedToArray(_ref9, 2),
              nextMin = _ref10[0],
              nextMax = _ref10[1];

          var refinedState = getRefinedState(helper, currentRange, nextMin, nextMax);

          if (refinedState) {
            sendEventWithRefinedState(refinedState, instantSearchInstance, helper);
            helper.setState(refinedState).search();
          }
        };
      },
      init: function init(_ref11) {
        var helper = _ref11.helper,
            instantSearchInstance = _ref11.instantSearchInstance;
        var stats = {};

        var currentRange = this._getCurrentRange(stats);

        var start = this._getCurrentRefinement(helper);

        renderFn({
          // On first render pass an empty range
          // to be able to bypass the validation
          // related to it
          refine: this._refine(instantSearchInstance, helper, {}),
          sendEvent: createSendEvent(instantSearchInstance, helper, {}),
          format: rangeFormatter,
          range: currentRange,
          widgetParams: _objectSpread({}, widgetParams, {
            precision: precision
          }),
          start: start,
          instantSearchInstance: instantSearchInstance
        }, true);
      },
      render: function render(_ref12) {
        var results = _ref12.results,
            helper = _ref12.helper,
            instantSearchInstance = _ref12.instantSearchInstance;
        var facetsFromResults = results.disjunctiveFacets || [];
        var facet = (0, _utils.find)(facetsFromResults, function (facetResult) {
          return facetResult.name === attribute;
        });
        var stats = facet && facet.stats || {};

        var currentRange = this._getCurrentRange(stats);

        var start = this._getCurrentRefinement(helper);

        renderFn({
          refine: this._refine(instantSearchInstance, helper, currentRange),
          sendEvent: createSendEvent(instantSearchInstance, helper, currentRange),
          format: rangeFormatter,
          range: currentRange,
          widgetParams: _objectSpread({}, widgetParams, {
            precision: precision
          }),
          start: start,
          instantSearchInstance: instantSearchInstance
        }, false);
      },
      dispose: function dispose(_ref13) {
        var state = _ref13.state;
        unmountFn();
        var stateWithoutDisjunctive = state.removeDisjunctiveFacet(attribute); // can not use setQueryParameters || removeNumericRefinement, because
        // they both keep the old value. This isn't immutable, but it is fine
        // since it's already a copy.

        stateWithoutDisjunctive.numericRefinements = _objectSpread({}, state.numericRefinements, _defineProperty({}, attribute, undefined));
        return stateWithoutDisjunctive;
      },
      getWidgetState: function getWidgetState(uiState, _ref14) {
        var searchParameters = _ref14.searchParameters;

        var _searchParameters$get = searchParameters.getNumericRefinements(attribute),
            _searchParameters$get2 = _searchParameters$get['>='],
            min = _searchParameters$get2 === void 0 ? [] : _searchParameters$get2,
            _searchParameters$get3 = _searchParameters$get['<='],
            max = _searchParameters$get3 === void 0 ? [] : _searchParameters$get3;

        if (min.length === 0 && max.length === 0) {
          return uiState;
        }

        return _objectSpread({}, uiState, {
          range: _objectSpread({}, uiState.range, _defineProperty({}, attribute, "".concat(min, ":").concat(max)))
        });
      },
      getWidgetSearchParameters: function getWidgetSearchParameters(searchParameters, _ref15) {
        var uiState = _ref15.uiState;
        var widgetSearchParameters = searchParameters.addDisjunctiveFacet(attribute).setQueryParameters({
          numericRefinements: _objectSpread({}, searchParameters.numericRefinements, _defineProperty({}, attribute, {}))
        });

        if (hasMinBound) {
          widgetSearchParameters = widgetSearchParameters.addNumericRefinement(attribute, '>=', minBound);
        }

        if (hasMaxBound) {
          widgetSearchParameters = widgetSearchParameters.addNumericRefinement(attribute, '<=', maxBound);
        }

        var value = uiState.range && uiState.range[attribute];

        if (!value || value.indexOf(':') === -1) {
          return widgetSearchParameters;
        }

        var _value$split$map = value.split(':').map(parseFloat),
            _value$split$map2 = _slicedToArray(_value$split$map, 2),
            lowerBound = _value$split$map2[0],
            upperBound = _value$split$map2[1];

        if ((0, _utils.isFiniteNumber)(lowerBound)) {
          widgetSearchParameters = widgetSearchParameters.addNumericRefinement(attribute, '>=', lowerBound);
        }

        if ((0, _utils.isFiniteNumber)(upperBound)) {
          widgetSearchParameters = widgetSearchParameters.addNumericRefinement(attribute, '<=', upperBound);
        }

        return widgetSearchParameters;
      }
    };
  };
}