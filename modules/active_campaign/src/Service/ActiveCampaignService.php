<?php

namespace Drupal\active_campaign\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ActiveCampaignService {

  const TAG_CONTACTO = 'mucavi_contacto';
  const TAG_COMPRA = 'mucavi_compra';

  /**
   * @var \GuzzleHttp\Client
   */
  private Client $client;

  /**
   * @var mixed|null
   */
  private  $endpoint;

  /**
   * @var mixed|null
   */
  private  $apikey;

  /**
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private LoggerChannel $logger;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  private $config;

  public function __construct(Client $client, ConfigFactory $config, LoggerChannel $loggerChannel) {
    $this->client = $client;
    $this->config = $config->get('active_campaign.config');
    $this->endpoint = $this->config->get('endpoint');
    $this->apikey = $this->config->get('apikey');
    $this->logger = $loggerChannel;
  }

  /**
   * Recibir datos.
   *
   * @param $uri
   * @return mixed|null
   */
  public function receive($uri)
  {
    $request = NULL;

    $url = $this->endpoint . $uri;
    try {
      $request = $this->client->request('GET', $url, ['headers'=> ['Accept'=>'application/json', 'Api-Token'=> $this->apikey]]);
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());
    }
    $result = null;
    if ($request) {
      $result = $request->getBody()->getContents();
      $result = Json::decode($result);
    }

    return $result;
  }

  /**
   * Eliminar datos.
   *
   * @param $uri
   *
   * @return mixed|null
   */
  public function delete($uri) {
    $request = NULL;

    $url = $this->endpoint . $uri;
    try {
      $request = $this->client->request('DELETE', $url, ['headers'=> ['Accept'=>'application/json', 'Api-Token'=> $this->apikey]]);
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());
    }
    $result = null;
    if ($request) {
      $result = $request->getBody()->getContents();
      $this->logger->info($uri . ' => ' . $result);
      $result = Json::decode($result);
    }

    return $result;
  }

  /**
   * Enviar datos.
   *
   * @param $uri
   * @param array $data
   *
   * @return mixed|null
   */
  public function send($uri, array $data) {
    $request = NULL;

    $url = $this->endpoint . $uri;
    try {
      $request = $this->client->request('POST', $url, ['json' => $data, 'headers'=> ['Api-Token'=>$this->apikey]]);
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());
    }
    $result = null;
    if ($request) {
      $result = $request->getBody()->getContents();
      $this->logger->info($uri . ' => ' . $result);
      $result = Json::decode($result);
    }

    return $result;
  }

  /**
   * Obtener todas las etiquetas.
   *
   * @return array|mixed
   */
  public function getTags() {
    $tags = $this->receive('tags');
    $etiquetas = $tags['tags'];
    if (isset($tags['meta']['total'])) {
      $page = 0;
      $count = count($tags['tags']);
      $total = (int) $tags['meta']['total'];

      while ($count < $total) {
        $page += 20;
        $tg = $this->receive('tags?offset=' . $page);
        $count += count($tg['tags']);
        $etiquetas = array_merge($etiquetas, $tg['tags']);
      }
    }
    return $etiquetas;
  }

  /**
   * Crear / actualizar contacto.
   *
   * @param string $email
   *  Email del contacto
   * @param string|null $firstName
   *  Nombre del contacto
   * @param string|null $lastName
   *  Apellidos del contacto
   * @param string|null $phone
   *  Teléfono del contacto.
   *
   * @return string|null
   */
  public function addContact(string $email, string $firstName = NULL, string $lastName = NULL, string $phone = NULL): ?string {
    $id = NULL;
    $data = ['contact' => ['email' => $email]];

    if ($firstName) {
      $data['contact']['firstName'] = $firstName;
    }

    if ($lastName) {
      $data['contact']['lastName'] = $lastName;
    }

    if ($phone) {
      $data['contact']['phone'] = $phone;
    }

    $response = $this->send('contact/sync', $data);

    if (isset($response['contact']['id'])) {
      $id = $response['contact']['id'];
    }

    return $id;
  }

  /**
   * Añadir etiqueta al contacto.
   *
   * @param string $contact_id
   * @param string $tag_id
   */
  public function addContactTag(string $contact_id, string $tag_id) {
    $data = [
      'contactTag' => [
        'contact' => $contact_id,
        'tag' => $tag_id,
      ]
    ];
    $this->send('contactTags', $data);
  }
}
