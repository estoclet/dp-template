(function ($, Drupal) {
  'use strict';

  Drupal.icdc_theme = Drupal.icdc_theme || {};

  Drupal.icdc_theme.getViewPort = function() {
    return {
      width: Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
      height: Math.max(document.documentElement.clientHeight, window.innerHeight || 0)
    };
  };

  Drupal.behaviors.alterDatePicker = {
    attach: function (context, settings) {
      $.datepicker._defaults.changeMonth = true;
      $.datepicker._defaults.changeYear = true;
      // adjust year range
      $.datepicker._defaults.yearRange = "c-80:c+10";
    }
  };


  Drupal.behaviors.counterUp = {
    attach: function (context, settings) {
      $(context).find(".chiffre").once('myThemeCounter').counterUp({
        delay: 10,
        time: 1000,
        formatter: function (chiffre) {
          var numbers = [], i;
          var currentIdx = chiffre.length % 3;
          var nbrLoops = Math.floor(chiffre.length / 3);

          if (chiffre.length > 3) {
            if (currentIdx > 0) {
              numbers.push(chiffre.substring(0, currentIdx));
            }

            for (i = 1; i <= nbrLoops; i++) {
              numbers.push(chiffre.substring(currentIdx, currentIdx + 3));
              currentIdx = currentIdx + 3;
            }

            chiffre = numbers.join('');
          }
          chiffre = chiffre.replace('.',',');

          return chiffre;
        }
      });
    }
  };

  Drupal.behaviors.socialMediaBtns = {
    attach: function (context, settings) {
      //$('.social-buttons').find(".social-buttons-links").attr('aria-expanded','false');
      $('.social-buttons').find(".social-buttons-title").attr({
        'aria-expanded': 'false'
      }).on("keydown", function (event) {
        // Check to see if space or enter were pressed
        // "Spacebar" for IE11 support
        if (event.key === " " || event.key === "Enter" || event.key === "Spacebar") {
          // Prevent the default action to stop scrolling when space is pressed
          event.preventDefault();
          //toggleButton(event.target);
          var elemP = $(event.target).find('p')
          handleCommand(elemP);
        }
      }).on("click", function (event){
        handleCommand(event.target);
      });

      function handleCommand(element){
        var $shareElm = $("<li class='btn-partager-ctn'><button type='button' class='btn-partager'><i class='cdcicon cdcicon-partager'></i></button></li>");
        var $elm = $(element);

        // var $socialNetworks = $elm.siblings('ul').first();
        var $title = $elm.parent('.social-buttons-title');
        var $socialNetworks = $title.siblings('ul').first();


        $shareElm.on('click', function () {
          if ($socialNetworks.hasClass('displayed')) {
            $socialNetworks.removeClass('displayed');
            $title.addClass('displayed');
            $socialNetworks.find('li.btn-partager').remove();
            $title.attr('aria-expanded', 'false');
          }
        });

        if (!$socialNetworks.hasClass('displayed')) {
          $socialNetworks.addClass('displayed');
          $title.removeClass('displayed');
          //$socialNetworks.fadeIn('slow');
          $title.attr('aria-expanded', 'true');

          if ($socialNetworks.find('li.btn-partager-ctn').length === 0) {
            $socialNetworks.prepend($shareElm);
          }
        }
      }
    }
  };

  Drupal.behaviors.tnsComex = {
    attach: function(context, settings) {
      var $sections = $(context).find(".block-comex");
      var $comexBlocs = $sections.find(".cdc-tns-comex");

      if ($comexBlocs.length > 0) {
        $comexBlocs.once('myThemeSlider').each(function (idx, elem) {
          var slider = tns({
            container: elem,
            items: 1,
            slideBy: 'page',
            navPosition: 'bottom',
            gutter: 20,
            controlsText: ['<i class="cdcicon cdcicon-chevron"></i>', '<i class="cdcicon cdcicon-chevron"></i>'],
            responsive: {
              768: {
                items: 3
              },
              1024: {
                items: 5
              }
            }
          });

          var sliderA11Y = tinySliderA11Y({
            tns: slider,
            parentSelectorType: 'element',
            parent: elem.closest(".tns-parent"),
            parentLabelText: $(elem.closest(".tns-parent")).attr('aria-label'),
            slideLabel: ".auteur",
            navText: '$page/$total'
          });
        });
      }
    }
  };

  Drupal.behaviors.tnsParagraphImageDiaporama = {
    attach: function (context, settings) {
      var $tnsDiapo = $(context).find(".cdc-tns-diapo");

      if ($tnsDiapo.length > 0) {
        $tnsDiapo.once('myThemeParagraphImageDiaporama').each(function (idx, diapo) {
          var slider = tns({
            container: diapo,
            items: 1,
            slideBy: 'page',
            navPosition: 'bottom',
            navContainer: '#customize-thumbnails',
            navAsThumbnails: true,
            controlsText: ['<i class="cdcicon cdcicon-chevron"></i>', '<i class="cdcicon cdcicon-chevron"></i>'],
          });
          var sliderA11Y = tinySliderA11Y({
            tns: slider,
            parentSelectorType: 'element',
            parent: diapo.closest(".tns-parent"),
            parentLabelText: $(diapo.closest(".tns-parent")).attr('aria-label'),
            slideLabel: ".auteur",
            navText: '$page/$total'
          });
        });
      }
    }
  };

  Drupal.behaviors.blocSwipeHorizontal = {
    attach: function (context, settings) {
      var $sections = $(context).find(".block-inline-blockbloc-swipe-horizontal");
      var $blocSwipeHorizontal = $sections.find(".block-swipe-horizontal");

      if ($blocSwipeHorizontal.length > 0) {
        $blocSwipeHorizontal.once('myThemeSwipeHorizontal').each(function (idx, bloc) {
          $(bloc).mCustomScrollbar({
            mouseWheel: { enable: false },
            axis: 'x',
            scrollButtons: {
              enable: 1,
              scrollAmount: 20
            },
            scrollbarPosition: 'outside'
          });
        });
      }
    }
  };

  /* Switcher langue */
  function switchLangue() {
    var langueActive = $('.block-language').find('.links').find('a.is-active');
    var attrLangueActive = langueActive.attr('hreflang');
    $('.block-language').find('a.dropdown-toggle').text(attrLangueActive);
  }

  /* Deplacement du lien TOUT du menu blog */
  function linkAllBlog() {
    if ($('.block-views-blockblog-term-block-view-blog-menu').length > 0) {
      var moreLink = $('.block-views-blockblog-term-block-view-blog-menu').find('.more-link').html();
      $('.block-views-blockblog-term-block-view-blog-menu .menu--menu-blog ul').prepend('<li>' + moreLink + '</li>');
    }
  }

  Drupal.behaviors.MenuRSA11y = {
    attach: function (context, settings) {
      $(context).find(".navbar-rs li a").once('menursa11y').each(function () {
        $(this).wrapInner('<span class="sr-only"></span>');
      });
    }
  };

  Drupal.behaviors.stickyMenuOnScroll = {
    attach: function (context, settings) {
      var $nodeAccueil = $(context).find("body.page-node-type-accueil");
      var $header = $(context).find("header#navbar");
      var $menu = $(context).find(".main-container-heading");
      var ua = navigator.userAgent;
      var isIe = ua.indexOf("MSIE ") > -1 || ua.indexOf("Trident/") > -1;
      var lastScrollTop = 0;

      if ($nodeAccueil.length < 1) {
        $(window).once('myThemeStickyMenu').scroll(function (event) {
          const deltaMenu = 200;//necessary value to remove the sticky state from menu due to IE
          var st = $(this).scrollTop();

          if (st <= deltaMenu) {
            $header.removeClass('sticky-header');
            $menu.removeClass('sticky-menu');
          } else if (
            st > deltaMenu &&
            st > lastScrollTop
          ) {
            $header.removeClass('sticky-header');
            $menu.removeClass('sticky-menu');
          } else if (isIe && st !== lastScrollTop) {
            $header.addClass('sticky-header');
            $menu.addClass('sticky-menu');
          } else if (!isIe) {
            $header.addClass('sticky-header');
            $menu.addClass('sticky-menu');
          }

          lastScrollTop = st;
        });
      }
    }
  };

  /* parallaxe page home*/
  function stratesWipeOnScroll() {
    var $nodeAccueil = $("body.page-node-type-accueil");
    var $editMode = $nodeAccueil.find(".layout-builder__add-section");
    var slides = document.querySelectorAll("div.layout");
    var dimensions = Drupal.icdc_theme.getViewPort();
    var heightElement = null;
    var controller = new ScrollMagic.Controller({
      globalSceneOptions: {
        triggerHook: 'onLeave',
        duration: "200%"
      }
    });



    if (
      $nodeAccueil.length > 0 &&
      $editMode.length < 1 &&
      dimensions.width > 991
    ) {
      for (var i=0; i<slides.length; i++) {
        heightElement = $(slides[i]).css('height').replace('px', '');

        if (heightElement < dimensions.height) {
          $(slides[i]).css('height', dimensions.height + 'px');
        }

        if ($(slides[i]).parent(".scrollmagic-pin-spacer").length < 1) {
          new ScrollMagic.Scene({
            triggerElement: slides[i]
          })
            .setPin(slides[i], {pushFollowers: false})
            .addTo(controller);
        }
      }
    } else {
      controller.destroy(true);
      controller = null;
    }
  }

  /* resizing Homepage Blocks */
  function resizeHomeBlocks() {
    var
      $nodeAccueil = $("body.page-node-type-accueil"),
      dimensions = Drupal.icdc_theme.getViewPort();

    if (
      $nodeAccueil.length > 0
    ) {
      // All block
      $(".layout__region").each(function () {
        var
          $countBlocks = $(this).children('.block').length,
          blocHeight = 0;

        if ($countBlocks == 1){
          var
            $blocView = $(this).find('.block-views'),
            marginTop = 0;

          blocHeight = $blocView.innerHeight();

          if (blocHeight < dimensions.height && dimensions.width > 991 ) {
            marginTop = (dimensions.height - blocHeight)/2;
            $blocView.css({'margin-top': marginTop + 'px'})
          }
          else {
            $blocView.css({'margin-top': ''})
          }
        }
      });

      // Bloc CTA dossier
      $(".block-inline-blockbloc-cta-dossier").each(function () {
        var
          $svg = $(this).find('.wrapper-texte'),
          blocHeight = 0,
          blocWidth = $(this).innerWidth(),
          svgPercent = 0;

        if (dimensions.width > 1919) {
          svgPercent = 0.71; // 71%
        } else if (dimensions.width > 1200 && dimensions.width <= 1919) {
          svgPercent = 0.9; // 90%
        } else if (dimensions.width > 767 && dimensions.width < 992 ){ //Tablette
          svgPercent = 0.95; // 95%
        } else if (dimensions.width < 479 ){ // Phone
          svgPercent = 1; // 100%
        } else {
          svgPercent = 0.9; // 81%
        }

        if(dimensions.height < 620 && dimensions.width < 768 ) { // Mobile Only
          blocHeight = 620;
        }
        else {
          blocHeight = dimensions.height;

        }

        $(this).css({'height': blocHeight + 'px'});
        $svg.css({'width': blocWidth * svgPercent + 'px','height': '' });

      });

    }
  }

  /* Resizing Homepage Header*/
  function resizeHomeHeader() {
    var $nodeAccueil = $('body.page-node-type-accueil');
    var $navbar = $('.region-navigation');
    var $headerRegion = $('.region-header');
    var $svgHeader = $('.svg-home');
    var dimensions = Drupal.icdc_theme.getViewPort();
    var height = dimensions.height - $navbar.innerHeight();
    var svgInitialHeight = 1581;
    var svgInitialWidth = 940;
    var svgWidth = svgInitialWidth;
    var svgHeight = svgInitialHeight;
    var svgRatio = (svgInitialHeight / svgInitialWidth);
    var svgPosition = 0;

    // Resizing Header
    if (
      $nodeAccueil.length > 0 &&
      $navbar.length > 0 &&
      $headerRegion.length > 0 &&
      $svgHeader.length > 0
    ) {

      $svgHeader.css({
        'display': 'block',
        'height': height
      });

        // Resize SVG
      if (dimensions.width > 767) { // Desktop + Tablette

        if (dimensions.width > dimensions.height) { // Paysage
          svgWidth = dimensions.width * 0.48 ;
        }
        else { // Portrait
          svgWidth = dimensions.width * 0.6 ;
        }

        if (height < svgInitialHeight ) {
          svgHeight = svgWidth * svgRatio ;
          svgPosition =  height - svgHeight;
        }
      }
      else { // Phone
        svgPosition =  0;
        svgWidth = dimensions.width ;
        svgHeight = svgWidth * svgRatio ;
      }

      if(dimensions.width > 767) {
        $headerRegion.css('height', height + 'px');
        $svgHeader.find('svg').css({
          'width': svgWidth,
          'height': svgHeight,
          'margin-top': svgPosition
        });
        $svgHeader.find('img').css({
          'width': svgWidth-1,
          'height': svgHeight-1,
          'margin-top': svgPosition
        });
      }
      else {
        $headerRegion.css('height', '');
        $svgHeader.find('svg').css({
          'width': '',
          'height': '',
          'margin-top': ''
        });
      }

    }
  }

  function resizeSvgHeaderParcours() {
    var dimensions = Drupal.icdc_theme.getViewPort();
    var $header = $(".header-parcours-vie");

    if($header.length > 0) { // Gestion du svg de l'entête
      var $svgWiredWrapper = $header.find(".field--name-field-pg-svg-wired");
      var $svgPlainWrapper = $header.find(".field--name-field-pg-svg-illustrated");
      var $contentWrapper = $header.find('.wrapper-header');
      var $textWrapper = $contentWrapper.find(".content-left");
      var $node = $("body.page-node-type-parcours-de-vie");
      var textHeight = $textWrapper.outerHeight();
      var svgRatio =  995/2160; // Ratio page taxonomy à partir svg fourni si bug assurez-vous le ratio est identique. Tous les svg doivent avoir le même ratio
      if($node.length > 0) {
        svgRatio =  690/1513; // Ratio page node à partir svg fourni si bug assurez-vous le ratio est identique. Tous les svg doivent avoir le même ratio
      }
      var svgWidth = $contentWrapper.innerWidth();
      var svgHeight = svgWidth*svgRatio;

      if(dimensions.width > 479 && $node.length > 0 ){ // tablette et mobile uniquement pour le type de contenu
        if(svgHeight > textHeight) {
          $contentWrapper.css({
            'height': svgHeight
          });
        }
        else {
          $contentWrapper.css({
            'height': textHeight
          });
        }
      }
      else { // Mobile
        $contentWrapper.css({
          'height': ''
        });
      }


      $svgWiredWrapper.find('.field--name-field-media-image').css({
        'width': svgWidth,
        'height': svgHeight
      });
      $svgPlainWrapper.find('.field--name-field-media-image').css({
        'width': svgWidth,
        'height': svgHeight
      });

    }
  }

  /* Resizing SVG parcours*/
  function resizeSvgStoryParcours() {
    var dimensions = Drupal.icdc_theme.getViewPort();
    var $node = $("body.page-node-type-parcours-de-vie");
    var $story = $node.find(".paragraph--type--story");

    if (
      $node.length > 0
    ) {

      if($story.length > 0) { // Gestion des svgs dans les stories
        $story.each(function() {
          var $this = $(this);
          var $svgWiredWrapper = $this.find(".field--name-field-pg-svg-wired");
          var $svgPlainWrapper = $this.find(".field--name-field-pg-svg-illustrated");
          var $textWrapper = $this.find(".wrapper-texte");
          var $separator = $this.find(".story-separator");
          var textHeight = $textWrapper.outerHeight();
          var textWidth = $textWrapper.innerWidth();
          var svgWidth = $svgPlainWrapper.outerWidth();
          var svgHeight = svgWidth ;  // Get a perfect square
          var separatorHeight = textHeight - svgWidth;

          if(dimensions.width > 991) {
            $svgWiredWrapper.find('.field--name-field-media-image').css({
              'height' : svgHeight,
              'width': svgWidth
            });
            $svgPlainWrapper.find('.field--name-field-media-image').css({
              'height' : svgHeight,
              'width': svgWidth
            });

            if (separatorHeight > 0) {
              $separator.css({
                'height' : separatorHeight
              });
            }
          }
          else {
            $svgWiredWrapper.find('.field--name-field-media-image').css({
              'height' : textWidth,
              'width': textWidth
            });
            $svgPlainWrapper.find('.field--name-field-media-image').css({
              'height' : textWidth,
              'width': textWidth
            });
            $separator.css({
              'height' : ''
            });
          }

        });
      }


    }


  }

  var resizeTimer;
  $(window).on("resize orientationchange", function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      mediathequeMasonry();
      stratesWipeOnScroll();
      resizeHomeHeader();
      resizeHomeBlocks();
      resizeSvgHeaderParcours();
      resizeSvgStoryParcours();
    }, 100);
  }); //end resize

  Drupal.behaviors.stratsInstit = {
    attach: function (context, settings) {
      var dimensions = Drupal.icdc_theme.getViewPort();
      var $blocStratsInsti = $(context).find(".block-strats-instit");

      if ($blocStratsInsti.length > 0) {
        $blocStratsInsti.find('.strats--menu--item').each(function () {
          $(this).find('a').once('myThemeStratsInstitEnter').mouseenter(function () {
            var indexMenuItem = $(this).closest('.strats--menu--item').index();
            $blocStratsInsti.find('.strats--image:eq(' + indexMenuItem + ')').addClass('active');
            $blocStratsInsti.find('.strats--menu--item:eq(' + indexMenuItem + ')').addClass('active');
            $blocStratsInsti.find('.strats--menu').addClass('active');
            $blocStratsInsti.find('.strats--menu--item-chapo:eq(' + indexMenuItem + ')').addClass('active');
          });

          $(this).find('a').once('myThemeStratsInstitLeave').mouseleave(function () {
            $blocStratsInsti.find('.strats--image').removeClass('active');
            $blocStratsInsti.find('.strats--menu').removeClass('active');
            $blocStratsInsti.find('.strats--menu--item').removeClass('active');
            $blocStratsInsti.find('.strats--menu--item-chapo').removeClass('active');
          });
        });


        if (dimensions.width > 991) {
          $blocStratsInsti.css('height', (dimensions.height)  + 'px');
        }
        else {
          $blocStratsInsti.css('height','');
        }

        $(window).once('myThemeStratsInstitResize').on('resize  orientationchange', function () {
          if (dimensions.width > 991) {
            $blocStratsInsti.css('height', (dimensions.height) + 'px');
          }
          else {
            $blocStratsInsti.css('height','');
          }
        });
      }
    }
  };

  function twitterWidth() {
    return $('.twitter-tweet').each(function () {
      $('.twitter-tweet').attr('style', 'display: block !important');

      return $(this.shadowRoot).find('.EmbeddedTweet').css({
        width: '100%',
        maxWidth: '100%'
      }), $(this.shadowRoot).find('a.customisable').css({
        color: '#56CCF2'
      }),
        $(this.shadowRoot).find('.EmbeddedTweet-tweetContainer').css({
          fontFamily: 'Montserrat, sans-serif',
          fontSize: '1.4rem'
        });
    });
  }

  function mediathequeMasonry() {
    if($('.view-mediatheque').length > 0) {
      $('.view-mediatheque .view-content').masonry({
        itemSelector: '.views-row',
        columnWidth: 293,
        gutter: 30
      });
    }
  }

  Drupal.behaviors.drawSvg = {
    attach: function (context, settings) {
      var $node = $(context).find('.page-node-type-parcours-de-vie');
      var $content = $(context).find('.node--view-mode-full'); // uniquement la zone de contenu -- exclu le header

      if (
        $node.length > 0 &&
        $content.length > 0
      ) {
        var $svgWrappers = $content.find(".wrapper-svg");
        var percentageSVG = 0;
        var percentageAudio = 0;

        if( $svgWrappers.length > 0) {
          $svgWrappers.once('myThemeDrawSvg').each(function(idx, svgWrapper) {
            var $current = $(svgWrapper);
            var $fieldItem = $current.parents(".field--item");
            var $svgWiredWrapper = $current.find(".field--name-field-pg-svg-wired");
            var $svg = null, play = false;

            if ($fieldItem.length > 0) {
              $svg = $svgWiredWrapper.find("svg");
              $svg.drawsvg();

              $(window).scroll(function () {
                percentageSVG = elementScrollPercentage(idx, $svgWiredWrapper);
                percentageSVG = percentageSVG >= 1 ? 1 : percentageSVG;
                percentageSVG = percentageSVG < 0 ? 0 : percentageSVG;

                $svg.drawsvg('progress', percentageSVG);

                percentageAudio = elementScrollPercentage(idx, $fieldItem);
                percentageAudio = percentageAudio >= 1 ? 1 : percentageAudio;
                percentageAudio = percentageAudio < 0 ? 0 : percentageAudio;

                if (percentageAudio > 0 && percentageAudio < 1) {
                  playSoundCloudIframe($fieldItem, true);
                } else {
                  playSoundCloudIframe($fieldItem, false);
                }
              });
            }
          });
        }

      }
    }
  };

  var elementScrollPercentage = function(idx, element) {
    var $header = $("header#navbar");
    var $menu = $(".main-container").find("div[role='heading']");
    var delta = 450;
    var scrollTop = $(window).scrollTop();
    var pos = element.offset();
    var height = element.outerHeight();
    var percentage;

    ($header.length > 0) ? delta = delta + $header.outerHeight() : delta;
    ($menu.length > 0) ? delta = delta + $menu.outerHeight() : delta;
    scrollTop = scrollTop + delta;

    percentage = Math.floor(((scrollTop - pos.top) / (height / 1.5)) * 100);

    return (percentage / 100);
  };

  var playSoundCloudIframe = function(iframeWrapper, play) {
    var $audioWrapper = iframeWrapper.find(".field--name-field-pg-audio");
    var $audioIframe = $audioWrapper.find("iframe");
    var scWidget = null;

    if ($audioIframe.length > 0) {
      scWidget = SC.Widget($audioIframe.get(0));
      if (play) {
        scWidget.play();
      } else {
        scWidget.pause();
        scWidget.seekTo(0);
      }
    }
  };

  Drupal.icdc_theme.keepfocusincontainer = function(container) {
    container.find('input, select, textarea, a, button, area, [tabindex="0"]').attr('data-focus','1');
    $('input, select, textarea, a, button, area, [tabindex="0"]').each(function() {
      if (!$(this).attr('data-focus')) {
        $(this).attr('tabindex','-1').attr('data-focus','0');
      }
    });
  };

  Drupal.icdc_theme.releasefocus = function () {
    $('[data-focus="0"], [data-focus="1"]').each(function() {
      $(this).attr('tabindex','0').removeAttr('data-focus');
    });
  };

  function a11ysearchmodal() {
    $('#icdcSearchBlock').on('shown.bs.modal', function (e) {
      Drupal.icdc_theme.keepfocusincontainer($('#icdcSearchBlock'));
      $('#icdcSearchBlock .close').focus();
      $('button[data-target="#icdcSearchBlock"]').attr('aria-expanded','true');
    });
    $('#icdcSearchBlock').on('hide.bs.modal', function (e) {
      Drupal.icdc_theme.releasefocus();
      $('button[data-target="#icdcSearchBlock"]').focus().attr('aria-expanded','false');
    });
  }

  function mediation(){
    if ($('#webform-submission-mediation-add-form').length){
      $('input').removeAttr('size');
    }
  }
  function darkHeader() {
    if ($('#block-entityviewcontenu-bandeau').length) {
      if ($('#block-entityviewcontenu-bandeau').hasClass("couleur-inversee")){
        $("body").addClass("couleur-inversee");
      }
      if ($('#block-entityviewcontenu-bandeau').hasClass("entete-immersive")) {
        $("body").addClass("entete-immersive");
      }
    }
  }

  function addAttrToSvg() {
    $("svg").each(function (index) {
      $(this).attr("aria-hidden", "true");
    });
    $("svg.ext").each(function (index) {
      $(this).find("title").remove();
      $(this).removeAttr("aria-label");
    });
  }

  function addAttrToExtLinks() {
    var $node = $("a.ext");
    if ($node.length > 0) {
      $("a.ext").each(function (index) {
        $(this).attr("title", $($(this).get(0).outerHTML).children().remove().end().text() + " - Nouvelle fenêtre");
      });
    }
  }

  function addSpanToDefaultSelect() {

    var $list = $('ul[data-drupal-facet-id="icdc_media_facet_year"]');
    if ($list.length > 0) {
      var $firstElement = $list.find("li").first();
      var $firstElementTexte = $firstElement.text();
      $firstElement.text("").html($firstElementTexte + "<span class='visually-hidden'> tout sélectionner</span>")
    }
    var $select = $('select[data-drupal-facet-id="icdc_media_facet_year"]');
    if ($select.length > 0) {
      $('select[data-drupal-facet-id="icdc_media_facet_year"]').attr("aria-label", "Filtrer par année");
      $('select[data-drupal-facet-id="icdc_media_facet_year"]').attr("id", "icdc_media_facet_year");
      $('select[data-drupal-facet-id="icdc_media_facet_year"]').removeAttr("aria-labelledby");
      $("#facet_icdc_media_facet_year_label").attr("for", "icdc_media_facet_year");
      $("#facet_icdc_media_facet_year_label").removeAttr("id");
      var $firstElementSelect = $select.find("option").first();
      var $firstElementSelectTexte = $firstElementSelect.text();
      $firstElementSelect.text("").html($firstElementSelectTexte + "<span class='visually-hidden'> (tout sélectionner)</span>")
    }
  }

  function updateTitleBlog() {
    var current_pageTitle = $(document).find("title").text();
    if ($("div.menu--menu-blog > ul").length > 0) {
      var $node = $("div.menu--menu-blog > ul > li");
      $node.find("a.active-trail").each(function (index) {
        if ($(this).attr("id") != "all") {
          var new_title = $(this).text() + " - " + current_pageTitle;
          $(document).find("title").text(new_title);
        }
      });
    }
  }
  function updateTitleMediatheque() {
    var current_pageTitle = $(document).find("title").text();
    var title_array = current_pageTitle.split('|');
    var $select = $('ul[data-drupal-facet-id="icdc_media_facet_year"]');
    var $node = $("#block-icdcmediafacettype").find("ul > li.facet-item").slice(0);
    var filterdate = "";
    var filterType = "";
    var filterTypeArray = [];
    var filterdateArray = [];
    if ($select.length > 0) {
      var $selectedElement = $select.find("li a.is-active").slice(0);
      if ($selectedElement.length > 0) {
        $selectedElement.each(function (index) {
          $(this).attr("aria-label", $(this).text().trim() + " - Retirer le filtre actif");
          $(this).attr("title", $(this).text().trim() + " - Retirer le filtre actif");
          filterdateArray.push($(this).text().trim());
        });
        filterdate = " (" + filterdateArray.join(", ") + ") ";
      }
    }
    if ($("#block-icdcmediafacettype").length > 0) {
      $node.find("a.is-active").each(function (index) {
        $(this).attr("aria-label", "Retirer le filtre " + $(this).text().split('(-)')[1].trim());
        $(this).attr("title", $(this).text().split('(-)')[1].trim() + " - Retirer le filtre actif");
        filterTypeArray.push($(this).text().split('(-)')[1].trim());
      });
      filterType = " - " + filterTypeArray.join(", ");
    }
    if (filterdate.length > 0 || filterType.length > 0) {
      var new_title = title_array[0] + filterdate + filterType + " | " + title_array[1];
      $(document).find("title").text(new_title);
    }

  }

  function updateTitleSearch() {
    if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      $(".icdc-menu-mobile #views-exposed-form-recherche-page-1").remove();
    }
    var current_pageTitle = $(document).find("title").text();
    var title_array = current_pageTitle.split('|');
    var search_input = $("#edit-search-api-fulltext").val();
    var number_pagination_text = $("li.is-active").text();
    var number_pagination = number_pagination_text.replace(/[-+()\s]/g, '');
    var number_last_pagination = "";
    if ($("li.pager__item--last a").length > 0) {
      number_last_pagination = parseInt($("li.pager__item--last a").attr("href").match(/\d+$/)[0]) + 1;
    }
    else {
      var last_item = $("li.pager__item").last().text().replace(/[-+()\s]/g, '');
      if (last_item.length > 0){
        number_last_pagination = parseInt(last_item.match(/\d+$/)[0]);
      }
    }
    if (search_input.length > 0) {
      var new_title = title_array[0] + " : " + search_input + " (page " + number_pagination.match(/\d+$/)[0] + "/" + number_last_pagination + ")" + " | " + title_array[1];
      $(document).find("title").text(new_title);
    }
  }

  let timeout;
  var id = "";

  function removeAriaExpandedFromDiv() {
    $('.accordion button').on("click", function(){
      id = $(this).attr('aria-controls');
      timeout = setTimeout(removeFunc, 500);
    })
  }

  function removeFunc() {
    document.querySelector('#' + id).removeAttribute('aria-expanded');
    document.querySelector('#' + id).removeAttribute('style');
  }


  $(document).ready(function () {
    mediation();
    darkHeader();
    switchLangue();
    linkAllBlog();
    mediathequeMasonry();
    stratesWipeOnScroll();
    resizeHomeHeader();
    resizeHomeBlocks();
    resizeSvgHeaderParcours();
    resizeSvgStoryParcours();
    a11ysearchmodal();
    addAttrToSvg();
    addAttrToExtLinks();
    addSpanToDefaultSelect();
    updateTitleBlog();
    updateTitleMediatheque();
    updateTitleSearch();
    setTimeout(twitterWidth, 900);
    removeAriaExpandedFromDiv();
  });
})(jQuery, Drupal);
