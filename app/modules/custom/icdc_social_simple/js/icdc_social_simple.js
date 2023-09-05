/**
 * @file
 * Provides the functionality to the social links buttons.
 */

(function ($) {
  'use strict';
  Drupal.behaviors.icdc_social_simple = {
    attach: function (context, settings) {
      var base_url = window.location.origin
        ? window.location.origin
        : window.location.protocol + '/' + window.location.host;
      $('.social-buttons-links a', context).once('icdc-social-buttons-link').each(function () {
        $(this, context).attr('href', $(this, context).attr('href').replace('___BASE_URL___', base_url));
      });
    }
  }

}(jQuery));
