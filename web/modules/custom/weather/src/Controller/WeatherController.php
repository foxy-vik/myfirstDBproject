<?php

namespace Drupal\weather\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\weather\WeatherDb;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Weather routes.
 */
class WeatherController extends ControllerBase {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Our database repository service.
   *
   * @var \Drupal\weather\WeatherDb
   */
  protected WeatherDb $weatherDb;

  /**
   * The controller constructor.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\weather\WeatherDb $weather_db
   *   Service for using weather_table.
   */
  public function __construct(ClientInterface $client, TimeInterface $time, WeatherDb $weather_db) {
    $this->client = $client;
    $this->time = $time;
    $this->weatherDb = $weather_db;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('datetime.time'),
      $container->get('weather.db'),
    );
  }

  /**
   * Builds the response.
   */
  public function build() {
    $fields = ['data_weather', 'main_data_weather', 'time'];
    $response_db = $this->weatherDb->getWeatherData($fields);
    $response_weather = json_decode($response_db->main_data_weather, TRUE);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Weather of the city:'),
    ];
    $build['content'][] = [
      '#theme' => 'weather_block_template',
      '#data_weather' => $response_weather,
      '#main_data_weather' => $response_weather['weather'][0],
      '#attached' => [
        'library' => [
          'weather/weather',
        ],
      ],
    ];

    return $build;
  }

}
