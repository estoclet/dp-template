(function ($) {
  'use strict';

  Drupal.behaviors.investorsModal = {
    attach: function(context, settings) {
      // Will be call only once.
      $('#icdcInvestorsModal', context).once('investorsModalBehaviour').each(function() {
        if (sessionStorage.getItem('icdcInvestorsModal') !== '1') {
          $('#icdcInvestorsModal').modal({
            backdrop: 'static',
            keyboard: true,
            show: true
          });
        }

        $('#icdcInvestorsModal').on('hide.bs.modal', function (e) {
          sessionStorage.setItem('icdcInvestorsModal', 1);
        })
      });

    }
  };

}(jQuery));
