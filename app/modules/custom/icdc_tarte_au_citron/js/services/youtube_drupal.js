//override tarteaucitron to add title for accessibility reason
tarteaucitron.services.youtube_drupal = {
  "key": "youtube_drupal",
  "type": "video",
  "name": "YouTube",
  "uri": "https://policies.google.com/privacy",
  "needConsent": true,
  "cookies": ['VISITOR_INFO1_LIVE', 'YSC', 'PREF', 'GEUP'],
  "js": function () {
    "use strict";
    tarteaucitron.fallback(['youtube_player'], function (x) {
      var video_id = x.getAttribute("videoID"),
        video_title = x.getAttribute("title"),
        frame_title = 'title=',
        video_width = x.getAttribute("width"),
        frame_width = 'width=',
        video_height = x.getAttribute("height"),
        frame_height = 'height=',
        video_frame,
        params = 'theme=' + x.getAttribute("theme") + '&rel=' + x.getAttribute("rel") + '&controls=' + x.getAttribute("controls") + '&showinfo=' + x.getAttribute("showinfo") + '&autoplay=' + x.getAttribute("autoplay");

      if (video_id === undefined) {
        return "";
      }
      if (video_width !== undefined) {
        frame_width += '"' + video_width + '" ';
      } else {
        frame_width += '"" ';
      }
      if (video_height !== undefined) {
        frame_height +=  '"' + video_height + '" ';
      } else {
        frame_height += '"" ';
      }
      if (video_title !== undefined) {
        frame_title +=  '"' + video_title + '" ';
      } else {
        frame_title = '';
      }
      video_frame = '<iframe type="text/html" ' + frame_title + frame_width + frame_height + ' src="//www.youtube-nocookie.com/embed/' + video_id + '?' + params + '" frameborder="0" allowfullscreen></iframe>';
      return video_frame;
    });
  },
  "fallback": function () {
    "use strict";
    var id = 'youtube_drupal';
    tarteaucitron.fallback(['youtube_player'], function (elem) {
      elem.style.width = elem.getAttribute('width') + 'px';
      elem.style.height = elem.getAttribute('height') + 'px';
      return tarteaucitron.engage(id);
    });
  }
};
