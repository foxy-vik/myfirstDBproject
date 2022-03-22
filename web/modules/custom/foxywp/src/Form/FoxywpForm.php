<?php

namespace Drupal\foxywp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Database\Connection;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a foxywp form.
 */
class FoxywpForm extends FormBase {

  /**
   * Protected variable.
   */
  protected $dbConnection;

  /**
   * Constructs a new catForm object.
   *
   * @param \Drupal\Core\Database\Connection $dbConnection
   */
  public function __construct(Connection $dbConnection) {
    $this->dbConnection = $dbConnection;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container): FoxywpForm {

    /**
     * @var \Drupal\Core\Database\Connection $dbConnection
     */
    $dbConnection = $container->get('database');
    return new static($dbConnection);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'foxywp_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['item'] = [
      '#type' => 'page_title',
      '#title' => $this->t("You can add here a photo of your cat!"),

    ];

    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Your cat's name:"),
      '#required' => TRUE,
      '#placeholder' => t(
        'Name of the cat must be min-2 letters and max-32 letters'
      ),
      '#wrapper_attributes' => ['class' => 'input-cats-name'],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t("Your e-mail"),
      '#required' => TRUE,
      '#placeholder' => t('example@email.com'),
      '#ajax' => [
        'callback' => '::emailAjaxCallback',
        'event' => 'input',
        'progress' => [
          'type' => 'custom',
        ],
      ],
      '#suffix' => '<div class="email-validation-message"></div>',
    ];

    $form['catimage'] = [
      '#title' => t('Add picture with your cat'),
      '#description' => $this->t(
        'Add image using one of this formats png jpg jpeg'
      ),
      '#type' => 'managed_file',
      '#required' => TRUE,
      '#upload_location' => 'public://images/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2 * 1024 * 1024],
      ],
      '#wrapper_attributes' => ['class' => 'cats-picture'],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t('Add cat'),
      '#ajax' => [
        'callback' => '::submitAjaxCallback',
        'wrapper' => 'foxywp-form',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];

    $form['system_messages'] = [
      '#markup' => '<div id="cats-system-messages"></div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $messenger = \Drupal::messenger();
    $message = [
      '#theme' => 'status_messages',
      '#message_list' => $messenger->all(),
      '#status_headings' => [
        'status' => t('Status message'),
        'error' => t('Error message'),
        'warning' => t('Warning message'),
      ],
    ];

    $messages = \Drupal::service('renderer')->render($message);
    $response->addCommand(
      new HtmlCommand('#cats-system-messages', $messages)
    );

    $messenger->deleteAll();

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function emailAjaxCallback(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if (preg_match(
      "/^[a-zA-Z_\-]+@[a-zA-Z_\-]+\.[a-zA-Z.]{2,6}+$/",
      $form_state->getValue('email')
    )
    ) {
      $css = ['border' => '2px solid green'];
      $message = $this->t('Email ok.');
    }
    else {
      $css = ['border' => '2px solid red'];
      $message = $this->t('Email not valid.');
    }
    $response->addCommand(new CssCommand('#edit-email', $css));
    $response->addCommand(
      new HtmlCommand('.email-validation-message', $message)
    );

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (mb_strlen($form_state->getValue('message')) < 2) {
      $form_state->setErrorByName(
        'name',
        $this->t("The cat's name must  be at least 2 characters.")
      );
    }
    elseif (mb_strlen($form_state->getValue('message')) > 32) {
      $form_state->setErrorByName(
        'name',
        $this->t("The cat's name must be no longer than 32 characters")
      );
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The cat added'));
    $form_state->setRedirect('foxywp/cats');

    // Better (int) time() than (varchar) date("d/m/y h:i:s");.
    $image = $form_state->getValue('catimage');
    $data = [
      'message' => $form_state->getValue('message'),
      'email' => $form_state->getValue('email'),
      'pid' => $image[0],
      'time' => time(),
    ];

    // Load the object of the file by its Pid.
    $file = File::load($image[0]);
    if (!empty($file)) {
      $file->setPermanent();
      $file->save();
    }
    // Insert data to database via Dependency injection.
    // Without Dependency injection \Drupal::database()->insert('foxywp')->fields($data)->execute();=\Drupal::service('database');
    $this->dbConnection->insert('foxywp')->fields($data)->execute();

  }

}
