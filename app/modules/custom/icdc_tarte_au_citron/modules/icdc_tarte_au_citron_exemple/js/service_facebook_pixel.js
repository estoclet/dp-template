(function (drupalSettings, Drupal) {
  'use strict';
  tarteaucitron.user.facebookpixelMore = function () {
    console.log(drupalSettings);
    fbq('track', drupalSettings.icdc_tarte_au_citron_exemple.pageName);
  };
})(drupalSettings, Drupal);
