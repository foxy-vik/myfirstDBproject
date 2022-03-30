<?php

namespace Drupal\weather;

use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

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
   * Constructs a WeatherDb object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   *   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(Connection $connection, TranslationInterface $translation, MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->setMessenger($messenger);
  }

  /**
   * Method description.
   */
  public function getWeatherData() {
    $this->connection->select('weather_table', 'db')
      ->fields('db', ['data_weather', 'main_data_weather', 'time'])
      ->execute()->fetchAll();
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
   */
  public function validateWeatherData() {
    // @DCG place your code here.
  }

}
