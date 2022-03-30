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
   * @param array $values
   *   An array containing all the fields of the item to be received.
   *
   * @return array
   *   The weather data.
   */
  public function getWeatherData(array $values) {
    if (!$values) {
      return [];
    }
    $response = $this->connection
      ->select('weather_table', 'db')
      ->fields('db', $values)
      ->execute()->fetchAll();
    return $response[0];
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
      if (preg_match($reg_ex, $city_name)) {
        return TRUE;
      }
    }
    catch (GuzzleException $e) {
      return FALSE;
    }
  }

}
