<?php

namespace Drupal\weather;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service get, delete and validate Weather data.
 */
class WeatherDb {

  use MessengerTrait;
  use StringTranslationTrait;

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
   * Constructs a WeatherDb object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   Translation service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(Connection $connection,
                              TranslationInterface $translation,
                              MessengerInterface $messenger,
                              ClientInterface $client,
                              ConfigFactoryInterface $config_factory) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->setMessenger($messenger);
    $this->client = $client;
    $this->configFactory = $config_factory;
  }

  /**
   * Get entries in the Weather database.
   *
   * @return string|false
   *   The weather data.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getWeatherData() {
    // @todo Detect user location by IP -> \Drupal::request()->getClientIp();.
    // '51.15.45.2';
    $client_ip = \Drupal::request()->getClientIp();
    $response_ip = $this->client->request('GET', "http://ip-api.com/json/$client_ip");
    $response_ip_content = json_decode($response_ip->getBody()->getContents(), TRUE);

    // Get city by IP or a default one.
    $city = $response_ip_content['status'] === 'fail'
      ? $this->configFactory->get('weather.settings')->get('city_weather')
      : $response_ip_content['city'];

    // Get whether data from DB.
    $data = $this->connection
      ->select('weather_table', 'db')
      ->fields('db', ['main_data_weather'])
      ->condition('data_weather', $city)
      ->condition('time', \Drupal::time()->getRequestTime() - 21600, '>')
      ->execute()->fetchField();

    // If data doesn't exist or outdated then we need to update it.
    if (!$data) {
      try {
        $data = $this->updateWeatherData($city);
      }
      catch (\Exception $e) {
        $this->messenger()->addMessage($this->t('Update failed. Message = %message', [
          '%message' => $e->getMessage(),
        ]), 'error');
        return FALSE;
      }
    }

    return $data;
  }

  /**
   * Update an entry in the Weather database.
   *
   * @param string|null $city
   *   The city for which we have to get the weather.
   *
   * @return string
   *   Weather data.
   *
   * @throws \Exception|GuzzleException
   *   Possible exceptions.
   */
  public function updateWeatherData(?string $city = NULL) {
    $config = $this->configFactory->get('weather.settings');
    $city ??= $config->get('city_weather');
    $url_weather = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$config->get('key_weather_api')}&units={$config->get('units')}";
    $response = $this->client->request('GET', $url_weather);
    $weather_data = $response->getBody()->getContents();

    $values = [
      'data_weather' => $city,
      'main_data_weather' => $weather_data,
      'time' => $this->time->getRequestTime(),
    ];

    $this->connection->Upsert('weather_table')
      ->fields(['data_weather', 'main_data_weather', 'time'])
      ->key('data_weather')
      ->values($values)
      ->execute();
    return $weather_data;
  }

  /**
   * Method description.
   *
   * @throws \Exception
   */
  public function validateWeatherData(string $city_name, $api_key): bool {
    try {
      $url = "https://api.openweathermap.org/data/2.5/weather?q=$city_name&appid=$api_key";
      $response = $this->client->request('GET', $url);
      if ($response->getStatusCode() != 200) {
        throw new \Exception('Failed to retrieve data.');
      }
      $reg_ex = "#^[A-Za-z-]+$#";
      return preg_match($reg_ex, $city_name);
    }
    catch (GuzzleException $e) {
      return FALSE;
    }
  }

}
