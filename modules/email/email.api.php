<?php


/**
 * Alterar tokens disponibles en formulario de correos.
 *
 * @param array $tokens
 *  Array de tokens.
 * @param string $key
 *  Clave del correo.
 *
 * @return void
 */
function hook_email_form_tokens_alter(array &$tokens, string $key) {
  $tokens[] = '[site:name] => Nombre del sitio';
}
