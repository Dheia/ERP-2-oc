"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _escapeHighlight = _interopRequireWildcard(require("../../lib/escape-highlight"));

var _utils = require("../../lib/utils");

function _getRequireWildcardCache() { if (typeof WeakMap !== "function") return null; var cache = new WeakMap(); _getRequireWildcardCache = function _getRequireWildcardCache() { return cache; }; return cache; }

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { default: obj }; } var cache = _getRequireWildcardCache(); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj.default = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

var withUsage = (0, _utils.createDocumentationMessageGenerator)({
  name: 'infinite-hits',
  connector: true
});

function getStateWithoutPage(state) {
  var _ref = state || {},
      page = _ref.page,
      rest = _objectWithoutProperties(_ref, ["page"]);

  return rest;
}

function getInMemoryCache() {
  var cachedHits = null;
  var cachedState = undefined;
  return {
    read: function read(_ref2) {
      var state = _ref2.state;
      return (0, _utils.isEqual)(cachedState, getStateWithoutPage(state)) ? cachedHits : null;
    },
    write: function write(_ref3) {
      var state = _ref3.state,
          hits = _ref3.hits;
      cachedState = getStateWithoutPage(state);
      cachedHits = hits;
    }
  };
}

function extractHitsFromCachedHits(cachedHits) {
  return Object.keys(cachedHits).map(Number).sort(function (a, b) {
    return a - b;
  }).reduce(function (acc, page) {
    return acc.concat(cachedHits[page]);
  }, []);
}

var connectInfiniteHits = function connectInfiniteHits(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : _utils.noop;
  (0, _utils.checkRendering)(renderFn, withUsage());
  return function (widgetParams) {
    var _ref4 = widgetParams || {},
        _ref4$escapeHTML = _ref4.escapeHTML,
        escapeHTML = _ref4$escapeHTML === void 0 ? true : _ref4$escapeHTML,
        _ref4$transformItems = _ref4.transformItems,
        transformItems = _ref4$transformItems === void 0 ? function (items) {
      return items;
    } : _ref4$transformItems,
        _ref4$cache = _ref4.cache,
        cache = _ref4$cache === void 0 ? getInMemoryCache() : _ref4$cache;

    var cachedHits = undefined;
    var prevState;
    var showPrevious;
    var showMore;
    var sendEvent;
    var bindEvent;

    var getFirstReceivedPage = function getFirstReceivedPage() {
      return Math.min.apply(Math, _toConsumableArray(Object.keys(cachedHits || {}).map(Number)));
    };

    var getLastReceivedPage = function getLastReceivedPage() {
      return Math.max.apply(Math, _toConsumableArray(Object.keys(cachedHits || {}).map(Number)));
    };

    var getShowPrevious = function getShowPrevious(helper) {
      return function () {
        // Using the helper's `overrideStateWithoutTriggeringChangeEvent` method
        // avoid updating the browser URL when the user displays the previous page.
        helper.overrideStateWithoutTriggeringChangeEvent(_objectSpread({}, helper.state, {
          page: getFirstReceivedPage() - 1
        })).searchWithoutTriggeringOnStateChange();
      };
    };

    var getShowMore = function getShowMore(helper) {
      return function () {
        helper.setPage(getLastReceivedPage() + 1).search();
      };
    };

    var filterEmptyRefinements = function filterEmptyRefinements() {
      var refinements = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      return Object.keys(refinements).filter(function (key) {
        return Array.isArray(refinements[key]) ? refinements[key].length : Object.keys(refinements[key]).length;
      }).reduce(function (obj, key) {
        obj[key] = refinements[key];
        return obj;
      }, {});
    };

    return {
      $$type: 'ais.infiniteHits',
      init: function init(_ref5) {
        var instantSearchInstance = _ref5.instantSearchInstance,
            helper = _ref5.helper;
        showPrevious = getShowPrevious(helper);
        showMore = getShowMore(helper);
        sendEvent = (0, _utils.createSendEventForHits)({
          instantSearchInstance: instantSearchInstance,
          index: helper.getIndex(),
          widgetType: this.$$type
        });
        bindEvent = (0, _utils.createBindEventForHits)({
          index: helper.getIndex(),
          widgetType: this.$$type
        });
        renderFn({
          hits: extractHitsFromCachedHits(cache.read({
            state: helper.state
          }) || {}),
          results: undefined,
          sendEvent: sendEvent,
          bindEvent: bindEvent,
          showPrevious: showPrevious,
          showMore: showMore,
          isFirstPage: getFirstReceivedPage() === 0 || helper.state.page === undefined,
          isLastPage: true,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, true);
      },
      render: function render(_ref6) {
        var results = _ref6.results,
            state = _ref6.state,
            instantSearchInstance = _ref6.instantSearchInstance;

        // Reset cache and received pages if anything changes in the
        // search state, except for the page.
        //
        // We're doing this to "reset" the widget if a refinement or the
        // query changes between renders, but we want to keep it as is
        // if we only change pages.
        var _state$page = state.page,
            page = _state$page === void 0 ? 0 : _state$page,
            facets = state.facets,
            hierarchicalFacets = state.hierarchicalFacets,
            disjunctiveFacets = state.disjunctiveFacets,
            maxValuesPerFacet = state.maxValuesPerFacet,
            currentState = _objectWithoutProperties(state, ["page", "facets", "hierarchicalFacets", "disjunctiveFacets", "maxValuesPerFacet"]);

        currentState.facetsRefinements = filterEmptyRefinements(currentState.facetsRefinements);
        currentState.hierarchicalFacetsRefinements = filterEmptyRefinements(currentState.hierarchicalFacetsRefinements);
        currentState.disjunctiveFacetsRefinements = filterEmptyRefinements(currentState.disjunctiveFacetsRefinements);
        currentState.numericRefinements = filterEmptyRefinements(currentState.numericRefinements);

        if (!(0, _utils.isEqual)(currentState, prevState)) {
          cachedHits = cache.read({
            state: state
          }) || {};
          prevState = currentState;
        }

        if (escapeHTML && results.hits.length > 0) {
          results.hits = (0, _escapeHighlight.default)(results.hits);
        }

        var initialEscaped = results.hits.__escaped;
        results.hits = (0, _utils.addAbsolutePosition)(results.hits, results.page, results.hitsPerPage);
        results.hits = (0, _utils.addQueryID)(results.hits, results.queryID);
        results.hits = transformItems(results.hits); // Make sure the escaped tag stays after mapping over the hits.
        // This prevents the hits from being double-escaped if there are multiple
        // hits widgets mounted on the page.

        results.hits.__escaped = initialEscaped;

        if (cachedHits === undefined) {
          cachedHits = cache.read({
            state: state
          }) || {};
        }

        if (cachedHits[page] === undefined) {
          cachedHits[page] = results.hits;
          cache.write({
            state: state,
            hits: cachedHits
          });
        }

        var isFirstPage = getFirstReceivedPage() === 0;
        var isLastPage = results.nbPages <= getLastReceivedPage() + 1;
        sendEvent('view', cachedHits[page]);
        renderFn({
          hits: extractHitsFromCachedHits(cachedHits),
          results: results,
          sendEvent: sendEvent,
          bindEvent: bindEvent,
          showPrevious: showPrevious,
          showMore: showMore,
          isFirstPage: isFirstPage,
          isLastPage: isLastPage,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, false);
      },
      dispose: function dispose(_ref7) {
        var state = _ref7.state;
        unmountFn();
        var stateWithoutPage = state.setQueryParameter('page', undefined);

        if (!escapeHTML) {
          return stateWithoutPage;
        }

        return stateWithoutPage.setQueryParameters(Object.keys(_escapeHighlight.TAG_PLACEHOLDER).reduce(function (acc, key) {
          return _objectSpread({}, acc, _defineProperty({}, key, undefined));
        }, {}));
      },
      getWidgetState: function getWidgetState(uiState, _ref8) {
        var searchParameters = _ref8.searchParameters;
        var page = searchParameters.page || 0;

        if (!page) {
          // return without adding `page` to uiState
          // because we don't want `page=1` in the URL
          return uiState;
        }

        return _objectSpread({}, uiState, {
          // The page in the UI state is incremented by one
          // to expose the user value (not `0`).
          page: page + 1
        });
      },
      getWidgetSearchParameters: function getWidgetSearchParameters(searchParameters, _ref9) {
        var uiState = _ref9.uiState;
        var widgetSearchParameters = searchParameters;

        if (escapeHTML) {
          widgetSearchParameters = searchParameters.setQueryParameters(_escapeHighlight.TAG_PLACEHOLDER);
        } // The page in the search parameters is decremented by one
        // to get to the actual parameter value from the UI state.


        var page = uiState.page ? uiState.page - 1 : 0;
        return widgetSearchParameters.setQueryParameter('page', page);
      }
    };
  };
};

var _default = connectInfiniteHits;
exports.default = _default;