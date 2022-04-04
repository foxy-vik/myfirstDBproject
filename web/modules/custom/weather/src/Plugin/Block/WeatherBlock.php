<?php

namespace Drupal\weather\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\weather\WeatherDb;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a weather block block.
 *
 * @Block(
 *   id = "weather_block",
 *   admin_label = @Translation("Weather block"),
 *   category = @Translation("Custom")
 * )
 */
class WeatherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Our database repository service.
   *
   * @var \Drupal\weather\WeatherDb
   */
  protected WeatherDb $weatherDb;

  /**
   * Constructs a new WeatherBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\weather\WeatherDb $weather_db
   *   Service for using weather_table.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
                              ClientInterface $client,
                              ConfigFactoryInterface $config_factory,
                              WeatherDb $weather_db) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->client = $client;
    $this->configFactory = $config_factory;
    $this->weatherDb = $weather_db;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('config.factory'),
      $container->get('weather.db'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['city_weather'];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function build(): array {
    $weather_data_json = $this->weatherDb->getWeatherData();
    $response_weather = json_decode($weather_data_json, TRUE);
    $main_data_weather = $response_weather['weather'][0];

    // Hardcore added Lutsk.
    $key_api_weather = $this->configFactory->get('weather.settings')->get('key_weather_api');
    $lutsk_url_weather = "https://api.openweathermap.org/data/2.5/weather?q=Lutsk&appid=$key_api_weather&units=metric";
    try {
      $lutsk_response = $this->client->request('GET', $lutsk_url_weather)
        ->getBody()->getContents();
      $lutsk_data_weather = json_decode($lutsk_response, TRUE);
    }
    catch (GuzzleException $e) {
      $build['content'] = [
        '#markup' => $this->t('You have problems with connection'),
      ];
    }

    $build['content'][] = [
      '#theme' => 'weather_block_template',
      '#data_weather' => $response_weather,
      '#main_data_weather' => $main_data_weather,
      '#lutsk_weather' => $lutsk_data_weather,
      '#attached' => [
        'library' => [
          'weather/weather',
        ],
      ],
    ];
    return $build;
  }

}
