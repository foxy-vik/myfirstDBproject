<?php

namespace Drupal\weather\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Weather settings for this site.
 */
class SettingsForm extends ConfigFormBase {

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
      $response = \Drupal::httpClient()->request('GET', $url_weather);
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
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $api_key_length = strlen($form_state->getValue('api_key'));
    if ($api_key_length != 32 || empty($form_state->getValue('city_weather'))) {
      $form_state->setErrorByName('api_key', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);
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
