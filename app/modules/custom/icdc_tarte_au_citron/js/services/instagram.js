// instagram
tarteaucitron.services.instagram = {
  "key": "instagram",
  "type": "social",
  "name": "Instagram",
  "uri": "https://help.instagram.com/155833707900388",
  "needConsent": true,
  "cookies": ['csrftoken', 'datr', 'ig_cb', 'ig_did' , 'mid', 'rur', 'urlgen'],
  "js": function () {
    "use strict";
    tarteaucitron.fallback(['instagram-media'], '');
    tarteaucitron.addScript('//platform.instagram.com/en_US/embeds.js', '', function () {
      instgrm.Embeds.process();
    });
  },
  "fallback": function () {
    "use strict";
    var id = 'instagram';
    tarteaucitron.fallback(['instagram-media'], function (elem){
      elem.style.width = elem.getAttribute('width') + 'px';
      elem.style.height = elem.getAttribute('height') + 'px';
      return tarteaucitron.engage(id);
    });
  }
};
