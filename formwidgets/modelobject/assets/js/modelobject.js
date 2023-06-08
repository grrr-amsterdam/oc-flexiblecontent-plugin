const SELECT_SELECTOR = '.model-object-field';
const SEARCH_ENDPOINT_ATTRIBUTE = 'data-search-endpoint';
const NAME_FROM_ATTRIBUTE = 'data-name-from';

$(document).render(function () {
  $(SELECT_SELECTOR).each(function (index, select) {
    const searchEndpoint = select.getAttribute(SEARCH_ENDPOINT_ATTRIBUTE);
    const optionLabelFrom = select.getAttribute(NAME_FROM_ATTRIBUTE);

    const select2Options = searchEndpoint ? {
      ajax: {
        url: searchEndpoint,
        dataType: 'json',
        data: function (params) {
          params['name_from'] = optionLabelFrom;
          return params;
        },
      }
    } : {};

    $(select).select2(select2Options);
  });
});
