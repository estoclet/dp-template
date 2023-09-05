(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.apercu_view_mode = {
    attach: function (context, settings) {
      $('.apercu-view-mode-iframe').once('apercu-view-mode-iframe-loaded').on("load", function(e) {
        $(this).height($(this).contents().find("body").height());
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
