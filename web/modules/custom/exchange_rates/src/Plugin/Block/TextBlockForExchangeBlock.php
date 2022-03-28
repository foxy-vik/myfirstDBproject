<?php

namespace Drupal\exchange_rates\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
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
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  const CACHE_TIME = 3600;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
                              ClientInterface $client,
                              CacheBackendInterface $cache_backend,
                              TimeInterface $time,
                              ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->client = $client;
    $this->cache = $cache_backend;
    $this->time = $time;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ContainerFactoryPluginInterface | TextBlockForExchangeBlock | static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('cache.default'),
      $container->get('datetime.time'),
      $container->get('config.factory'),
    );
  }

  /**
   * Comment todo.
   *
   * @return array|false
   *   Description todo.
   */
  protected function getCurrencyData() {
    $cid = $this->getPluginId();
    $currency_data = $this->cache->get($cid, TRUE);
    if (!$currency_data) {
      try {
        $api_key = $this->configFactory->get('exchange_rates.settings')->get('key_api');
        $url = "http://api.exchangeratesapi.io/v1/latest?access_key=$api_key";
        $response = $this->client->request('GET', $url);
        if ($response->getStatusCode() != 200) {
          throw new \Exception('Failed to retrieve data.');
        }
        $currency_data = json_decode($response->getBody()->getContents(), TRUE);
        $expire = $this->time->getRequestTime() + self::CACHE_TIME;
        $this->cache->set($cid, $currency_data, $expire);
      }
      catch (\Throwable $e) {
        return FALSE;
      }
    }
    return $currency_data;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function build() {
    $build['content'] = [];
    if (!($currency_data = $this->getCurrencyData())) {
      return $build;
    }
    $allCurrenciesNames = $this->configFactory->get('exchange_rates.settings')->get('currencies');
    $currency_data_arr = $currency_data->data;

    $uahEUR = $currency_data_arr['rates']['UAH'];
    $currency_rate_array = [];
    foreach ($allCurrenciesNames as $value) {
      if ($value) {
        $currency_rate_array[$value] = round($uahEUR / $currency_data_arr['rates'][$value], 4);
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
      '#cache' => [
        'max-age' => self::CACHE_TIME,
      ],
    ];
    return $build;

  }

}
