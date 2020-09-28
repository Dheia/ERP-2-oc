function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

import algoliasearchHelper from 'algoliasearch-helper';
import { createDocumentationMessageGenerator, isPlainObject, mergeSearchParameters, noop } from '../../lib/utils';
/**
 * Refine the given search parameters.
 */

var withUsage = createDocumentationMessageGenerator({
  name: 'configure',
  connector: true
});

function getInitialSearchParameters(state, widgetParams) {
  // We leverage the helper internals to remove the `widgetParams` from
  // the state. The function `setQueryParameters` omits the values that
  // are `undefined` on the next state.
  return state.setQueryParameters(Object.keys(widgetParams.searchParameters).reduce(function (acc, key) {
    return _objectSpread({}, acc, _defineProperty({}, key, undefined));
  }, {}));
}

var connectConfigure = function connectConfigure() {
  var renderFn = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : noop;
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : noop;
  return function (widgetParams) {
    if (!widgetParams || !isPlainObject(widgetParams.searchParameters)) {
      throw new Error(withUsage('The `searchParameters` option expects an object.'));
    }

    var connectorState = {};

    function refine(helper) {
      return function (searchParameters) {
        // Merge new `searchParameters` with the ones set from other widgets
        var actualState = getInitialSearchParameters(helper.state, widgetParams);
        var nextSearchParameters = mergeSearchParameters(actualState, new algoliasearchHelper.SearchParameters(searchParameters)); // Trigger a search with the resolved search parameters

        helper.setState(nextSearchParameters).search(); // Update original `widgetParams.searchParameters` to the new refined one

        widgetParams.searchParameters = searchParameters;
      };
    }

    return {
      $$type: 'ais.configure',
      init: function init(_ref) {
        var instantSearchInstance = _ref.instantSearchInstance,
            helper = _ref.helper;
        connectorState.refine = refine(helper);
        renderFn({
          refine: connectorState.refine,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, true);
      },
      render: function render(_ref2) {
        var instantSearchInstance = _ref2.instantSearchInstance;
        renderFn({
          refine: connectorState.refine,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, false);
      },
      dispose: function dispose(_ref3) {
        var state = _ref3.state;
        unmountFn();
        return getInitialSearchParameters(state, widgetParams);
      },
      getWidgetSearchParameters: function getWidgetSearchParameters(state, _ref4) {
        var uiState = _ref4.uiState;
        return mergeSearchParameters(state, new algoliasearchHelper.SearchParameters(_objectSpread({}, uiState.configure, {}, widgetParams.searchParameters)));
      },
      getWidgetState: function getWidgetState(uiState) {
        return _objectSpread({}, uiState, {
          configure: _objectSpread({}, uiState.configure, {}, widgetParams.searchParameters)
        });
      }
    };
  };
};

export default connectConfigure;