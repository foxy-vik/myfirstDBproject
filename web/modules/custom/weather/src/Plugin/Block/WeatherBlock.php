<?php

namespace Drupal\weather\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation;

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
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new WeatherBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $client, ConfigFactoryInterface $config_factory, MessengerInterface $messenger, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->client = $client;
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->time = $time;
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
      $container->get('messenger'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'city_weather' => ['standard', 'metric', 'imperial'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['city'] = [
      '#type' => 'select',
      '#title' => $this->t('Select here your city'),
      '#options' => $this->configuration['city_weather'],
      // @todo default_value.
    //   '#default_value' => ,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['city_weather'] = $form_state->getValue('city');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function build() {

    $clien_ip = \Drupal::request()->getClientIp();
    $responce_ip = $this->client->request('GET', 'http://ip-api.com/json/24.48.0.1');
    $response_ip_content = json_decode($responce_ip->getBody()->getContents());
    ksm($response_ip_content->city);

    $key_api_weather = $this->configFactory->get('weather.settings')->get('key_weather_api');
    $city_name_weather = 'Lutsk';
    $lutsk_weather = 'Lutsk';
    // @todo metric change mechanism.
    $url_weather = "https://api.openweathermap.org/data/2.5/weather?q=$city_name_weather&appid=$key_api_weather&units=metric";
    try {
      $response = $this->client->request('GET', $url_weather);
      $weather_data = json_decode($response->getBody()->getContents(), TRUE);
      $main_data_weather = $weather_data['weather'][0];
    }
    catch (GuzzleException $e) {
    }
    $build['content'] = [
      '#markup' => '<h4>' . $this->t('City name: ')
      . $city_name_weather . '</h4>',
    ];
    $build['content'][] = [
      '#theme' => 'weather_block_template',
      '#data_weather' => $weather_data,
      '#main_data_weather' => $main_data_weather,
      '#attached' => [
        'library' => [
          'weather/weather',
        ],
      ],
    ];
    return $build;
  }

}
