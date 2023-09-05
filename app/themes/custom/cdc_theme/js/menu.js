(function ($, Drupal) {
  'use strict';

  var dimensions = Drupal.icdc_theme.getViewPort();
  var buttonCloseMenu;

  Drupal.behaviors.cdc_theme_menu = {
    attach: function (context, settings) {
      $("ul.menu li a.tarte-au-citron-open-modal").once('cdc-theme-tarteaucitron-modale').click(function(e) {
        e.preventDefault();
        if(tarteaucitron) {
          tarteaucitron.userInterface.openPanel();
        }
        return false;
      });
      $("#block-cdc-theme-main-menu .navbar-main > li > span" ).once('cdc-theme-navbar').click(function() {
        var $blocMenu = $("nav#block-cdc-theme-main-menu");
        var $menuModalTitle = $blocMenu.find("#menu-level--1-titre");
        //var $menuWidth = dimensions.width + 'px';
        buttonCloseMenu = $(this).next('.menu-level--1').find('.a42-ac-close');
        $(this).next('.menu-level--1').addClass('show-level').animate({width:"100%"},500, function(){
          buttonCloseMenu.focus();
        });
        $('body').addClass('body-menu-active');
        $(this).next('.menu-level--1').find('li.active div.menu-level--2').addClass('show-level').delay(500).animate({width:'60%'},500).prev().attr('aria-expanded','true');
        //$(this).next('.menu-level--1').find('li.active .menu-level--2').addClass('show-level').delay(500).prev().attr('aria-expanded','true');

        Drupal.icdc_theme.keepfocusincontainer($(this).next('.menu-level--1'));

        if ($menuModalTitle.length > 0) {
          $menuModalTitle.text($(this).text());
        }

        setTimeout(function() {
          var $listWrapper = $("button.a42-ac-close").siblings("ul");
          var $listOptions = $listWrapper.find("li.expanded.dropdown");
          var $firstItem = $listWrapper.find("li.expanded.dropdown.first").first();

          $listOptions.each(function(idx, elem) {
            $(elem).removeClass('active');
          });

          $firstItem.find("button.navbar-text").first().trigger('click');
        }, 500);
      });

      $("#block-cdc-theme-main-menu .menu-level--1 > li > button" ).once('cdc-theme-mainmenu').click(function() {
        $('#block-cdc-theme-main-menu .navbar-main div.menu-level--2').removeClass('show-level').css('width','0').prev().attr('aria-expanded','false');
        $('#block-cdc-theme-main-menu ul.menu-level--1 > li.active').removeClass('active');
        $('#block-cdc-theme-main-menu ul.menu-level--1 > li > button').removeAttr('title');
        //$(this).next('.menu-level--2').addClass('show-level').animate({width:"60%"},500).prev().attr('aria-expanded','true');
        $(this).next('.menu-level--2').addClass('show-level').css({width:"60%"}).prev().attr('aria-expanded','true');
        $(this).parent('li').addClass('active');
        $(this).attr('title', $(this).text() + ' - Elément actif');

        var $secondMenu = $(this).siblings('div.menu-level--2');

        if ($(this).parent('li').hasClass('first')) {
          $secondMenu.find('.caret-menu-level2').css('top', 'calc(36% - 12px');
        } else if ($(this).parent('li').hasClass('last')) {
          $secondMenu.find('.caret-menu-level2').css('top', 'calc(64% - 12px');
        }
      });

      $("button.a42-ac-close").once('cdc-theme-close-btn').click(function() {
        $("body").css('overflow-y', 'unset');
        $('body').removeClass('body-menu-active').css('left',"0");
        $('#block-cdc-theme-main-menu .navbar-main .menu-level--1,#block-cdc-theme-main-menu .navbar-main .menu-level--2').removeAttr('style').removeClass('show-level');
        Drupal.icdc_theme.releasefocus();
        $('#block-cdc-theme-main-menu button.navbar-text[aria-expanded="true"]').attr('aria-expanded','false');
        $(this).parent().prev().focus();
        $('#block-cdc-theme-main-menu ul.menu-level--1 > li.active').removeClass('active');
        $('#block-cdc-theme-main-menu ul.menu-level--1 > li.active-trail').addClass('active');
      });

      /* Menu mobile*/
      // $(".main-menu-mobile").attr('aria-hidden', 'true');
      $(".burger-menu").on('click', function () {
        console.log(".burger-menu click");
        // $(".main-menu-mobile").removeAttr('aria-hidden').attr('aria-expanded','true');
        // $(".main-menu-mobile").addClass('open');
        // $(this).attr('aria-expanded', 'true');

        var $sectionMenu = $('#block-icdcmenumobile');
        var $menuPanel = $sectionMenu.find('.icdc-menu-mobile.main-menu-mobile');
        var $firstLevel = $menuPanel.find('.a42-ac-close').siblings("ul").first();

        $firstLevel.find("#menu-level--1-titre").remove();
        $firstLevel.find("li.expanded.dropdown").on('click', function () {
          console.log('click sous-menu');
          addPanelContent(this);
          openPanel();
          closePanel();
        });
      });

      $(".main-menu-mobile").find(".close").on('click', function () {
        console.log(".main-menu-mobile .close click");
        $(".burger-menu").once('cdc-theme-menu-mobile').trigger('click');
        // $(".main-menu-mobile").attr('aria-hidden', 'true').attr('aria-expanded','false');
        //$(this).closest(".main-menu-mobile").removeClass('open');
        // $(".burger-menu").attr('aria-expanded','false');
      });


      /* Menu mobile Mini site*/
      $(".menu-minisite").attr('aria-hidden', 'true');
      $(".burger-menu").on('click', function () {
        $("body").css('overflow-y', 'hidden');
        $(".menu-minisite").removeAttr('aria-hidden');
        $(".menu-minisite").addClass('open');
        $(this).attr('aria-expanded', 'true');
      });

      $(".menu-minisite").find(".close").on('click', function () {
        $("body").css('overflow-y', 'unset');
        $(".menu-minisite").attr('aria-hidden', 'true');
        $(this).closest(".menu-minisite").removeClass('open');
        $(".burger-menu").attr('aria-expanded', 'false');
      });
    }
  };

  var openPanel = function () {
    var $panel = $("#menu-level3-panel");
    $panel.addClass("icdc-menu-mobile main-menu-mobile visible-xs open");
    $panel.find('li.first a').trigger("focus");
    console.log('trigger focus');
  };

  var addPanelContent = function (panelContent) {
    var $content = $(panelContent).find("div.menu-level--2").clone();
    var $panel = $("#menu-level3-panel").find("#menu-panel");
    var $level2;

    if ($panel.find(".menu-level--2").length > 0) {
      $panel.find(".menu-level--2").remove();
    }

    $panel.append($content);
    $level2 = $panel.find("div.menu-level--2");

    $level2.find("ul").each(function (idx, elem) {
      $(elem).show();
    });
  };

  var closePanel = function () {
    var $panel = $("#menu-level3-panel");

    $panel.find(".return-panel").on('click', function () {
      $panel.removeClass("open");
      setTimeout(function () {
        $panel.removeClass("icdc-menu-mobile main-menu-mobile visible-xs");
      }, 500);
    });

    $panel.find(".close").on('click', function () {
      $panel.prev().removeClass("open");
      $panel.removeClass("icdc-menu-mobile main-menu-mobile visible-xs open");
    })
  };

  $("*[aria-label='']").removeAttr('aria-label');
  $("#block-selecteurdelanguecontenu").removeAttr('role');
  $('svg.ext').removeAttr('aria-label');
  $('svg.ext').attr('aria-hidden','true');

})(jQuery, Drupal);
