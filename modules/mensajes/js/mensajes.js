(function ($, Drupal, drupalSettings) {

  $.fn.ShowMensaje = function (config) {
    var id = this.attr('id');
    var text = this.html();
    var type, theme, time, sound;
    switch (id) {
      case 'mensaje-status':
        type = 'success';
        theme = config.status.tema;
        time = config.status.tiempo;
        sound = config.status.urlSonido;
        break;
      case 'mensaje-error':
        type = 'error';
        theme = config.error.tema;
        time = config.error.tiempo;
        sound = config.error.urlSonido;
        break;
      case 'mensaje-warning':
        type = 'warning';
        theme = config.warning.tema;
        time = config.warning.tiempo;
        sound = config.warning.urlSonido;
        break;
      case 'mensaje-info':
        type = 'info';
        theme = config.info.tema;
        time = config.info.tiempo;
        sound = config.info.urlSonido;
        break;
    }
    Mensaje(type, theme, text, time, sound);
  };

  function Mensaje(type, theme, text, time, sound) {
    var config = {
      type: type,
      theme: theme,
      text: text,
      animation: {
        open: function (promise) {
          var n = this;
          new Bounce()
            .translate({
              from: {
                x: 450,
                y: 0
              },
              to: {
                x: 0,
                y: 0
              },
              easing: "bounce",
              duration: 1000,
              bounces: 4,
              stiffness: 3
            })
            .scale({
              from: {
                x: 1.2,
                y: 1
              },
              to: {
                x: 1,
                y: 1
              },
              easing: "bounce",
              duration: 1000,
              delay: 100,
              bounces: 4,
              stiffness: 1
            })
            .scale({
              from: {
                x: 1,
                y: 1.2
              },
              to: {
                x: 1,
                y: 1
              },
              easing: "bounce",
              duration: 1000,
              delay: 100,
              bounces: 6,
              stiffness: 1
            })
            .applyTo(n.barDom, {
              onComplete: function () {
                promise(function (resolve) {
                  resolve();
                });
              }
            });
          PlaySound(sound);
        },
        close: function (promise) {
          var n = this;
          new Bounce()
            .translate({
              from: {
                x: 0,
                y: 0
              },
              to: {
                x: 450,
                y: 0
              },
              easing: "bounce",
              duration: 500,
              bounces: 4,
              stiffness: 1
            })
            .applyTo(n.barDom, {
              onComplete: function () {
                promise(function (resolve) {
                  resolve();
                });
              }
            });
        }
      }
    };

    if (time != null) {
      config.timeout = parseInt(time);
    }

    new Noty(config).show();
  }

  function PlaySound(sound) {
    var audio = new Audio(sound);
    audio.play();
  }

  Drupal.behaviors.mensajes = {
    attach: function (context, settings) {
      var config = drupalSettings.mensajes;
      $(document).ready(function () {
        if ($('#mensajes-wrapper').length > 0) {
          $('#mensajes-wrapper .mensaje-content').each(function () {
            $(this).ShowMensaje(config);
          });
          $('#mensajes-wrapper').remove();
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
