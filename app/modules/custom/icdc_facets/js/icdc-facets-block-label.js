/**
 * @file
 * ICDC Facets block label
 */


(function ($, Drupal) {
  'use strict';

  /**
   * Keep the original beforeSend method to use it later.
   */
  var success = Drupal.Ajax.prototype.success;

  /**
   * Handler for the form redirection completion.
   *
   * @param {Array.<Drupal.AjaxCommands~commandDefinition>} response
   *   Drupal Ajax response.
   * @param {number} status
   *   XMLHttpRequest status.
   */
  Drupal.Ajax.prototype.success = function (response, status) {

    // Call the original Drupal method with the right context.
    success.apply(this, arguments);
  }
})(jQuery, Drupal);
