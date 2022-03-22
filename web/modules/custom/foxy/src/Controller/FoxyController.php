<?php

namespace Drupal\foxy\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\foxy\Form;

/**
 * Returns responses for foxy routes.
 */
class FoxyController extends ControllerBase {

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {

    $buildForm = \Drupal::formBuilder()
      ->getForm('Drupal\foxy\Form\CollectPhone');
    $city = 'London';
    $data = file_get_contents('https://api.openweathermap.org/data/2.5/weather?q=' . $city . '&limit=1&appid=0a66bec47e39d392b4606128466e0360');
    $cat_facts = json_decode($data, TRUE);
  //  ksm($cat_facts);
    $build['content'] = [
      '#theme' => 'build_form',
      '#form' => $buildForm,
    ];
    $build['title'] = [
      '#type' => 'item',
      '#markup' => $cat_facts['weather'][0]['main'],
    ];

    return $build;
  }

}
