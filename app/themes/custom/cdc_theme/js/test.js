/**
 * @file
 * A JavaScript file for the theme.
 *
 * In order for this JavaScript to be loaded on pages, see the instructions in
 * the README.txt next to this file.
 */
// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - http://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth
(function ($, Drupal, window, document, undefined) {

  /* ---------- deviceInfo Setup ---------- */
  deviceInfo = {
    'size': '',
    'width': '',
    'height': '',
    'menuWidth' : '',
    'touchEvents': function() {
      if (typeof Modernizr.touch !== 'undefined') {
        return Modernizr.touch;
      }
      else {
        console.log("deviceInfo cannot detect touchevents with Modernizr.touch.");
        return null;
      }
    }(),
    'orientation': null,
    'breakPoints': {
      'xsmallBp': 450,
      'smallBp': 640,
      'mediumBp': 800,
      'largeBp': 1300,
      'xlargeBp': 1400,
    },

    "calculate": function() {
      $window = $(window);
      this.width = $window.width();
      this.height = $window.height();
      this.menuWidth = $('#nice-menu-1').outerWidth(true);

      //Set screenSize based on breakpoints
      if (this.width > this.breakPoints.xlargeBp) {
        this.size = "x-large";
      }
      else if (this.width > this.breakPoints.mediumBp && this.width < this.breakPoints.xlargeBp ) {
        this.size = "large";
      }
      else if (this.width > this.breakPoints.smallBp && this.width < this.breakPoints.mediumBp) {
        this.size = "medium";
      }
      else if (this.width > this.breakPoints.xsmallBp && this.width < this.breakPoints.smallBp) {
        this.size = "small";
      }
      else {
        this.size = "x-small";
      }
      //Set screen orientation
      if (this.width > this.height) {
        this.orientation = 'landscape';
      }
      else {
        this.orientation = 'portrait';
      }
      //console.log(deviceInfo);
    }, //Close calculate

    "setBreakPoints": function(newBps) {
      if (typeof newBps !== "object") {
        console.error("deviceInfo.setBreakPoints expects an object with keys: 'x-small'/'small'/'medium'/'large'/'x-large'.\n Example: deviceInfo.setBreakPoints({'large': 1000})");
        return;
      }
      if (newBps['x-small']) {
        this.breakPoints.mediumBp = newBps['x-small'];
      }
      if (newBps['small']) {
        this.breakPoints.mediumBp = newBps['small'];
      }
      if (newBps['medium']) {
        this.breakPoints.mediumBp = newBps['medium'];
      }
      if (newBps['large']) {
        this.breakPoints.largeBp = newBps['large'];
      }
      if (newBps['x-large']) {
        this.breakPoints.xlargeBp = newBps['x-large'];
      }
      //Calculate again because the device may change after changing breakPoints.
      this.calculate();
    } //Close setBreakPoints
  }; //Close deviceInfo
  /* ---------- End deviceInfo setup ---------- */


  /* ---------- Behaviors: Code to run on pageload AND after ajax updates ---------- */
  /**
   * Responsive Tables and other responsive stuff
   */
  Drupal.behaviors.responsive = {
    attach: function (context, settings) {
      deviceInfo.calculate();
      $('.view-scholar-admin table.views-table').addClass(function() {
        return 'responsive-table-with-label';
      });
      checkWindowSize();
    }
  };
  /**
   * EasyDropDowns
   * I had to hack easyDropDowns for this to work.
   * See: https://github.com/patrickkunka/easydropdown/pull/53/files
   */
  Drupal.behaviors.dropdowns = {
    attach: function (context, settings) {
      $('select').each(function() {
        $(this).easyDropDown({
          wrapperClass: 'pretty-select-lists',
          cutOff: 10,
        }); //end easyDropDown
      }); //end 'select'.each
    } //end attach
  }; //end behavior
  /* ---------- end all behaviors ---------- */

  function changeMenuWidth(menuWidth) {
    //$('#nice-menu-1').css({'width': menuWidth +'px'});
    var menuItems = $('#nice-menu-1 li').not('#nice-menu-1 li ul li').size();
    var totalWidth = 0;
    var currentWidth = 0;
    var pxWidth = [];
    var paddingItem = 0;
    var ItemWidth = 0;

    for (var i=0; i<=menuItems; i++) {
      currentWidth = $('#nice-menu-1 li.menu__item:nth-child('+ i +') a').not('#nice-menu-1 li ul li.menu__item:nth-child('+ i +') a').width();
      pxWidth.push(currentWidth);
      totalWidth += currentWidth;
    }
    paddingItem = (menuWidth-totalWidth)/menuItems - .1;

    for (var j=0; j<=menuItems; j++) {
      ItemWidth = pxWidth[j] + paddingItem;
      $('#nice-menu-1 li.menu__item:nth-child('+ j +')').not('#nice-menu-1 li ul li.menu__item:nth-child('+ j +')').css('width', ItemWidth+"px");
    }
  }; //end changeMenuWidth

  /**
   * Callback function invoked when the window is resized.
   */
  function checkWindowSize() {
    // @todo: Not sure why this should be in a resize callback. This value won't change
    // when the window is resized, after all.
    $('table').addClass(function() {
      return 'col-' + $('tbody tr', this)[0].cells.length;
    });

    if (deviceInfo.size == "small" || deviceInfo.size == "x-small" || deviceInfo.size == "medium") {
      // Removed Mobile Menu
      $( '#block-nice-menus-1 div.contextual-links-wrapper').remove();

      // Table responsive with Header floating left
      $('table.responsive-table-with-header').each(function() {
        var theadWidth = parseInt($(this).find('thead').outerWidth(true));
        var tableHeight = parseInt($(this).find('thead').outerHeight(true));
        var tableWidth = $(this).parent().width();
        var tbodyWidth = tableWidth - theadWidth;
        var rowCount = parseInt($('tbody tr:last-child', this).index() + 1);
        var colCount = parseInt($("tbody tr:last-child td:last-child", this).index() + 1);
        var colThCount = parseInt($("thead tr:last-child th:last-child", this).index() + 1);

        $(this).css('height', tableHeight + "px");
        $(this).css('width', tableWidth + "px");
        $('tbody', this).css('width', tbodyWidth+ "px");
        $('tbody', this).css('left', theadWidth + "px"); // adding thead border-left
        /* Fix for ie */
        $('thead', this).css('width', theadWidth+ "px"); // adding border-left

        for(var j=0; j<=rowCount; j++) {
          for(var k=0; k<=colCount; k++) {
            $($('tbody tr:nth-child('+j+') td:nth-child('+k+')', this), $('thead tr:last-child th:nth-child(' + k + ')', this)).equalize('outerHeight');
          }
        }
      }); // End table.responsive-table-with-header each()

      //Table with Header as Label
      $('table.responsive-table-with-label').each(function() {
        // Fix duplication of label
        $('span.header-label').remove();

        var rowThCount = parseInt($("thead tr:last-child", this).index() + 1);
        var colThCount = parseInt($("thead tr:last-child th:last-child", this).index() + 1);
        var rowCount = parseInt($("tbody tr:last-child", this).index() + 1);
        var colCount = parseInt($("tbody tr:last-child td:last-child", this).index() + 1);
        // Avoid duplicate on resizing
        var colLabel=[];
        var headerLabel;

        for(i=0;i<=colThCount;i++)  {
          // head column label extraction
          colLabel.push($('thead tr:last-child > *:nth-child('+i+')', this).text());
        }
        for (j=0; j<= rowCount; j++) {
          for(k=0; k<=colCount; k++) {
            //add each td element of data-title attribute
            headerLabel = '<span class="header-label">' + colLabel[k] + ': </span>';
            $('tbody tr:nth-child('+ j +') td:nth-child('+ k +')', this).prepend(headerLabel);
          }
        }
      }); // End table.responsive-table-with-label each() */
    }
    else {
      // remove all header-label on table.responsive-table-with-label
      $('span.header-label').remove();
      // remove button for responsive menu
      $('.menu-back').remove();
    }

    var fitSize = $(".field-name-title h2").css('font-size');
    $(".field-name-field-nativelanguagename .field-item").css({ 'font-size': fitSize });

  } //end checkWindowSize

  //Resize Events
  var resizeTimer;
  $(window).on("resize", function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      deviceInfo.calculate();
      checkWindowSize();
      changeMenuWidth(deviceInfo.menuWidth);
    }, 100);
  });


  $(document).ready(function() {
    changeMenuWidth(deviceInfo.menuWidth);

    // Fix for Abobe PDF icons that were inserted into the body on the old version of the site.
    $('img[src*="pdf.gif"]').parent().removeClass('figure');

    //Duplicate Featured Scholar's Block for responsive homepage
    $("#block-views-scholar-admin-featured-scholars").removeAttr("id").addClass("block-views-scholar-admin-featured-scholars").clone().addClass('mobile').appendTo('.region-sidebar-second .wrapper');


    $(".field-name-title h2").fitText(1.2, { maxFontSize: '35px' });

    // Fix for Iphone bug on click - Use ".bind(event, function(e) {}" instead of .click
    var event = (navigator.userAgent.match(/iPhone/i)) ? "touchstart" : "click";

    /* Adding class to every table column */
    $('table').addClass(function() {
      return 'col-' + $('tbody tr', this)[0].cells.length;
    });

    $('a').filter(function() {
      return $(this).children().length == 1 && $(this).children().get(0).tagName.toUpperCase() == 'IMG';
    }).addClass('ilink');

    if (Drupal.settings.backgrounds) {
      var bgnum = Math.floor(Math.random()*Drupal.settings.backgrounds.length);
      $('body').css('background-image', 'url(' + Drupal.settings.backgrounds[bgnum] + ')');
    }

    var mobile_banners = 9;
    $('#page').css({'background-image': 'url(/sites/harvard-yenching.org/files/images/mobile-banner/banner-r0' + Math.floor((Math.random()*mobile_banners)+1) + '.jpg)'});

    $('.xpandable').each(function() {
      $(this)
        .bind(event, function(e) {
          $(this).toggleClass('open').next('.xpandable-area').slideToggle();
        })
        .nextUntil('.xpandable, .xpandable-break')
        .wrapAll('<div class="xpandable-area"></div>');
    });
    $('.xpandable-area').hide();

    // Mobile menu
    $("#block-nice-menus-1 .content ul#nice-menu-1").clone().removeAttr("id").removeAttr("class").addClass("dl-menu").appendTo("#block-search-form .content");
    $(".menu-block-10 ul.menu").clone().addClass("dl-utility-menu").appendTo('ul.dl-menu');

    /* Wrap clone menu */
    $("#block-search-form ul.dl-menu").wrap('<div id="dl-menu" class="dl-menuwrapper" />');
    $("#block-search-form ul.dl-menu ul").addClass('dl-submenu').removeAttr("style");
    $("#block-search-form ul.dl-menu ul li").css('width', '100%');
    $('#dl-menu').prepend('<button class="dl-trigger">Open Menu</button>');
    $( '#dl-menu' ).dlmenu({
      animationClasses : { classin : 'dl-animate-in-2', classout : 'dl-animate-out-2' }
    });
  });//Close domReady
})(jQuery, Drupal, this, this.document);