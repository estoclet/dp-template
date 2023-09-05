(function ($) {
  'use strict';

  var treeAction = function(idContainer, container, action) {
    var id = container.data('id'),
        parent = container.data('parent');
    var $rightList = container.find(".rightList");
    var $childElement = $("[data-parent='" + id + "']");
    var ariaControls = "itemClose" + id;

    if ($childElement.length > 0) {
      ariaControls = ariaControls + " leftList" + (id + 1);
      ariaControls = ariaControls + " itemOpen" + (id + 1);
    }

    $rightList.find(".rebuildTreeOpen").attr("aria-controls", ariaControls);
    $rightList.find(".rebuildTreeClose").attr("aria-controls", ariaControls);

    switch(action) {
      case 'open' :
        $('#' + idContainer + ' .decision-tree-body[data-parent=' + id + ']:not(:first) .leftList p, #' + idContainer + ' .decision-tree-body[data-id!=' + id + '][data-parent=' + parent + '], #' + idContainer + ' .decision-tree-body[data-id=' + id + '] .item-open').hide();
        $('#' + idContainer + ' .decision-tree-body[data-id=' + id + '] .leftList p, #' + idContainer + ' .decision-tree-body[data-parent=' + id + '], #' + idContainer + ' .decision-tree-body[data-id=' + id + '] .item-close').show();
        $rightList.find(".rebuildTreeOpen").attr("aria-expanded", false);
        $rightList.find(".rebuildTreeClose").attr("aria-expanded", true);
        break;
      case 'close' :
        var path = container.data('path');
        path = path.substring(1, path.length - 1).split('-');

        var parentsSelector = [];
        $.each(path, function(index, idParent){
          if(idParent != id) {
            parentsSelector.push('[data-id="' + idParent + '"]');
          }
        });
        parentsSelector = parentsSelector.join(', ');
        var levelSelector = '[data-parent="' + parent + '"]';

        $('#' + idContainer + ' .decision-tree-body:not(' + parentsSelector + ',' + levelSelector + '), #' + idContainer + ' .decision-tree-body:not(' + parentsSelector + ',' + levelSelector + ') .item-close').hide();
        $('#' + idContainer + ' .decision-tree-body:not(' + parentsSelector + ',' + levelSelector + ') .item-open').show();
        var parentsElements = $('#' + idContainer + ' .decision-tree-body').filter(parentsSelector),
            levelElements = $('#' + idContainer + ' .decision-tree-body').filter(levelSelector);

        $('.item-close', parentsElements).show();
        $('.item-close button').attr("aria-expanded", true);
        $('.item-open', parentsElements).hide();
        $('.item-open button').attr("aria-expanded", false);
        $('.item-close', levelElements).hide();
        $('.item-close button').attr("aria-expanded", false);
        $('.item-open', levelElements).show();
        $('.item-open button').attr("aria-expanded", true);

        $('#' + idContainer + ' .decision-tree-body' + levelSelector + ':not(:first) .leftList p').hide();
        parentsElements.show();
        levelElements.show();
        break;
    }
  };

  Drupal.behaviors.decisionTree = {
    attach: function(context, settings) {
      $.each(settings.decision_tree, function(idContainer, item) {
        if(item.need_close) {
          $('#collapse' + idContainer).collapse();
        }
        $('#' + idContainer).once('decisionTreeBehavior').each(function() {
          var fakeContainer = $('#' + idContainer + ' .decision-tree-body:first()');
          treeAction(idContainer, fakeContainer, 'close');
        });

        $('#' + idContainer + ' .rebuildTree').click(function() {
          var container = $(this).parents('.decision-tree-body');

          if($(this).hasClass('rebuildTreeOpen')) {
            treeAction(idContainer, container, 'open');
          }
          else if($(this).hasClass('rebuildTreeClose')) {
            treeAction(idContainer, container, 'close');
          }
        });
      });

      $('#btnIcdcDecisionTree', context).once('btnDecisionTreeBehaviour').each(function() {
        $(this).click(function(e) {
          if (!$('#collapseicdcDecisionTree').hasClass('in')) {
            $('#collapseicdcDecisionTree').collapse();
          }

          var page = $(this).attr('href'); // Page cible
          var speed = 750; // Durée de l'animation (en ms)
          $('html, body').animate( { scrollTop: $(page).offset().top }, speed ); // Go
        });
      });
    }
  };

  let timeout;
  var id = "";

  function removeAriaExpandedFromDiv() {
    $('#icdcDecisionTree button').on("click", function () {
      id = $(this).attr('aria-controls');
      console.log(id);
      timeout = setTimeout(removeFunc, 500);
    })
  }

  function removeFunc() {
    console.log('click');
    console.log(id + ' ' + $('#' + id).length);
    document.querySelector('#' + id).removeAttribute('aria-expanded');
    document.querySelector('#' + id).removeAttribute('style');
  }

  $(document).ready(function () {
    removeAriaExpandedFromDiv();
  });
}(jQuery));
