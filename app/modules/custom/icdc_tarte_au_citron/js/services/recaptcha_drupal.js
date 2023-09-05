// recaptcha
tarteaucitron.services.recaptcha_drupal = {
  "key": "recaptcha_drupal",
  "type": "api",
  "name": "reCAPTCHA",
  "uri": "https://policies.google.com/privacy",
  "needConsent": true,
  "cookies": ['nid'],
  "js": function () {
    "use strict";
    tarteaucitron.fallback(['g-recaptcha'], '');
    if(typeof grecaptcha === "undefined") {
      window.tacRecaptchaOnLoad = tarteaucitron.user.recaptchaOnLoad || function() {
        var elements = document.getElementsByClassName('g-recaptcha');
        for (var i = 0; i < elements.length; ++i) {
          var item = elements[i];
          var params = {
            'sitekey' : item.getAttribute('data-sitekey')
          }

          var data_theme = item.getAttribute('data-sitekey'),
            data_type = item.getAttribute('data-type'),
            data_size = item.getAttribute('data-size'),
            data_tabindex = item.getAttribute('data-tabindex');
          if(data_theme) {
            params.theme = data_theme;
          }
          if(data_type) {
            params.type = data_type;
          }
          if(data_size) {
            params.size = data_size;
          }
          if(data_tabindex) {
            params.tabindex = data_tabindex;
          }
          grecaptcha.render(item, params);
          item.setAttribute('class', 'g-recaptcha-processed');
        }
      };
      tarteaucitron.addScript('https://www.google.com/recaptcha/api.js?onload=tacRecaptchaOnLoad&render=explicit');
    }
    else if(typeof tacRecaptchaOnLoad === "function") {
      tacRecaptchaOnLoad();
    }
  },
  "fallback": function () {
    "use strict";
    var id = 'recaptcha_drupal';
    tarteaucitron.fallback(['g-recaptcha'], tarteaucitron.engage(id));
  }
};
