(function ($, Drupal) {
  Drupal.behaviors.checkout = {
    attach: function (context, settings) {
      $(window).on('load', function () {
        Stripe($('input.apikey').val()).redirectToCheckout({sessionId: $('input.sessionId').val()});
      });
    }
  };
})(jQuery, Drupal);
