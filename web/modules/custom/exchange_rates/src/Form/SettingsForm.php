<?php

namespace Drupal\exchange_rates\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure exchange_rates settings for this site.
 */
class SettingsForm extends ConfigFormBase {

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
      '#default_value' => t('14484123d2770bca142e4f40a0b5f0a5'),
    ];
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
      '#title' => t('Choose the currency that will be converted:'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('api_id') != 'validation') {
      $form_state->setErrorByName('api_id', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $optionsCheckboxes = $form_state->getValues('check_currency');
    $this->config('exchange_rates.settings')
      ->set('example', $form_state->getValue('api_id'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}