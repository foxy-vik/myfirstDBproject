<?php

namespace Drupal\weather\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\weather\WeatherDb;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Weather routes.
 */
class WeatherController extends ControllerBase {

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
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\weather\WeatherDb $weather_db
   *   Service for using weather_table.
   */
  public function __construct(TimeInterface $time, WeatherDb $weather_db) {
    $this->time = $time;
    $this->weatherDb = $weather_db;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time'),
      $container->get('weather.db'),
    );
  }

  /**
   * Builds the response.
   */
  public function build() {
    $response_db = $this->weatherDb->getWeatherData();
    $response_weather = json_decode($response_db, TRUE);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Weather of the city:'),
    ];
    $build['content'][] = [
      '#theme' => 'weather_controller_template',
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
