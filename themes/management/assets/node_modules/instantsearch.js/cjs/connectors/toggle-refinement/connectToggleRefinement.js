"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = connectToggleRefinement;

var _utils = require("../../lib/utils");

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var withUsage = (0, _utils.createDocumentationMessageGenerator)({
  name: 'toggle-refinement',
  connector: true
});
var $$type = 'ais.toggleRefinement';

var createSendEvent = function createSendEvent(_ref) {
  var instantSearchInstance = _ref.instantSearchInstance,
      attribute = _ref.attribute,
      on = _ref.on,
      helper = _ref.helper;
  return function () {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    if (args.length === 1) {
      instantSearchInstance.sendEventToInsights(args[0]);
      return;
    }

    var eventType = args[0],
        isRefined = args[1],
        _args$ = args[2],
        eventName = _args$ === void 0 ? 'Filter Applied' : _args$;

    if (eventType !== 'click' || on === undefined) {
      return;
    } // Checking


    if (!isRefined) {
      instantSearchInstance.sendEventToInsights({
        insightsMethod: 'clickedFilters',
        widgetType: $$type,
        eventType: eventType,
        payload: {
          eventName: eventName,
          index: helper.getIndex(),
          filters: on.map(function (value) {
            return "".concat(attribute, ":").concat(JSON.stringify(value));
          })
        }
      });
    }
  };
};
/**
 * @typedef {Object} ToggleValue
 * @property {boolean} isRefined `true` if the toggle is on.
 * @property {number} count Number of results matched after applying the toggle refinement.
 * @property {Object} onFacetValue Value of the toggle when it's on.
 * @property {Object} offFacetValue Value of the toggle when it's off.
 */

/**
 * @typedef {Object} CustomToggleWidgetOptions
 * @property {string} attribute Name of the attribute for faceting (eg. "free_shipping").
 * @property {Object} [on = true] Value to filter on when toggled.
 * @property {Object} [off] Value to filter on when not toggled.
 */

/**
 * @typedef {Object} ToggleRenderingOptions
 * @property {ToggleValue} value The current toggle value.
 * @property {function():string} createURL Creates an URL for the next state.
 * @property {function(value)} refine Updates to the next state by applying the toggle refinement.
 * @property {Object} widgetParams All original `CustomToggleWidgetOptions` forwarded to the `renderFn`.
 */

/**
 * **Toggle** connector provides the logic to build a custom widget that will provide
 * an on/off filtering feature based on an attribute value or values.
 *
 * Two modes are implemented in the custom widget:
 *  - with or without the value filtered
 *  - switch between two values.
 *
 * @type {Connector}
 * @param {function(ToggleRenderingOptions, boolean)} renderFn Rendering function for the custom **Toggle** widget.
 * @param {function} unmountFn Unmount function called when the widget is disposed.
 * @return {function(CustomToggleWidgetOptions)} Re-usable widget factory for a custom **Toggle** widget.
 * @example
 * // custom `renderFn` to render the custom ClearAll widget
 * function renderFn(ToggleRenderingOptions, isFirstRendering) {
 *   ToggleRenderingOptions.widgetParams.containerNode
 *     .find('a')
 *     .off('click');
 *
 *   var buttonHTML = `
 *     <a href="${ToggleRenderingOptions.createURL()}">
 *       <input
 *         type="checkbox"
 *         value="${ToggleRenderingOptions.value.name}"
 *         ${ToggleRenderingOptions.value.isRefined ? 'checked' : ''}
 *       />
 *       ${ToggleRenderingOptions.value.name} (${ToggleRenderingOptions.value.count})
 *     </a>
 *   `;
 *
 *   ToggleRenderingOptions.widgetParams.containerNode.html(buttonHTML);
 *   ToggleRenderingOptions.widgetParams.containerNode
 *     .find('a')
 *     .on('click', function(event) {
 *       event.preventDefault();
 *       event.stopPropagation();
 *
 *       ToggleRenderingOptions.refine(ToggleRenderingOptions.value);
 *     });
 * }
 *
 * // connect `renderFn` to Toggle logic
 * var customToggle = instantsearch.connectors.connectToggleRefinement(renderFn);
 *
 * // mount widget on the page
 * search.addWidgets([
 *   customToggle({
 *     containerNode: $('#custom-toggle-container'),
 *     attribute: 'free_shipping',
 *   })
 * ]);
 */


function connectToggleRefinement(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : _utils.noop;
  (0, _utils.checkRendering)(renderFn, withUsage());
  return function () {
    var widgetParams = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var attribute = widgetParams.attribute,
        _widgetParams$on = widgetParams.on,
        userOn = _widgetParams$on === void 0 ? true : _widgetParams$on,
        userOff = widgetParams.off;

    if (!attribute) {
      throw new Error(withUsage('The `attribute` option is required.'));
    }

    var hasAnOffValue = userOff !== undefined;
    var hasAnOnValue = userOn !== undefined;
    var on = hasAnOnValue ? (0, _utils.toArray)(userOn).map(_utils.escapeRefinement) : undefined;
    var off = hasAnOffValue ? (0, _utils.toArray)(userOff).map(_utils.escapeRefinement) : undefined;
    var sendEvent;
    return {
      $$type: $$type,
      _toggleRefinement: function _toggleRefinement(helper) {
        var _ref2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
            isRefined = _ref2.isRefined;

        // Checking
        if (!isRefined) {
          sendEvent('click', isRefined);

          if (hasAnOffValue) {
            off.forEach(function (v) {
              return helper.removeDisjunctiveFacetRefinement(attribute, v);
            });
          }

          on.forEach(function (v) {
            return helper.addDisjunctiveFacetRefinement(attribute, v);
          });
        } else {
          // Unchecking
          on.forEach(function (v) {
            return helper.removeDisjunctiveFacetRefinement(attribute, v);
          });

          if (hasAnOffValue) {
            off.forEach(function (v) {
              return helper.addDisjunctiveFacetRefinement(attribute, v);
            });
          }
        }

        helper.search();
      },
      init: function init(_ref3) {
        var _this = this;

        var state = _ref3.state,
            helper = _ref3.helper,
            createURL = _ref3.createURL,
            instantSearchInstance = _ref3.instantSearchInstance;
        sendEvent = createSendEvent({
          instantSearchInstance: instantSearchInstance,
          attribute: attribute,
          on: on,
          helper: helper
        });

        this._createURL = function (isCurrentlyRefined) {
          return function () {
            var valuesToRemove = isCurrentlyRefined ? on : off;

            if (valuesToRemove) {
              valuesToRemove.forEach(function (v) {
                state.removeDisjunctiveFacetRefinement(attribute, v);
              });
            }

            var valuesToAdd = isCurrentlyRefined ? off : on;

            if (valuesToAdd) {
              valuesToAdd.forEach(function (v) {
                state.addDisjunctiveFacetRefinement(attribute, v);
              });
            }

            return createURL(state);
          };
        };

        this.toggleRefinement = function (opts) {
          _this._toggleRefinement(helper, opts);
        };

        var isRefined = on && on.every(function (v) {
          return state.isDisjunctiveFacetRefined(attribute, v);
        }); // no need to refine anything at init if no custom off values

        if (hasAnOffValue) {
          // Add filtering on the 'off' value if set
          if (!isRefined) {
            var currentPage = helper.state.page;

            if (off) {
              off.forEach(function (v) {
                return helper.addDisjunctiveFacetRefinement(attribute, v);
              });
            }

            helper.setPage(currentPage);
          }
        }

        var onFacetValue = {
          isRefined: isRefined,
          count: 0
        };
        var offFacetValue = {
          isRefined: hasAnOffValue && !isRefined,
          count: 0
        };
        var value = {
          name: attribute,
          isRefined: isRefined,
          count: null,
          onFacetValue: onFacetValue,
          offFacetValue: offFacetValue
        };
        renderFn({
          value: value,
          createURL: this._createURL(value.isRefined),
          refine: this.toggleRefinement,
          sendEvent: sendEvent,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, true);
      },
      render: function render(_ref4) {
        var helper = _ref4.helper,
            results = _ref4.results,
            state = _ref4.state,
            instantSearchInstance = _ref4.instantSearchInstance;
        var isRefined = on && on.every(function (v) {
          return helper.state.isDisjunctiveFacetRefined(attribute, v);
        });
        var offValue = (0, _utils.toArray)(off === undefined ? false : off);
        var allFacetValues = results.getFacetValues(attribute) || [];
        var onData = on && on.map(function (v) {
          return (0, _utils.find)(allFacetValues, function (_ref5) {
            var name = _ref5.name;
            return name === (0, _utils.unescapeRefinement)(v);
          });
        }).filter(function (v) {
          return v !== undefined;
        });
        var onFacetValue = {
          isRefined: onData.length > 0 ? onData.every(function (v) {
            return v.isRefined;
          }) : false,
          count: onData.length === 0 ? null : onData.reduce(function (acc, v) {
            return acc + v.count;
          }, 0)
        };
        var offData = hasAnOffValue ? offValue.map(function (v) {
          return (0, _utils.find)(allFacetValues, function (_ref6) {
            var name = _ref6.name;
            return name === (0, _utils.unescapeRefinement)(v);
          });
        }).filter(function (v) {
          return v !== undefined;
        }) : [];
        var offFacetValue = {
          isRefined: offData.length > 0 ? offData.every(function (v) {
            return v.isRefined;
          }) : false,
          count: offData.length === 0 ? allFacetValues.reduce(function (total, _ref7) {
            var count = _ref7.count;
            return total + count;
          }, 0) : offData.reduce(function (acc, v) {
            return acc + v.count;
          }, 0)
        }; // what will we show by default,
        // if checkbox is not checked, show: [ ] free shipping (countWhenChecked)
        // if checkbox is checked, show: [x] free shipping (countWhenNotChecked)

        var nextRefinement = isRefined ? offFacetValue : onFacetValue;
        var value = {
          name: attribute,
          isRefined: isRefined,
          count: nextRefinement === undefined ? null : nextRefinement.count,
          onFacetValue: onFacetValue,
          offFacetValue: offFacetValue
        };
        renderFn({
          value: value,
          state: state,
          createURL: this._createURL(value.isRefined),
          refine: this.toggleRefinement,
          sendEvent: sendEvent,
          helper: helper,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, false);
      },
      dispose: function dispose(_ref8) {
        var state = _ref8.state;
        unmountFn();
        return state.removeDisjunctiveFacet(attribute);
      },
      getWidgetState: function getWidgetState(uiState, _ref9) {
        var searchParameters = _ref9.searchParameters;
        var isRefined = on && on.every(function (v) {
          return searchParameters.isDisjunctiveFacetRefined(attribute, v);
        });

        if (!isRefined) {
          return uiState;
        }

        return _objectSpread({}, uiState, {
          toggle: _objectSpread({}, uiState.toggle, _defineProperty({}, attribute, isRefined))
        });
      },
      getWidgetSearchParameters: function getWidgetSearchParameters(searchParameters, _ref10) {
        var uiState = _ref10.uiState;
        var withFacetConfiguration = searchParameters.clearRefinements(attribute).addDisjunctiveFacet(attribute);
        var isRefined = Boolean(uiState.toggle && uiState.toggle[attribute]);

        if (isRefined) {
          if (on) {
            on.forEach(function (v) {
              withFacetConfiguration = withFacetConfiguration.addDisjunctiveFacetRefinement(attribute, v);
            });
          }

          return withFacetConfiguration;
        } // It's not refined with an `off` value


        if (hasAnOffValue) {
          if (off) {
            off.forEach(function (v) {
              withFacetConfiguration = withFacetConfiguration.addDisjunctiveFacetRefinement(attribute, v);
            });
          }

          return withFacetConfiguration;
        } // It's not refined without an `off` value


        return withFacetConfiguration.setQueryParameters({
          disjunctiveFacetsRefinements: _objectSpread({}, searchParameters.disjunctiveFacetsRefinements, _defineProperty({}, attribute, []))
        });
      }
    };
  };
}