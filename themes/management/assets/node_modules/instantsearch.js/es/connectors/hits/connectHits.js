function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import escapeHits, { TAG_PLACEHOLDER } from '../../lib/escape-highlight';
import { checkRendering, createDocumentationMessageGenerator, addAbsolutePosition, addQueryID, createSendEventForHits, createBindEventForHits, noop } from '../../lib/utils';
var withUsage = createDocumentationMessageGenerator({
  name: 'hits',
  connector: true
});

var connectHits = function connectHits(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : noop;
  checkRendering(renderFn, withUsage());
  return function (widgetParams) {
    var _ref = widgetParams || {},
        _ref$escapeHTML = _ref.escapeHTML,
        escapeHTML = _ref$escapeHTML === void 0 ? true : _ref$escapeHTML,
        _ref$transformItems = _ref.transformItems,
        transformItems = _ref$transformItems === void 0 ? function (items) {
      return items;
    } : _ref$transformItems;

    var sendEvent;
    var bindEvent;
    return {
      $$type: 'ais.hits',
      init: function init(_ref2) {
        var instantSearchInstance = _ref2.instantSearchInstance,
            helper = _ref2.helper;
        sendEvent = createSendEventForHits({
          instantSearchInstance: instantSearchInstance,
          index: helper.getIndex(),
          widgetType: this.$$type
        });
        bindEvent = createBindEventForHits({
          index: helper.getIndex(),
          widgetType: this.$$type
        });
        renderFn({
          hits: [],
          results: undefined,
          sendEvent: sendEvent,
          bindEvent: bindEvent,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, true);
      },
      render: function render(_ref3) {
        var results = _ref3.results,
            instantSearchInstance = _ref3.instantSearchInstance;

        if (escapeHTML && results.hits.length > 0) {
          results.hits = escapeHits(results.hits);
        }

        var initialEscaped = results.hits.__escaped;
        results.hits = addAbsolutePosition(results.hits, results.page, results.hitsPerPage);
        results.hits = addQueryID(results.hits, results.queryID);
        results.hits = transformItems(results.hits); // Make sure the escaped tag stays, even after mapping over the hits.
        // This prevents the hits from being double-escaped if there are multiple
        // hits widgets mounted on the page.

        results.hits.__escaped = initialEscaped;
        sendEvent('view', results.hits);
        renderFn({
          hits: results.hits,
          results: results,
          sendEvent: sendEvent,
          bindEvent: bindEvent,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, false);
      },
      dispose: function dispose(_ref4) {
        var state = _ref4.state;
        unmountFn();

        if (!escapeHTML) {
          return state;
        }

        return state.setQueryParameters(Object.keys(TAG_PLACEHOLDER).reduce(function (acc, key) {
          return _objectSpread({}, acc, _defineProperty({}, key, undefined));
        }, {}));
      },
      getWidgetSearchParameters: function getWidgetSearchParameters(state) {
        if (!escapeHTML) {
          return state;
        }

        return state.setQueryParameters(TAG_PLACEHOLDER);
      }
    };
  };
};

export default connectHits;