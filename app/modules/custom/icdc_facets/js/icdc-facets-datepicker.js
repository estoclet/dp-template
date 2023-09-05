/**
 * @file
 * ICDC Facets datepicker
 */
(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.icdc_facets_datepicker = {
    attach: function (context, settings) {
      var dateFormat = 'yy-mm-dd';
      function getDate( element ) {
        var date;
        try {
          date = $.datepicker.parseDate( dateFormat, element.value );
        } catch( error ) {
          date = null;
        }
        return date;
      }

      $('.icdc-facets-datepicker', context).once('icdc-facets-datepicker-processed').each(function () {
        let form = $(this).parents('form');
        $(this).datepicker({
          dateFormat: dateFormat,
          minDate: $(this).hasClass('icdc-facets-datepicker-to') && $('.icdc-facets-datepicker-from', form).length ? $('.icdc-facets-datepicker-from', form).val() : null,
          maxDate: $(this).hasClass('icdc-facets-datepicker-from') && $('.icdc-facets-datepicker-to', form).length ? $('.icdc-facets-datepicker-to', form).val() : null,
        });
        if($(this).hasClass('icdc-facets-datepicker-from') && $('.icdc-facets-datepicker-to', form).length) {
          $(this).on('change', function() {
            $('.icdc-facets-datepicker-to', form).datepicker('option', 'minDate', getDate(this));
          });
        }
        if($(this).hasClass('icdc-facets-datepicker-to') && $('.icdc-facets-datepicker-from', form).length) {
          $(this).on('change', function() {
            $('.icdc-facets-datepicker-from', form).datepicker('option', 'maxDate', getDate(this));
          });
        }

        $(this).on('click', function () {
          $('.block-facets').find('.icdc-facets-collapsible-container').hide();
        });

      });
    }
  };
})(jQuery, Drupal);
