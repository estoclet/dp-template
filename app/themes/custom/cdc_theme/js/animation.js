(function ($, Drupal) {
  'use strict';

  var getViewPort = function () {
    return {
      width: Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
      height: Math.max(document.documentElement.clientHeight, window.innerHeight || 0)
    };
  };

  function mobilePrincipalMenu(){
    var dimension = getViewPort();
    if (dimension.width <= 767) {
      $('#block-cdc-theme-main-menu, #block-navigationprofilee').attr('aria-hidden', 'true');
    } else {
      $('#block-cdc-theme-main-menu, #block-navigationprofilee').removeAttr('aria-hidden')
    }
  }

  /* Animation header home */
  function animationHome() {
    var dimension = getViewPort();
    if (dimension.width > 767 && $('.region-highlighted .block-entity-viewnode .node--type-accueil').length) {
      setTimeout(function () {
        $('.region-highlighted .block-entity-viewnode .node--type-accueil').addClass('show');
      }, 1000);
      setTimeout(function () {
        $('.header-navigation-secondary').addClass('show');
        $('#block-cdc-theme-main-menu').addClass('show');
        $('#block-navigationprofilee').addClass('show');
      }, 1500);
    }
    if (dimension.width <= 767){
      $('#block-cdc-theme-main-menu, #block-navigationprofilee').attr('aria-hidden','true');
    }else{

    }
    // Animation Partage header
    $('.header-navigation-secondary .social-buttons-title').on('click', function () {
      $('.header-navigation-secondary .social-buttons-links').toggleClass('show');
    });

    // Animation progress bar / sommaire
    var dimension = getViewPort();
    var menu = $('.block-views-blocksommaire-block-1 .views-field-field-sommaire');
    if (dimension.width > 767 && menu.length) {
      $('.footer').addClass('margin-progress');
      var elementOffset = menu.offset().top + menu.outerHeight();
      var ids = [];
      $('.block-views-blocksommaire-block-1 .views-field-field-sommaire li').each(function (n) {
        $(this).children('a').attr("position", n);
        var href = $(this).children('a').attr("href");
        ids.push([href, n]);
      });

      $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        var scrollBottom = $(window).scrollTop() + $(window).height();
        if ((elementOffset) < scrollBottom) {
          $('.block-views-blocksommaire-block-1').addClass('fixed');
        }
        if ((elementOffset) >= scrollBottom) {
          $('.block-views-blocksommaire-block-1').removeClass('fixed');
        }

        $.each( ids, function (i) {
          var id = $(this)[0];
          var pos = $(this)[1];
          var count = $('.block-views-blocksommaire-block-1 .views-field-field-sommaire ul').children().length;
          var bar = (pos / (count - 1)) * 100;
          if ($(id).length) {
            var top = $(id).offset().top - 100;
            if (top < scrollTop) {
              $(".block-views-blocksommaire-block-1 .progress-bar").attr('style', 'width:' + bar + '%');
            }
            if (pos == 0) {
              $(".block-views-blocksommaire-block-1 .progress-bar").attr('style', 'width: 11px');
            }
          }
        });

        // progress bar on scroll
        //var d = $(document).height(), c = $(window).height();
        //var p = (scrollTop / (d - c)) * 100;
        //$(".block-views-blocksommaire-block-1 .progress-bar").attr('style', 'width:' + p + '%');

      });

      $('.block-views-blocksommaire-block-1 .views-field-field-sommaire a').on("click", function (e) {
        e.preventDefault();
        var id = $(this).attr('href');
        if ($(id).length) {
          var top = $(id).offset().top ;
          $('html, body').animate({
            scrollTop: top
          }, 1200, 'easeOutQuart');
        }
      });
    }
  };

  /* Animation footer typing #icdcDecisionTree */
  function animationTyping() {
    var dimension = getViewPort();

    function showAnimation() {
      $('#icdcDecisionTree .btn-primary').addClass('show');
      setTimeout(function () {
        $('.page-node-type-accueil .decision-tree-body .leftList').addClass('show');
      }, 2750);
      setTimeout(function () {
        $('.page-node-type-accueil .decision-tree-body .rightList').addClass('show');
      }, 3000);
    }

    if (dimension.width > 767 && $('#icdcDecisionTree').length >0) {
      var scrollTop = $(window).scrollTop();
      var height = $(window).height();
      var elementOffset = $('#icdcDecisionTree').offset().top;
      var trigger = $(window).height() - 20;

      if ((elementOffset < (height + scrollTop)) && (elementOffset > scrollTop)) {
        showAnimation();
      }

      $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        var distance = (elementOffset - scrollTop);
        if (distance <= trigger) {
          showAnimation();
        }
      });
    }
  };

  /* Animation h2.block-title + bloquote + image */
  function animationRubic() {
    var dimension = getViewPort();

    function showAnimation(element) {
      $(element).addClass('show');
    }

    function getElementOffset(element) {
      var elementOffset = element.offset().top;
      element.attr("data-offset", elementOffset);
      return elementOffset;
    }

    if (dimension.width > 767) {
      var scrollTop = $(window).scrollTop();
      var height = $(window).height();
      var trigger = $(window).height() - 20;
      var elementsOffset = [];
      var elementOffset;

      $("h2.animation").each(function () {
        var offset = getElementOffset($(this));
        elementsOffset.push($(this));

        if ((offset < (height + scrollTop)) && (offset > scrollTop)) {
          showAnimation($(this));
        }
      });

      $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        $(elementsOffset).each(function () {
          var distance = ($(this).attr("data-offset") - scrollTop);
          if (distance <= trigger) {
            showAnimation($(this));
          }
        });
      });
    }
  };

  /* Animation Carte de France */
  function animationMap() {
    var dimension = getViewPort();
    if (dimension.width > 767 && $('.header-parcours-vie').length) {
      // Animation tracés
      function pathPrepare($el) {
        var lineLength = 0;
        if($el.length > 0) {
          lineLength = $el[0].getTotalLength();
        }
        $el.css("stroke-dasharray", lineLength);
        $el.css("stroke-dashoffset", lineLength);
      }
      var $france = $(".header-parcours-vie path#france");
      var $corse = $(".header-parcours-vie path#corse");
      var $domtom1 = $(".header-parcours-vie path#domtom1");
      var $domtom2 = $(".header-parcours-vie path#domtom2");
      var $domtom3 = $(".header-parcours-vie path#domtom3");
      var $domtom4 = $(".header-parcours-vie path#domtom4");
      var $domtom5 = $(".header-parcours-vie path#domtom5");
      var $domtom6 = $(".header-parcours-vie path#domtom6");
      var $domtom7 = $(".header-parcours-vie path#domtom7");
      var $domtom8 = $(".header-parcours-vie path#domtom8");

      pathPrepare($france);
      pathPrepare($corse);
      pathPrepare($domtom1);
      pathPrepare($domtom2);
      pathPrepare($domtom3);
      pathPrepare($domtom4);
      pathPrepare($domtom5);
      pathPrepare($domtom6);
      pathPrepare($domtom7);
      pathPrepare($domtom8);

      var controller = new ScrollMagic.Controller();
      var tween = new TimelineMax()
        .add(TweenMax.to($france, 6, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($corse, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom8, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom7, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom6, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom5, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom4, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom3, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom2, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }))
        .add(TweenMax.to($domtom1, 0.5, { strokeDashoffset: 0, ease: Linear.easeNone }));
      var scene = new ScrollMagic.Scene({ tweenChanges: true })
        .setTween(tween)
        .addTo(controller);

      //Animation zones
      setTimeout(function () {
        $('.header-parcours-vie path#zone2').addClass('show');
      }, 4000);
      setTimeout(function () {
        $('.header-parcours-vie path#zone3').addClass('show');
      }, 5500);
      setTimeout(function () {
        $('.header-parcours-vie path#zone4').addClass('show');
      }, 6000);
      setTimeout(function () {
        $('.header-parcours-vie path#zone5').addClass('show');
      }, 8000);
    }
  };

  function minisiteancor() {
    $('.field--type-text-with-summary a, .minisite-container-menu a').each(function () {
      $(this).on("click", function (e) {
        var id = $(this).attr('href');
        if (id.indexOf("#") == 0){
          e.preventDefault();

          if ($(id).length > 0) {
            $('html,body').animate({
              scrollTop: $(id).parents('.wrapper-large').offset().top
            }, 1200, 'easeOutQuart');
          }
        }
      });
    });
  }

  function videoText(){
    $('.display-transcription').on('click', function() {
      $(this).next('.transcrition').toggleClass('open');
    });
  }

function allAuthors(){
  $('.view-blog .menu--menu-blog ul').append($('.all-authors-item'));
}

  $(document).ready(function () {
    animationHome();
    animationTyping();
    animationRubic();
    minisiteancor();
    videoText();
    allAuthors();
    mobilePrincipalMenu();
    let map = document.querySelector('.header-parcours-vie path#france');
    if (typeof map !== 'undefined' && map !== null) {
      animationMap();
    }
    $(window).on('resize', function(){
      mobilePrincipalMenu();
    });
  });
})(jQuery, Drupal);
