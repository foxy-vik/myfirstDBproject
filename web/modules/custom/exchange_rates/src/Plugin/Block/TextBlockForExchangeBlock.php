<?php

namespace Drupal\exchange_rates\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [];
    $form = \Drupal::formBuilder()->getForm('Drupal\exchange_rates\Form\SettingsForm');
    $appID = '14484123d2770bca142e4f40a0b5f0a5';
    $url = 'http://api.exchangeratesapi.io/v1/latest?access_key=' . $appID;
    $method = 'GET';

    try {
      $clientResponse = $this->client->request($method, $url);
      $code = $clientResponse->getStatusCode();
      if ($code !== 200) {
        return $build;
      }
      $body = $clientResponse->getBody()->getContents();
      $allRates = json_decode($body, TRUE);
      $uahEUR = $allRates['rates']['UAH'];
      $uahUSD = round($uahEUR / $allRates['rates']['USD'], 4);
      $uahPL = round($uahEUR / $allRates['rates']['PLN'], 4);
      $build['content'][] = [
        '#theme' => 'rates_block',
        '#usd' => $uahUSD,
        '#euro' => $uahEUR,
        '#pl' => $uahPL,
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
