function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

import { checkRendering, clearRefinements, getRefinements, createDocumentationMessageGenerator, noop, uniq, mergeSearchParameters } from '../../lib/utils';
var withUsage = createDocumentationMessageGenerator({
  name: 'clear-refinements',
  connector: true
});

var connectClearRefinements = function connectClearRefinements(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : noop;
  checkRendering(renderFn, withUsage());
  return function (widgetParams) {
    var _ref = widgetParams || {},
        _ref$includedAttribut = _ref.includedAttributes,
        includedAttributes = _ref$includedAttribut === void 0 ? [] : _ref$includedAttribut,
        _ref$excludedAttribut = _ref.excludedAttributes,
        excludedAttributes = _ref$excludedAttribut === void 0 ? ['query'] : _ref$excludedAttribut,
        _ref$transformItems = _ref.transformItems,
        transformItems = _ref$transformItems === void 0 ? function (items) {
      return items;
    } : _ref$transformItems;

    if (widgetParams.includedAttributes && widgetParams.excludedAttributes) {
      throw new Error(withUsage('The options `includedAttributes` and `excludedAttributes` cannot be used together.'));
    }

    var connectorState = {
      refine: noop,
      createURL: function createURL() {
        return '';
      }
    };

    var cachedRefine = function cachedRefine() {
      return connectorState.refine();
    };

    var cachedCreateURL = function cachedCreateURL() {
      return connectorState.createURL();
    };

    return {
      $$type: 'ais.clearRefinements',
      init: function init(_ref2) {
        var instantSearchInstance = _ref2.instantSearchInstance;
        renderFn({
          hasRefinements: false,
          refine: cachedRefine,
          createURL: cachedCreateURL,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, true);
      },
      render: function render(_ref3) {
        var scopedResults = _ref3.scopedResults,
            createURL = _ref3.createURL,
            instantSearchInstance = _ref3.instantSearchInstance;
        var attributesToClear = scopedResults.reduce(function (results, scopedResult) {
          return results.concat(getAttributesToClear({
            scopedResult: scopedResult,
            includedAttributes: includedAttributes,
            excludedAttributes: excludedAttributes,
            transformItems: transformItems
          }));
        }, []);

        connectorState.refine = function () {
          attributesToClear.forEach(function (_ref4) {
            var indexHelper = _ref4.helper,
                items = _ref4.items;
            indexHelper.setState(clearRefinements({
              helper: indexHelper,
              attributesToClear: items
            })).search();
          });
        };

        connectorState.createURL = function () {
          return createURL(mergeSearchParameters.apply(void 0, _toConsumableArray(attributesToClear.map(function (_ref5) {
            var indexHelper = _ref5.helper,
                items = _ref5.items;
            return clearRefinements({
              helper: indexHelper,
              attributesToClear: items
            });
          }))));
        };

        renderFn({
          hasRefinements: attributesToClear.some(function (attributeToClear) {
            return attributeToClear.items.length > 0;
          }),
          refine: cachedRefine,
          createURL: cachedCreateURL,
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, false);
      },
      dispose: function dispose() {
        unmountFn();
      }
    };
  };
};

function getAttributesToClear(_ref6) {
  var scopedResult = _ref6.scopedResult,
      includedAttributes = _ref6.includedAttributes,
      excludedAttributes = _ref6.excludedAttributes,
      transformItems = _ref6.transformItems;
  var clearsQuery = includedAttributes.indexOf('query') !== -1 || excludedAttributes.indexOf('query') === -1;
  return {
    helper: scopedResult.helper,
    items: transformItems(uniq(getRefinements(scopedResult.results, scopedResult.helper.state, clearsQuery).map(function (refinement) {
      return refinement.attribute;
    }).filter(function (attribute) {
      return (// If the array is empty (default case), we keep all the attributes
        includedAttributes.length === 0 || // Otherwise, only add the specified attributes
        includedAttributes.indexOf(attribute) !== -1
      );
    }).filter(function (attribute) {
      return (// If the query is included, we ignore the default `excludedAttributes = ['query']`
        attribute === 'query' && clearsQuery || // Otherwise, ignore the excluded attributes
        excludedAttributes.indexOf(attribute) === -1
      );
    })))
  };
}

export default connectClearRefinements;