function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === "[object Arguments]")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

import { checkRendering, warning, createDocumentationMessageGenerator, isEqual, noop } from '../../lib/utils';
var withUsage = createDocumentationMessageGenerator({
  name: 'breadcrumb',
  connector: true
});

var connectBreadcrumb = function connectBreadcrumb(renderFn) {
  var unmountFn = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : noop;
  checkRendering(renderFn, withUsage());
  var connectorState = {};
  return function (widgetParams) {
    var _ref = widgetParams || {},
        attributes = _ref.attributes,
        _ref$separator = _ref.separator,
        separator = _ref$separator === void 0 ? ' > ' : _ref$separator,
        _ref$rootPath = _ref.rootPath,
        rootPath = _ref$rootPath === void 0 ? null : _ref$rootPath,
        _ref$transformItems = _ref.transformItems,
        transformItems = _ref$transformItems === void 0 ? function (items) {
      return items;
    } : _ref$transformItems;

    if (!attributes || !Array.isArray(attributes) || attributes.length === 0) {
      throw new Error(withUsage('The `attributes` option expects an array of strings.'));
    }

    var _attributes = _slicedToArray(attributes, 1),
        hierarchicalFacetName = _attributes[0];

    return {
      $$type: 'ais.breadcrumb',
      init: function init(_ref2) {
        var createURL = _ref2.createURL,
            helper = _ref2.helper,
            instantSearchInstance = _ref2.instantSearchInstance;

        connectorState.createURL = function (facetValue) {
          if (!facetValue) {
            var breadcrumb = helper.getHierarchicalFacetBreadcrumb(hierarchicalFacetName);

            if (breadcrumb.length > 0) {
              return createURL(helper.state.toggleFacetRefinement(hierarchicalFacetName, breadcrumb[0]));
            }
          }

          return createURL(helper.state.toggleFacetRefinement(hierarchicalFacetName, facetValue));
        };

        connectorState.refine = function (facetValue) {
          if (!facetValue) {
            var breadcrumb = helper.getHierarchicalFacetBreadcrumb(hierarchicalFacetName);

            if (breadcrumb.length > 0) {
              helper.toggleRefinement(hierarchicalFacetName, breadcrumb[0]).search();
            }
          } else {
            helper.toggleRefinement(hierarchicalFacetName, facetValue).search();
          }
        };

        renderFn({
          createURL: connectorState.createURL,
          canRefine: false,
          instantSearchInstance: instantSearchInstance,
          items: [],
          refine: connectorState.refine,
          widgetParams: widgetParams
        }, true);
      },
      render: function render(_ref3) {
        var instantSearchInstance = _ref3.instantSearchInstance,
            results = _ref3.results,
            state = _ref3.state;

        var _state$hierarchicalFa = _slicedToArray(state.hierarchicalFacets, 1),
            facetName = _state$hierarchicalFa[0].name;

        var facetValues = results.getFacetValues(facetName, {});
        var data = Array.isArray(facetValues.data) ? facetValues.data : [];
        var items = transformItems(shiftItemsValues(prepareItems(data)));
        renderFn({
          canRefine: items.length > 0,
          createURL: connectorState.createURL,
          instantSearchInstance: instantSearchInstance,
          items: items,
          refine: connectorState.refine,
          widgetParams: widgetParams
        }, false);
      },
      dispose: function dispose() {
        unmountFn();
      },
      getWidgetSearchParameters: function getWidgetSearchParameters(searchParameters) {
        if (searchParameters.isHierarchicalFacet(hierarchicalFacetName)) {
          var facet = searchParameters.getHierarchicalFacetByName(hierarchicalFacetName);
          process.env.NODE_ENV === 'development' ? warning(isEqual(facet.attributes, attributes) && facet.separator === separator && facet.rootPath === rootPath, 'Using Breadcrumb and HierarchicalMenu on the same facet with different options overrides the configuration of the HierarchicalMenu.') : void 0;
          return searchParameters;
        }

        return searchParameters.addHierarchicalFacet({
          name: hierarchicalFacetName,
          attributes: attributes,
          separator: separator,
          rootPath: rootPath
        });
      }
    };
  };
};

function prepareItems(data) {
  return data.reduce(function (result, currentItem) {
    if (currentItem.isRefined) {
      result.push({
        label: currentItem.name,
        value: currentItem.path
      });

      if (Array.isArray(currentItem.data)) {
        result = result.concat(prepareItems(currentItem.data));
      }
    }

    return result;
  }, []);
}

function shiftItemsValues(array) {
  return array.map(function (x, idx) {
    return {
      label: x.label,
      value: idx + 1 === array.length ? null : array[idx + 1].value
    };
  });
}

export default connectBreadcrumb;