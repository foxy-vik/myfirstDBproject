<?php

namespace Drupal\weather\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
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
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $config_factory) {
    $this->client = $client;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ConfigFormBase | SettingsForm | static {
    return new static(
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
    $default_cities = ['Lutsk', 'Kiev', 'Rivne'];
    $this->getWeather();
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Please add the key for Weather API:'),
      '#default_value' => $this->config('weather.settings')->get('key_weather_api'),
      '#require' => TRUE,
    ];
    $form['city_weather'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add a location city'),
      '#default_value' => $default_cities[0],
      '#require' => TRUE,
      '#ajax' => [
        'callback' => '::cityWeatherAjaxCallback',
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'edit-output',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying city'),
        ],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * An Ajax callback.
   */
  public function cityWeatherAjaxCallback(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $api_key_length = strlen($form_state->getValue('api_key'));
    $city_name_for_weather = $form_state->getValue('city_weather');
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
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('weather.settings')
      ->set('key_weather_api', $form_state->getValue('api_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
