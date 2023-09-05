/*
    Script de mise en conformité d'accessibilité WAI pour Tiny Slider 2
    https://github.com/ganlanyuan/tiny-slider

    Référenciel WAI
    https://www.w3.org/TR/wai-aria-practices-1.1/#carousel

    option.tns [object] [requis] Objet généré lors de la création du carrousel TNS.
    option.parent [selecteur] [requis] Selecteur de la balise encapsulant le carrousel.
    option.parentLabel [selecteur] Selecteur de la balise titre du carrousel
    option.parentLabelText [Text] Texte de titre du carrousel
    option.slideLabel [selecteur] Selecteur du titre de chaque slide
    option.slideLabelText [Text] Defaut : "$page sur $total" Titre de chaque slide Accepte $page et $total
    option.navTitleText [Text] Défaut : "Carousel Pagination"  Titre du bloc navigation
    option.navText [Text] Texte du bouton de navigation Accepte $page et $total

    Exemple de code :

        var slider = tns({
            ...
        });

        var sliderA11Y = tinySliderA11Y({
            tns: slider,
            parent: ".tns-carrousel",
            parentLabel: "h3",
            slideLabel: ".title"
        });

*/
var $ = jQuery;

var tinySliderA11Y = (function (options){

    if(!options.tns || !options.parent) {
        console.log('Erreur tinySliderA11Y : paramètres "tns" et "parent" obligatoires');
        return false;
    }


    //références utiles
    var focusableHTML = 'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, [contentEditable=true]';
    var keys = {
        left: 37,
        right: 39
      };

    //libellés par défauts
    if( !options.navTitleText) options.navTitleText = "Carousel Pagination";

    //objet Tiny Slider de référence
    var tns = options.tns,
    tnsInfo = options.tns.getInfo(),
    parent = (options.parentSelectorType === 'element') ? options.parent : document.querySelector(options.parent);
    if(!parent) {
        console.log('Erreur tinySliderA11Y : parent non trouvé');
        return false;
    }

    function setAriaLabel(target, selector, text){
        var label = false;
        if(selector && parent.querySelector(selector) !== null) {
            label = parent.querySelector(selector).textContent.trim();
        } else if(text) {
            label = text;
        }
        if (label)
        target.setAttribute('aria-label', label);

    }

    //Balise parente : attributs aria et rôle
    parent.setAttribute('aria-roledescription', 'carrousel');
    parent.setAttribute('role', 'group');
    if( options.parentLabel || options.parentLabelText) {
        setAriaLabel(parent, options.parentLabel, options.parentLabelText);
    }


    //Slides : attributs aria et rôle
    var slides = tnsInfo.container.querySelectorAll('.tns-item');
    var sliderIsDiapo = $(tnsInfo.container).hasClass("cdc-tns-diapo");
    var role = tnsInfo.navContainer ? 'tabpanel': 'group';

    $(slides).each(function (index, slide) {
      if (sliderIsDiapo) {
        var $firstChild = $(slide).find("div").first();

        slide.setAttribute('role', 'presentation');
        $firstChild.attr({
          id: slide.getAttribute('id'),
          role: 'tabpanel',
          tabindex: slide.getAttribute('tabindex'),
          'aria-labelledby': 'slide-' + slide.getAttribute('id')
        });

        slide.removeAttribute('id');
        slide.removeAttribute('tabindex');
        slide.removeAttribute('aria-hidden');
      } else {
        slide.setAttribute('aria-roledescription', 'slide');
        slide.setAttribute('role', role);
        var slideLabel;
        if(!options.slideLabel) {
          slideLabel = options.slideLabelText ? options.slideLabelText : "$page sur $total";
          slideLabel = slideLabel.replace('$page', index+1);
          slideLabel = slideLabel.replace('$total', tnsInfo.index);
        }
        setAriaLabel(slide, options.slideLabel, slideLabel);
      }
    });

    //Liste de navigation : attributs aria et rôle
    if(tnsInfo.navContainer) {
        tnsInfo.navContainer.addEventListener('keydown', changeNavOnFocus);
        tnsInfo.navContainer.setAttribute('role', 'tablist');
        if(options.navText) {
            tnsInfo.navContainer.setAttribute('aria-label', options.navTitleText);
        }
        var navlist = tnsInfo.navContainer.children;
        $(navlist).each(function (idx, navbtn) {
          navbtn.setAttribute('role', 'tab');
          navbtn.setAttribute('tabindex', '-1');
        });

        //le focus activé par les bouton "droite" et "gauche" sur les elements de navigation doit être circulaire
        function changeNavOnFocus(event) {
            tnsInfo.navContainer.querySelector('[tabindex="0"]').attr('tabindex', '-1');
            if( event.keyCode == keys.left && event.target.getAttribute('data-nav') == 0) {
                tnsInfo.navContainer.querySelector('[data-nav="'+(tnsInfo.pages-1)+'"]').focus().attr('tabindex', '0');
            }
            if( event.keyCode == keys.right && event.target.getAttribute('data-nav') == tnsInfo.pages-1) {
                tnsInfo.navContainer.querySelector('[data-nav="0"]').focus().attr('tabindex', '0');
            }
        };

        //aria selected passe à true pour l'élément en cours
        function fixNavAriaSelected() {
            $(navlist).each(function (index, navbtn) {
              var isSelected = navbtn.classList.contains("tns-nav-active") ? 'true' : 'false';
              var isFocusable = navbtn.classList.contains("tns-nav-active") ? '0' : '-1';
              if (!sliderIsDiapo) {
                navbtn.setAttribute('aria-selected', isSelected);
                navbtn.setAttribute('tabindex', isFocusable);
              }
              var slideItem = slides.item(navbtn.getAttribute('data-nav')+1);
              if(slideItem) {
                navbtn.setAttribute('aria-controls', slideItem.getAttribute('id'));
              }
              //TNS réécrit les labels des boutons de navigation à chaque transition,
              //il faut donc surcharger les labels à chaque transition
              if(options.navText) {
                var navbtnLabel = options.navText;
                navbtnLabel = navbtnLabel.replace('$page', index+1);
                navbtnLabel = navbtnLabel.replace('$total', tnsInfo.pages);
                navbtn.setAttribute('aria-label', navbtnLabel);
              }
            });
        }
        fixNavAriaSelected();
    }


    //suppression du tabindex posé par Tiny Slider sur le bloc de navigation
    tnsInfo.controlsContainer.removeAttribute('tabindex');
    //suppression du tabindex sur les boutons de navigation
    var controlsBtn = tnsInfo.controlsContainer.querySelectorAll('button');
    $(controlsBtn).each(function (idx, btn) {
      btn.removeAttribute('tabindex');
    });

    //le contenu des slides masqués ne doit pas recevoir le focus
    function fixSlideTabindex() {
      $(slides).each(function (idx, slide) {
        var slideHasFocus = slide.getAttribute('tabindex') == '-1' ? false : true;
        var focusables = slide.querySelectorAll('focusableHTML');

        $(focusables).each(function (idx, focusable) {
          if(slideHasFocus) {
            focusable.removeAttribute('tabindex');
          } else {
            focusable.setAttribute('tabindex','-1');
          }
        });
      });
    };
    if (!sliderIsDiapo) {
      fixSlideTabindex();
    }

    //dès qu'un élément du carrousel recoit un focus, la lecture automatique est suspendue
    parent.addEventListener('focusin', function(event){
        tns.pause();
        if (!sliderIsDiapo) {
          tnsInfo.container.setAttribute('aria-live', 'polite');
        }
    });

    //Tiny Slider génère une balise "tns-liveregion" avec les attributs "aria-live" et "aria-atomic".
    //malheureusement "tns-liveregion" n'encapsule pas les slide, ce qui est requis pour les deux attributs aria ci-dessus.
    var liveRegion = parent.querySelector('.tns-liveregion');
    var PlayPauseBtn = parent.querySelector('button[data-action]');
    //suppression des attributs aria mal placés
    if( liveRegion ) {
        liveRegion.removeAttribute('aria-live');
        liveRegion.removeAttribute('aria-atomic');
    }

    //la balise encapsulant les slides possède un attribut "aria-atomic" à false
    if (!sliderIsDiapo) {
      tnsInfo.container.setAttribute('aria-atomic', false);
    }

    //gestion de l'attribut "aria-live" dans la balise encapsulant les slides
    //"aria-live" prend la valeur "off" quand le carrousel tourne automatiquement, sinon "polite"
    function toggleAriaLive() {
        var action = PlayPauseBtn.getAttribute('data-action') == 'stop' ? 'off' : 'polite';
        if (!sliderIsDiapo) {
          tnsInfo.container.setAttribute('aria-live', action);
        }
    }

    if(PlayPauseBtn) {
        ///"aria-live" en "off" par défaut
        tnsInfo.container.setAttribute('aria-live', 'off');
        //ajustement de "aria-live" au clic et clavier
        PlayPauseBtn.addEventListener('click', toggleAriaLive);
        PlayPauseBtn.addEventListener('keypress', toggleAriaLive);
        //ajustement de "aria-live" au survol
        tnsInfo.container.addEventListener('mouseenter', function(){
          if (!sliderIsDiapo) {
            tnsInfo.container.setAttribute('aria-live', 'polite');
          }
        });
        tnsInfo.container.addEventListener('mouseleave', function(){
            if( PlayPauseBtn.getAttribute('data-action') == 'stop') {
                tnsInfo.container.setAttribute('aria-live', 'off');
            }
        });
    }


    //Ecouteurs spécifiques à Tiny Slider
    //Actions après chaque transition
    tns.events.on('transitionEnd', function(){
      if (!sliderIsDiapo) {
        fixSlideTabindex();
      }
        if(tnsInfo.navContainer) fixNavAriaSelected();
    });


});
