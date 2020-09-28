function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

import { getRefinements, checkRendering, createDocumentationMessageGenerator, noop, warning } from '../../lib/utils';
var withUsage = createDocumentationMessageGenerator({
  name: 'current-refinements',
  connector: true
});

var connectCurrentRefinements = function connectCurrentRefinements(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : noop;
  checkRendering(renderFn, withUsage());
  return function (widgetParams) {
    if ((widgetParams || {}).includedAttributes && (widgetParams || {}).excludedAttributes) {
      throw new Error(withUsage('The options `includedAttributes` and `excludedAttributes` cannot be used together.'));
    }

    var _ref = widgetParams || {},
        includedAttributes = _ref.includedAttributes,
        _ref$excludedAttribut = _ref.excludedAttributes,
        excludedAttributes = _ref$excludedAttribut === void 0 ? ['query'] : _ref$excludedAttribut,
        _ref$transformItems = _ref.transformItems,
        transformItems = _ref$transformItems === void 0 ? function (items) {
      return items;
    } : _ref$transformItems;

    return {
      $$type: 'ais.currentRefinements',
      init: function init(_ref2) {
        var helper = _ref2.helper,
            _createURL = _ref2.createURL,
            instantSearchInstance = _ref2.instantSearchInstance;
        var items = transformItems(getItems({
          results: {},
          helper: helper,
          includedAttributes: includedAttributes,
          excludedAttributes: excludedAttributes
        }));
        renderFn({
          items: items,
          refine: function refine(refinement) {
            return clearRefinement(helper, refinement);
          },
          createURL: function createURL(refinement) {
            return _createURL(clearRefinementFromState(helper.state, refinement));
          },
          instantSearchInstance: instantSearchInstance,
          widgetParams: widgetParams
        }, true);
      },
      render: function render(_ref3) {
        var scopedResults = _ref3.scopedResults,
            helper = _ref3.helper,
            _createURL2 = _ref3.createURL,
            instantSearchInstance = _ref3.instantSearchInstance;
        var items = scopedResults.reduce(function (results, scopedResult) {
          return results.concat(transformItems(getItems({
            results: scopedResult.results,
            helper: scopedResult.helper,
            includedAttributes: includedAttributes,
            excludedAttributes: excludedAttributes
          })));
        }, []);
        renderFn({
          items: items,
          refine: function refine(refinement) {
            return clearRefinement(helper, refinement);
          },
          createURL: function createURL(refinement) {
            return _createURL2(clearRefinementFromState(helper.state, refinement));
          },
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

function getItems(_ref4) {
  var results = _ref4.results,
      helper = _ref4.helper,
      includedAttributes = _ref4.includedAttributes,
      excludedAttributes = _ref4.excludedAttributes;
  var clearsQuery = (includedAttributes || []).indexOf('query') !== -1 || (excludedAttributes || []).indexOf('query') === -1;
  var filterFunction = includedAttributes ? function (item) {
    return includedAttributes.indexOf(item.attribute) !== -1;
  } : function (item) {
    return excludedAttributes.indexOf(item.attribute) === -1;
  };
  var items = getRefinements(results, helper.state, clearsQuery).map(normalizeRefinement).filter(filterFunction);
  return items.reduce(function (allItems, currentItem) {
    return [].concat(_toConsumableArray(allItems.filter(function (item) {
      return item.attribute !== currentItem.attribute;
    })), [{
      indexName: helper.state.index,
      attribute: currentItem.attribute,
      label: currentItem.attribute,
      refinements: items.filter(function (result) {
        return result.attribute === currentItem.attribute;
      }) // We want to keep the order of refinements except the numeric ones.
      .sort(function (a, b) {
        return a.type === 'numeric' ? a.value - b.value : 0;
      }),
      refine: function refine(refinement) {
        return clearRefinement(helper, refinement);
      }
    }]);
  }, []);
}

function clearRefinementFromState(state, refinement) {
  switch (refinement.type) {
    case 'facet':
      return state.removeFacetRefinement(refinement.attribute, String(refinement.value));

    case 'disjunctive':
      return state.removeDisjunctiveFacetRefinement(refinement.attribute, String(refinement.value));

    case 'hierarchical':
      return state.removeHierarchicalFacetRefinement(refinement.attribute);

    case 'exclude':
      return state.removeExcludeRefinement(refinement.attribute, String(refinement.value));

    case 'numeric':
      return state.removeNumericRefinement(refinement.attribute, refinement.operator, String(refinement.value));

    case 'tag':
      return state.removeTagRefinement(String(refinement.value));

    case 'query':
      return state.setQueryParameter('query', '');

    default:
      process.env.NODE_ENV === 'development' ? warning(false, "The refinement type \"".concat(refinement.type, "\" does not exist and cannot be cleared from the current refinements.")) : void 0;
      return state;
  }
}

function clearRefinement(helper, refinement) {
  helper.setState(clearRefinementFromState(helper.state, refinement)).search();
}

function getOperatorSymbol(operator) {
  switch (operator) {
    case '>=':
      return '≥';

    case '<=':
      return '≤';

    default:
      return operator;
  }
}

function normalizeRefinement(refinement) {
  var value = refinement.type === 'numeric' ? Number(refinement.name) : refinement.name;
  var label = refinement.operator ? "".concat(getOperatorSymbol(refinement.operator), " ").concat(refinement.name) : refinement.name;
  var normalizedRefinement = {
    attribute: refinement.attribute,
    type: refinement.type,
    value: value,
    label: label
  };

  if (refinement.operator !== undefined) {
    normalizedRefinement.operator = refinement.operator;
  }

  if (refinement.count !== undefined) {
    normalizedRefinement.count = refinement.count;
  }

  if (refinement.exhaustive !== undefined) {
    normalizedRefinement.exhaustive = refinement.exhaustive;
  }

  return normalizedRefinement;
}

export default connectCurrentRefinements;