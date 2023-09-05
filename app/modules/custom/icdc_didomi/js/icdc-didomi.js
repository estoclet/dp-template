/**
 * @file
 */

(function ($, Drupal, drupalSettings, cookies) {

  'use strict';

  Drupal.behaviors.icdcDidomi = {
    attach: function (context, settings) {
      const OPEN_PREFERENCES_SELETOR = '.icdc-didomi-consent-preferences';
      // Display Didomi preferences board.
      $(context).find(OPEN_PREFERENCES_SELETOR).on('click', function () {
        Didomi.preferences.show();
      });
    }
  }

  Drupal.icdcDidomiCookies = {
    providers: drupalSettings.icdcDidomiProviders,
    set: function (cname, cvalue, parameters) {
      if (this.isAuthorized(cname)) {
        cookies.set(cname, cvalue, parameters);
      }
    },
    get: function (cname) {
      if (this.isAuthorized(cname)) {
        return cookies.get(cname);
      } else {
        cookies.remove(cname);
      }
    },
    isAuthorized: function (cname) {
      let cookie = this.find(cname);
      if (cookie) {
        if (this.hasRequireConsent(cookie)) {
          if (cookie.hasOwnProperty('purposeId')) {
            var result = Didomi.getUserConsentStatusForPurpose(cookie.purposeId);
            if (result == false) {
              return false;
            }
          }
        }
      }
      return true;
    },
    find: function (cname) {
      for (const [key, value] of Object.entries(this.providers)) {
        if (value.hasOwnProperty('cookies')) {
          for (const [ckey, cvalue] of Object.entries(value.cookies)) {
            if (cvalue.hasOwnProperty('name') && cname.match(cvalue.name)) {
              return cvalue;
            }
          }
        }
      }
      return null;
    },
    hasRequireConsent: function (cookie) {
      if (cookie) {
        if (cookie.hasOwnProperty('constent') && cookie.constent === true) {
          return true;
        }
      }
      return false;
    },
  }
  setTimeout(function () {
    if (typeof Didomi !== 'undefined') {
      Didomi.getObservableOnUserConsentStatusForVendor('c:auscha_cdc').subscribe(function (consentStatusForVendor) {
        setTimeout(function () {
          if (consentStatusForVendor == true) {
            jQuery('div[data-vendor="c:auscha_cdc"]').show();
            jQuery('.ausha-cookie').hide();
            jQuery('.media-content .mt-2').hide();
            jQuery('.ausha-content .lead').removeClass("d-sm-block").hide();
            jQuery('.media-content .lead').removeClass("d-sm-block").hide();
          }
          // else if (consentStatusForVendor == undefined) {
          else {
            jQuery('[data-vendor="c:auscha_cdc"]').parents(".media-content").addClass("ausha-content");
            jQuery('.ausha-iframe').hide();
            jQuery('div[data-vendor="c:auscha_cdc"]').hide();
            jQuery('.ausha-cookie').show();
            jQuery('.media-content .mt-2').show();
            jQuery('.ausha-content .lead').show();
            jQuery('.media-content .lead').addClass("d-sm-block").show();
          }
        }, 200);
      })
      Didomi.getObservableOnUserConsentStatusForVendor('c:soundcloud-zwnLCUDR').subscribe(function (consentStatusForVendor) {
        setTimeout(function () {
          if (consentStatusForVendor == true) {
            jQuery('.soundcloud-iframe').show();
            jQuery('.soundcloud-iframe').css("display", "block");
            jQuery('.soundcloud-cookie').hide();
            jQuery('.soundcloud-cookie').css("display", "non");
            jQuery('.soundcloud-content .lead').hide();
            jQuery('.soundcloud-content .lead').css("display", "non");
          }
          else {
            jQuery('.soundcloud-iframe').hide();
            jQuery('.soundcloud-cookie').show();
            jQuery('.soundcloud-content .lead').show();
          }
        }, 600);
      })
    } else {
      jQuery('.ausha-iframe').hide();
      jQuery('div[data-vendor="c:auscha_cdc"]').hide();
      jQuery('[data-vendor="c:auscha_cdc"]').parents(".media-content").addClass("ausha-content");
      jQuery('.ausha-cookie').show();
      jQuery('.media-content .mt-2').show();
      jQuery('.ausha-content .lead').show();
      jQuery('.media-content .lead').addClass("d-sm-block").show();
      jQuery('.soundcloud-iframe').hide();
      jQuery('.soundcloud-cookie').show();
      jQuery('.soundcloud-content .lead').show();
    }
  }, 600);

})(jQuery, Drupal, drupalSettings, window.Cookies);
