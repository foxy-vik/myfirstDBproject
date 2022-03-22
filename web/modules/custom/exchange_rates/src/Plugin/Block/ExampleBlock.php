<?php

namespace Drupal\exchange_rates\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "exchange_rates_example",
 *   admin_label = @Translation("Exchange rates for Ukraine"),
 *   category = @Translation("exchange_rates")
 * )
 */
class ExampleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $appID = '14484123d2770bca142e4f40a0b5f0a5';
    $url = 'http://api.exchangeratesapi.io/v1/latest?access_key=' . $appID;
    $method = 'GET';

    $client = \Drupal::httpClient();
    $response = $client->request($method, $url);

    $code = $response->getStatusCode();
    if ($code == 200) {
      $body = $response->getBody()->getContents();
      $allRates = json_decode($body, TRUE);
    }

    $uahEUR = $allRates['rates']['UAH'];
    $uahUSD = round($uahEUR / $allRates['rates']['USD'], 4);
    $uahPL = round($uahEUR / $allRates['rates']['PLN'], 4);

    $build['content'] = [
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

}
