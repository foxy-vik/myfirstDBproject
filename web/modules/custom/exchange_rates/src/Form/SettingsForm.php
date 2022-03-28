<?php

namespace Drupal\exchange_rates\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure exchange_rates settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exchange_rates_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['exchange_rates.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['api_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add here your API Access Key'),
      '#default_value' => $this->config('exchange_rates.settings')
        ->get('key_api'),
    ];
    $default_currencies = $this->config('exchange_rates.settings')->get('currencies');
    $default_currency = [];
    foreach ($default_currencies as $value) {
      if ($value) {
        $default_currency[] = $value;
      }
    }
    $form['check_currency'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'USD' => $this->t('USD - United States Dollar'),
        'EUR' => $this->t('EUR - European Union Euro'),
        'GBP' => $this->t('GBP - United Kingdom Pound sterling'),
        'PLN' => $this->t('PLN - Poland złoty'),
        'CHF' => $this->t('CHF - Swiss Franc'),
        'CAD' => $this->t('CAD - Canadian Dollar'),
        'AUD' => $this->t('AUD - Australian Dollar'),
        'HUF' => $this->t('HUF - Hungarian Forint'),
        'AED' => $this->t('AED - United Arab Emirates Dirham'),
      ],
      '#default_value' => $default_currency,
      '#title' => $this->t('Choose the currency that will be converted:'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $key_API = $form_state->getValue('api_id');
    if (strlen($key_API) != 32) {
      $form_state->setErrorByName('api_id', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addStatus('Successfully saved into database');
    $this->config('exchange_rates.settings')
      ->set('key_api', $form_state->getValue('api_id'))
      ->set('currencies', $form_state->getValue('check_currency'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
