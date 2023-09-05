// twitter
tarteaucitron.services.twitter_drupal = {
  "key": "twitter_drupal",
  "type": "social",
  "name": "Twitter",
  "uri": "https://support.twitter.com/articles/20170514",
  "needConsent": true,
  "cookies": [],
  "js": function () {
    "use strict";
    tarteaucitron.fallback(['twitter-tweet'], function (x) {
      var html = '<a href="' + x.getAttribute('data-path') + '"></a>';
      return html;
    });
    tarteaucitron.addScript('//platform.twitter.com/widgets.js', 'twitter-wjs', function () {
      if(twttr) {
        twttr.widgets.load();
      }
    });
  },
  "fallback": function () {
    "use strict";
    var id = 'twitter_drupal';
    tarteaucitron.fallback(['twitter-tweet'], function (elem){
      return tarteaucitron.engage(id);
    });
  }
};
