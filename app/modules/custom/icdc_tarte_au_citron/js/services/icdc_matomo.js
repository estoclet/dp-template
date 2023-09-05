tarteaucitron.services.icdc_matomo = {
  "key": "icdc_matomo",
  "type": "analytic",
  "name": "Matomo (formerly known as Piwik)",
  "uri": "https://matomo.org/faq/general/faq_146/",
  "needConsent": false,
  "cookies": ['_pk_ref', '_pk_cvar', '_pk_id', '_pk_ses', '_pk_hsr', 'piwik_ignore', '_pk_uid'],
  "js": function () {
    "use strict";
    if (!tarteaucitron.user.url_tms) {
      return;
    }
    var _mtm = _mtm || [];
    _mtm.push({'mtm.startTime': (new Date().getTime()), 'event': 'mtm.Start'});
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=tarteaucitron.user.url_tms; s.parentNode.insertBefore(g,s);
  }
};
