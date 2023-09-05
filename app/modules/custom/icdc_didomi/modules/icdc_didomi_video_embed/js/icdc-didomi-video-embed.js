/**
 * @file
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.didomiVideoEmbed = {
    attach: function (context, settings) {
      window.didomiOnReady = window.didomiOnReady || [];
      window.didomiOnReady.push(function (Didomi) {
        if (Didomi.isConsentRequired()) {
          // Consent is required: your visitor is from the EU or you are an EU company
          if (settings.icdc_didomi_embed.providers) {
            let providers = settings.icdc_didomi_embed.providers;
            for (const [key, provider] of Object.entries(providers)) {
              // Only enable the vendor when consent is given
              Didomi.getObservableOnUserConsentStatusForVendor(provider.vendor_id)
                .filter(function (status) { return status === true; }) // Filter out updates where status is not true
                .subscribe(function (consentStatusForVendor) {
                  // The user has given consent to the vendor
                  // Enable it
                  var elementsToHide = document.querySelectorAll(`[data-blocked-content="${provider.vendor_id}"]`); //elementsToHide is an array
                  for (var i = 0; i < elementsToHide.length; i++) {
                    elementsToHide[i].style.visibility = "hidden";
                    elementsToHide[i].style.display = "none";
                  }
                  // set options
                  if (provider.parent_id) {
                    var iframe = document.querySelectorAll(`#${provider.parent_id} iframe`)[0];
                    //isElementVisible
                    if (iframe.offsetParent) {
                      if (provider.options.setAttribute) {
                        for (const [key, value] of Object.entries(provider.options.setAttribute)) {
                          iframe.setAttribute(key, value);
                        }
                      }
                    }
                  }

                });
            }

          }

        }
      });
    }
  }

})(jQuery, Drupal);
