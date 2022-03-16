<?php

namespace Drupal\email;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\email\Entity\Mail;

class MailService {

  use StringTranslationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private LoggerChannel $logger;

  /**
   * @var \Drupal\Core\Utility\Token
   */
  private Token $token;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private EntityTypeManager $entityTypeManager;

  private \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig $config;


  /**
   * Constructs a new MailService object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Logger\LoggerChannel $loggerChannel
   * @param \Drupal\Core\Utility\Token $token
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   */
  public function __construct(LanguageManagerInterface $language_manager, MailManagerInterface $mail_manager, LoggerChannel $loggerChannel, Token $token, EntityTypeManager $entityTypeManager, ConfigFactory $configFactory) {
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
    $this->logger = $loggerChannel;
    $this->token = $token;
    $this->entityTypeManager = $entityTypeManager;
    $this->config = $configFactory->get('system.site');
  }

  /**
   * Enviar correo.
   *
   * @param string $key
   * @param string $to
   * @param array|null $entity_keys_ids
   *  Array [entity_key => id]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function send(string $key, string $to, array $entity_keys_ids = NULL): void {

    $mail = Mail::load($key);

    if ($mail instanceof Mail) {
      $langcode = $this->languageManager->getDefaultLanguage()->getId();

      $subject = $mail->getSubject();
      $body = $mail->getBody();

      $token_service = $this->token;

      if ($entity_keys_ids) {
        foreach ($entity_keys_ids as $entity_key => $id) {
          $entity = $this->entityTypeManager->getStorage($entity_key)->load($id);
          if ($entity) {
            $subject = $token_service->replace($subject, [
              $entity_key => $entity
            ]);
            $body = $token_service->replace($body, [
              $entity_key => $entity
            ]);
          }
        }
      }

      $params = [
        'from' => $this->config->get('mail'),
        'subject' => $subject,
        'body' => Markup::create($body),
      ];

      $this->mailManager->mail('email', $key, $to, $langcode, $params);
      $this->logger->info('Correo de ' . $mail->get('name')->value . ' enviado a ' . $to);
    }

  }

}
