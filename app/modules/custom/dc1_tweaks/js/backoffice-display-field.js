(function ($, Drupal) {

    'use strict';

    Drupal.behaviors.blockField = {
        attach: function (context, settings) {

            $(document).ready(function () {

                $('.field-block-custom').each(function( index ) {
                    var blockField = $('.field-block-custom')[index];
                    var idBlockField = blockField.id;
                    if (blockField != undefined) {
                        var Idcheckbox = $('#'+idBlockField).find('.form-checkbox')[1].id;
                        var fieldset = $('#'+idBlockField).find('fieldset');
                        if($('#'+Idcheckbox).is(":checked") == false) {
                            fieldset.addClass('visually-hidden');
                        }
                        $('#'+Idcheckbox).once('collapse-fieldBlock').click(function () {
                            if($(this).is(":checked")){
                                fieldset.removeClass('visually-hidden');
                            }else{
                                fieldset.addClass('visually-hidden');
                            }

                        });

                    }
                });

            });

        }
    };

})(jQuery, Drupal);