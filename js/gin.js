(function ($, Drupal) {
  Drupal.behaviors.corporate_gin = {
    attach: function (context, settings) {
      $(".gin--classic-toolbar ul.toolbar-menu.root li a").each(function () {
        if ($(this).text() === 'Vista general' || $(this).text().indexOf('Inicio') !== -1) {
          $(this).parent().remove();
        }
      })

      let vistaTabla = $('.view-content .gin-table-scroll-wrapper');

      let divViewsActions = $('.view form table th.select-all');

      if (vistaTabla.length && !divViewsActions.length) {
        vistaTabla.addClass('views-form')
      }
    }
  };
})(jQuery, Drupal);
