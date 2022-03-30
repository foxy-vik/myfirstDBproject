<?php

namespace Drupal\weather\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\CronInterface;
use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Weather routes.
 */
class WeatherController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The cron service.
   *
   * @var \Drupal\Core\CronInterface
   */
  protected $cron;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\CronInterface $cron
   *   The cron service.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(Connection $connection, CronInterface $cron, ClientInterface $client, TimeInterface $time) {
    $this->connection = $connection;
    $this->cron = $cron;
    $this->client = $client;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('cron'),
      $container->get('http_client'),
      $container->get('datetime.time')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {
    // @todo Gonna add cron operations with DB.
    //   $test1 = $this->cron->run();
    //   $test2 = $this->messenger()->addMessage($this->t('Cron run successfully.'));
    $responce_db = $this->connection->select('weather_table', 'db')
      ->fields('db', ['data_weather', 'main_data_weather', 'time'])
      ->execute()->fetchAll();
    $response_weather = json_decode($responce_db[0]->main_data_weather);
    ksm($response_weather);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Something'),
    ];

    return $build;
  }

}
