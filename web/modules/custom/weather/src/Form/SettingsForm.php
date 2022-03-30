<?php

namespace Drupal\weather\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Weather settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
   * Constructs a new WeatherSettings instance.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(Connection $connection, ClientInterface $client, ConfigFactoryInterface $config_factory) {
    $this->connection = $connection;
    $this->client = $client;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ConfigFormBase | SettingsForm | static {
    return new static(
      $container->get('database'),
      $container->get('http_client'),
      $container->get('config.factory'),
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
   * Get weather info.
   */
  public function getWeather() {
    $key_api_weather = $this->config('weather.settings')->get('key_weather_api');
    $url_weather = "https://api.openweathermap.org/data/2.5/weather?id=524901&lang=fr&appid=$key_api_weather";
    try {
      $response = $this->client->request('GET', $url_weather);
      $weather_data = json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (GuzzleException $e) {
    }
    return $weather_data;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->getWeather();
    $default_value_city = $this->config('weather.settings')->get('city_weather');
    if (!$default_value_city) {
      $default_value_city = 'Kyiv';
    }

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Please add the key for Weather API:'),
      '#default_value' => $this->config('weather.settings')->get('key_weather_api'),
      '#require' => TRUE,
    ];
    $form['city_weather'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add a location city'),
      '#default_value' => $default_value_city,
      '#require' => TRUE,
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
    $api_key_length = strlen($form_state->getValue('api_key'));
    $city_name_for_weather = $form_state->getValue('city_weather');
    $api_key = $this->config('weather.settings')->get('key_weather_api');
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$city_name_for_weather&appid=$api_key";
    try {
      $response = $this->client->request('GET', $url);
      if ($response->getStatusCode() != 200) {
        throw new \Exception('Failed to retrieve data.');
      }
    }
    catch (GuzzleException $e) {
      $form_state->setErrorByName('city_weather',
        $this->t('Error! City name is not correct!!!'));
    }
    $reg_ex = "#^[A-Za-z-]+$#";
    if ($api_key_length != 32) {
      $form_state->setErrorByName('api_key', $this->t('The value is not correct.'));
    }
    elseif (!preg_match($reg_ex, $city_name_for_weather) && !empty($city_name_for_weather)) {
      $form_state->setErrorByName('city_weather', $this->t('The name of city is not correct.'));
    }
    else {
      parent::validateForm($form, $form_state);
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
      $response = \Drupal::httpClient()->request('GET', $url_weather);
      $weather_data = $response->getBody()->getContents();
      $timestamp = \Drupal::time()->getRequestTime();
    }
    catch (GuzzleException $e) {
    }
    $values = [
      'data_weather' => $city_name,
      'main_data_weather' => $weather_data,
      'time' => $timestamp,
    ];
    $query = $this->connection->update('weather_table')
      ->fields($values);

    try {
      $query->condition('id', 1);
      $query->execute();
    }
    catch (\Exception $e) {
    }

    parent::submitForm($form, $form_state);
  }

}
