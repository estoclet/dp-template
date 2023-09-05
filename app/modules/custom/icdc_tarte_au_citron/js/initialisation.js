(function (drupalSettings, Drupal, $) {
  'use strict';

  // force English in order for strings to be localized.
  tarteaucitronForceLanguage = "en";

  // Add custom translated texts to tarteaucitron.
  tarteaucitronCustomText = drupalSettings.icdc_tarte_au_citron.texts;

  tarteaucitron.init({
    "privacyUrl": drupalSettings.icdc_tarte_au_citron.privacyUrl, /* Privacy policy url */

    "hashtag": drupalSettings.icdc_tarte_au_citron.hashtag, /* Open the panel with this hashtag */
    "cookieName": drupalSettings.icdc_tarte_au_citron.cookieName, /* Cookie name */

    "orientation": drupalSettings.icdc_tarte_au_citron.orientation, /* Banner position (top - bottom) */
    "showAlertSmall": drupalSettings.icdc_tarte_au_citron.showAlertSmall ? true : false, /* Show the small banner on bottom right */
    "cookieslist": drupalSettings.icdc_tarte_au_citron.cookieslist ? true : false, /* Show the cookie list */

    "adblocker": drupalSettings.icdc_tarte_au_citron.adblocker ? true : false, /* Show a Warning if an adblocker is detected */
    "AcceptAllCta" : drupalSettings.icdc_tarte_au_citron.AcceptAllCta ? true : false, /* Show the accept all button when highPrivacy on */
    "highPrivacy": drupalSettings.icdc_tarte_au_citron.highPrivacy ? true : false, /* Disable auto consent */
    "handleBrowserDNTRequest": drupalSettings.icdc_tarte_au_citron.handleBrowserDNTRequest ? true : false, /* If Do Not Track == 1, disallow all */

    "removeCredit": drupalSettings.icdc_tarte_au_citron.removeCredit ? true : false, /* Remove credit link */
    "moreInfoLink": drupalSettings.icdc_tarte_au_citron.moreInfoLink ? true : false, /* Show more info link */
    "useExternalCss": drupalSettings.icdc_tarte_au_citron.useExternalCss ? true : false, /* If false, the tarteaucitron.css file will be loaded */

    "cookieDomain": drupalSettings.icdc_tarte_au_citron.cookieDomain === '' ? '' : drupalSettings.icdc_tarte_au_citron.cookieDomain, /* Shared cookie for multisite */

    "readmoreLink": drupalSettings.icdc_tarte_au_citron.readmoreLink /* Change the default readmore link */
  });
  Drupal.behaviors.icdc_tarte_au_citron  = {
    attach: function (context, settings) {
      tarteaucitron.job = tarteaucitron.job || [];
      tarteaucitron.user = drupalSettings.icdc_tarte_au_citron.user;
      settings.icdc_tarte_au_citron.services.forEach(function (item, index) {
        tarteaucitron.job.push(item);
      });
    },
  };
})(drupalSettings, Drupal, jQuery);



