<?php

namespace Drupal\active_campaign\Form;

use Drupal\active_campaign\Service\ActiveCampaignService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActiveCampaignConfigForm extends ConfigFormBase {


  /**
   * @var \Drupal\active_campaign\Service\ActiveCampaignService
   */
  private ActiveCampaignService $activeCampaign;

  public function __construct(ConfigFactoryInterface $config_factory, ActiveCampaignService $activeCampaignService) {
    parent::__construct($config_factory);
    $this->activeCampaign = $activeCampaignService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('active_campaign')
    );
  }


  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames(): array {
    return ['active_campaign.config'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId(): string {
    return 'active_campaign_config';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {

    $config = $this->config('active_campaign.config');

    $form['config'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $form['config']['endpoint'] = [
      '#type' => 'url',
      '#title' => 'EndPoint',
      '#required' => TRUE,
      '#default_value' => $config->get('endpoint')
    ];

    $form['config']['apikey'] = [
      '#type' => 'textfield',
      '#title' => 'ApiKey',
      '#required' => TRUE,
      '#default_value' => $config->get('apikey')
    ];

    if ($config->get('endpoint')) {
      $tags = $this->activeCampaign->getTags();
      if (!empty($tags)) {
        $options = [];
        foreach ($tags as $tag) {
          $options[$tag['id']] = $tag['tag'];
        }
        $form['config']['tags'] = [
          '#type' => 'checkboxes',
          '#title' => 'Etiquetas disponibles',
          '#description' => 'Marcar las etiquetas que estarán activas y disponibles en el desplegable del landing',
          '#options' => $options,
          '#default_value' => $config->get('tags')
        ];
      }

      $form['tag'] = [
        '#type' => 'details',
        '#title' => 'Generar nueva etiqueta',
        '#tree' => TRUE,
      ];

      $form['tag']['label'] = [
        '#type' => 'textfield',
        '#title' => 'Titulo'
      ];

      $form['tag']['description'] = [
        '#type' => 'textfield',
        '#title' => 'Descripción'
      ];

      $form['tag']['generate'] = [
        '#type' => 'submit',
        '#value' => 'Generar',
        '#submit' => ['::generarEtiqueta']
      ];

    }

    $form += parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    parent::submitForm($form, $form_state);
    $config = $this->config('active_campaign.config');
    $values = $form_state->getValues();
    foreach ($values['config'] as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
    $this->messenger()->addStatus('Guardar');
  }

  /**
   * Generar etiqueta en ActiveCampaign.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function generarEtiqueta(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();
    if (isset($values['tag']['label']) && $values['tag']['label'] != '') {
      $data = [
        'tag' => [
          'tag' => $values['tag']['label'],
          'tagType' => 'contact',
          'description' => $values['tag']['description'],
        ]
      ];

      $this->activeCampaign->send('tags', $data);
      $this->messenger()->addStatus('Se ha creado la etiqueta ' . $values['tag']['label'] . ' en la cuenta de ActiveCampaign');
    }
  }
}
