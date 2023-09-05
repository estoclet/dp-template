(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.mySearchMenu = {
    attach: function (context, settings) {
      $('.button-menu--search', context).once('icdc-search').click(function (e) {
        $('.header-navigation-secondary').addClass('navbar-fixed-top');
        $('.button-menu--search').addClass('modal--full-page-btn-close');
        $('.page-node-type-accueil').addClass('modal-layer-search-open');
      });

      $('.modal--full-page').on('hide.bs.modal', function (event) {
        $('.header-navigation-secondary').removeClass('navbar-fixed-top');
        $('.page-node-type-accueil').removeClass('modal-layer-search-open');
      });

      //Bouton submit avec loupe
      $('button[id^="edit-submit-recherche"]').html('<i class="cdcicon cdcicon-loupe" aria-hidden="true"></i><span class="sr-only">Rechercher</span>');

      $('button[id^="edit-submit-recherche"]').removeAttr('value');
      $('button[id^="edit-submit-recherche"]').removeAttr('name');

      $('#edit-search-api-fulltext, #edit-search-api-fulltext--2').removeAttr('size');
      $("#views-exposed-form-recherche-page-1").attr('role','search');
    }
  };
})(jQuery, Drupal);
