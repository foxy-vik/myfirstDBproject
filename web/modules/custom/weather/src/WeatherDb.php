<?php

namespace Drupal\weather;

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
   */
  public function __construct(Connection $connection,
                              TranslationInterface $translation,
                              MessengerInterface $messenger,
                              ClientInterface $client) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->setMessenger($messenger);
    $this->client = $client;
  }

  /**
   * Get entries in the Weather database.
   *
   * @return string|false
   *   The weather data.
   */
  public function getWeatherData() {
    // @todo Detect user location by IP -> \Drupal::request()->getClientIp();.
    $client_ip = \Drupal::request()->getClientIp();//'51.15.45.2';
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
   * @param array $values
   *   An array containing all the fields of the item to be updated.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   The number of updated rows.
   */
  public function updateWeatherData(array $values) {
    try {
      $query_update = $this->connection->update('weather_table')
        ->fields($values)
        ->condition('id', 1)
        ->execute();
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage($this->t('Update failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]
      ), 'error');
    }
    return $query_update ?? 0;
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
