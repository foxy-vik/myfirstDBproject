<?php

namespace Drupal\exchange_rates\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a text block for exchange block.
 *
 * @Block(
 *   id = "exchange_rates_text_block_for_exchange",
 *   admin_label = @Translation("Currency exchange"),
 *   category = @Translation("Custom")
 * )
 */
class TextBlockForExchangeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;
  /**
   * Get Cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   *   Cache service.
   */
  private CacheBackendInterface $cache;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * Constructs a new TextBlockForExchangeBlock instance.
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
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
                              ClientInterface $client,
                              CacheBackendInterface $cache_backend,
                              TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->client = $client;
    $this->cache = $cache_backend;
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
      $container->get('cache.default'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function build() {
    $build['content'] = [];
    $query = \Drupal::database()->select('currency_data', 'db_table');
    $query->fields('db_table', ['currency_key', 'currencies']);
    $result = $query->execute()->fetchAll();
    $lastResult = count($result) - 1;
    $keyApi = $result[$lastResult]->currency_key;
    $allCurrenciesNames = explode('-', $result[$lastResult]->currencies);
    $url = 'http://api.exchangeratesapi.io/v1/latest?access_key=' . $keyApi;
    $method = 'GET';
    $cid = 'CACHE_UNIQUE_ID';

    try {
      $currency_data = $this->cache->get($cid, TRUE);
      ksm($currency_data);
      if (!$currency_data) {
        $expire = $this->time->getRequestTime() + 3600;
        $currency_data = json_decode(
        $this->client->request($method, $url)->getBody()->getContents(),
        TRUE);
      }
//      $this->cache->set($this->cacheKey, $path_lookups, $this->getRequestTime() + $twenty_four_hours);

     //$this->cache->set($cid, $currency_data);
      $code = $this->client->request($method, $url)->getStatusCode();
      if ($code !== 200) {
        return $build;
      }
      $header = $this->client->request($method, $url)->getHeaders();
      $uahEUR = $currency_data['rates']['UAH'];
      $currency_rate_array = [];
      foreach ($allCurrenciesNames as $value) {
        if ($value != 0) {
          $currency_rate_array[$value] = round($uahEUR / $currency_data['rates'][$value], 4);
        }
      }

      $build['content'][] = [
        '#theme' => 'rates_block',
        '#currencies' => $currency_rate_array,
        '#attached' => [
          'library' => [
            'exchange_rates/rates',
          ],
        ],
      ];
      return $build;
    }
    catch (GuzzleException $e) {
      $build['content'][] = [
        '#markup' => $this->t('Error'),
      ];
      return $build;
    }
  }

}
