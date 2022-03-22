<?php

namespace Drupal\foxy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure foxy settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'foxy_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['foxy.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('foxy.settings');
    $form['city_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City:'),
      '#default_value' => $this->t('Add city name'),
      '#attributes' => [
        'class' => [
          'test-class-attributes',
          'weather-city-name',
        ],
      ],
      '#required' => TRUE,
    ];
    $form['button'] = [
      '#type' => 'button',
      '#value' => t('Add city'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $catName = $form_state->getValue('city_name');
    $catLength = strlen($catName);
    if ($catLength < 2 || $catLength > 32) {
      $form_state->setErrorByName('example', $this->t('The value is not correct.'));
    }
    else {
      $messenger = \Drupal::messenger();
      $messenger->addStatus(t('This is a successful message.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('foxy.settings')
      ->set('City name', $form_state->getValue('city_name'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
