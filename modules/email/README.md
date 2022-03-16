# EMAIL

Este módulo implementa la administración de los correos de la página, por defecto se sobreescriben los correos del sistema como el de registro y recuperar
clave.

* Se pueden crear todos los correos deseados asignándole una clave diferente a cada uno.
* Se implementa un servicio para el envío de cualquier correo creado.
* Se implementa un gancho para poder cambiar los tokens disponibles en el formulario de administración del correo.

### Servicio para el envío de correos

> \Drupal::service('email.mail')->send('register', 'example@example.com', ['user' => $user])

Parámetros:
1. La clave del correo.
2. Correo del destinatario
3. Array de objetos, tipo => objeto, en los cuales se reemplazarán los tokens encontrados.

### Gancho para modificar o agregar los tokens disponibles

* NOTA: Estos tokens son solo a modo informativo para la administración, cada objeto que se pase se reemplazaran todos los token que tenga, no solo los que se muestran en el formulario.

> function mymodule_email_form_tokens_alter(&$tokens, $key) { if ($key == 'register') { $tokens[] = '[user:field_nombre] => Nombre del usuario'; }  }
