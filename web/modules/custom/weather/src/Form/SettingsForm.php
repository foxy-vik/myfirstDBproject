<?php

namespace Drupal\weather\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\weather\WeatherDb;

/**
 * Configure Weather settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Our database repository service.
   *
   * @var \Drupal\weather\WeatherDb
   */
  protected WeatherDb $weatherDb;

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
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new WeatherSettings instance.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\weather\WeatherDb $weather_db
   *   Service for Weather db.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(ClientInterface $client,
                              ConfigFactoryInterface $config_factory,
                              WeatherDb $weather_db,
                              TimeInterface $time) {
    $this->client = $client;
    $this->configFactory = $config_factory;
    $this->weatherDb = $weather_db;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ConfigFormBase | SettingsForm | static {
    return new static(
      $container->get('http_client'),
      $container->get('config.factory'),
      $container->get('weather.db'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'weather_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['weather.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $default_value_city = $this->config('weather.settings')->get('city_weather');
    if (!$default_value_city) {
      $default_value_city = 'Kyiv';
    }

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Please add the key for Weather API:'),
      '#default_value' => $this->config('weather.settings')->get('key_weather_api'),
      '#required' => TRUE,
    ];
    $form['city_weather'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add a default location city'),
      '#default_value' => $default_value_city,
      '#required' => TRUE,
    ];
    $form['metric'] = [
      '#type' => 'select',
      '#title' => $this->t('Select here your metric system'),
      '#options' => [
        'standard' => $this->t('standard'),
        'metric' => $this->t('metric'),
        'imperial' => $this->t('imperial'),
      ],
      '#default_value' => $this->config('weather.settings')->get('units'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $api_key = $form_state->getValue('api_key');
    $api_key_length = strlen($api_key);
    $city_name_for_weather = $form_state->getValue('city_weather');
    if ($api_key_length != 32) {
      $form_state->setErrorByName('api_key', $this->t('The value is not correct.'));
    }
    if (!empty($city_name_for_weather)) {
      $validation = $this->weatherDb->validateWeatherData($city_name_for_weather, $api_key);
      if (!$validation) {
        $form_state->setErrorByName('city_weather', $this->t('Error! City name is not correct!!!.'));
        return FALSE;
      }
      else {
        parent::validateForm($form, $form_state);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $key_api_weather = $form_state->getValue('api_key');
    $city_name = $form_state->getValue('city_weather');
    $metric = $form_state->getValue('metric');
    $this->config('weather.settings')
      ->set('key_weather_api', $key_api_weather)
      ->set('city_weather', $city_name)
      ->set('units', $metric)
      ->save();

    $url_weather = "https://api.openweathermap.org/data/2.5/weather?q=$city_name&appid=$key_api_weather&units=$metric";
    try {
      $response = $this->client->request('GET', $url_weather);
      $weather_data = $response->getBody()->getContents();
      $timestamp = $this->time->getRequestTime();
      $values = [
        'data_weather' => $city_name,
        'main_data_weather' => $weather_data,
        'time' => $timestamp,
      ];
    }
    catch (GuzzleException $e) {
      $this->messenger()->addMessage($this->t('Update failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]
      ), 'error');
    }
    $this->weatherDb->updateWeatherData($values);

    parent::submitForm($form, $form_state);
  }

}
