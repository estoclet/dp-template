/**
 * @file
 * Transforms links into checkboxes.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.facets = Drupal.facets || {};
  Drupal.behaviors.facetsCollapsibleCheckboxWidget = {
    attach: function (context) {
      Drupal.facets.makeCollapsibleCheckboxes(context);
    }
  };

  /**
   * Turns all facet links into checkboxes.
   */
  Drupal.facets.makeCollapsibleCheckboxes = function (context) {
    // Find all checkbox facet links and give them a checkbox.
    var $checkboxWidgets = $('.icdc-facets-widget-collapsible_checkbox', context)
      .once('facets-collapsible-checkbox-transform');
    if ($checkboxWidgets.length > 0) {
      $checkboxWidgets.each(function (index, widget) {
        var $widget = $(widget),
          $container = $('.icdc-facets-collapsible-container', $widget),
          $collapsibleLink = $('.icdc-facets-collapsible_checkbox-title', $widget),
          $applyButton = $('input[type="submit"]', $container),
          $facetId = $('.item-list__collapsible_checkbox', $widget).data('drupal-facet-id');

        $container.hide();
        $collapsibleLink.click(function() {
          $(this).closest('.block-facets').siblings('.block-facets').find('.icdc-facets-collapsible-container').hide().attr('aria-expanded','false');
          $container.slideToggle(function () {
            if($(this).is(":visible")) {
              $(this).attr('aria-expanded', 'true');
            }else {
              $(this).attr('aria-expanded', 'false');
            }
          });

        });
        $applyButton.click(function(e) {
          e.stopImmediatePropagation();
          e.preventDefault();
          $container.slideUp();
          var $params = [];
          $.each(drupalSettings.facets[$facetId].urlParams, function(index, item) {
            if(item.indexOf($facetId) != -1) {
              var $elem = $('input[type="checkbox"][value="' + item + '"]:checked');
              if($elem.length != 0) {
                $params.push(item);
              }
            }
            else {
              $params.push(item);
            }
          });
          var $elems = $('input[type="checkbox"][value^="' + $facetId + '"]:checked');
          $elems.each(function(index) {
            if($params.indexOf($(this).val()) === -1){
              $params.push($(this).val());
            }
          });
          var url = drupalSettings.facets[$facetId].baseUrl;
          var urlHasParam = url.indexOf('?') === -1 ? false: true;
          $.each($params, function(index, item) {
            url += (index === 0 && !urlHasParam ? '?' : '&') + 'f[' + index + ']=' + item;
          });
          window.location.href = url;
          return false;
        })
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
