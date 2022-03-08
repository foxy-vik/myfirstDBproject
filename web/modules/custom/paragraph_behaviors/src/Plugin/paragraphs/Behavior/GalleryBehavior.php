<?php

namespace Drupal\paragraph_behaviors\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Annotation\ParagraphsBehavior;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Description.
 *
 * @ParagraphsBehavior(
 *  id = "paragraph_behaviors_gallery",
 *  label = @Translation ("Gallery settings"),
 *  description = @Translation("Settings for gallery paragraph type."),
 *  weight =0,
 * )
 */
class GalleryBehavior extends ParagraphsBehaviorBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(ParagraphsType $paragraphs_type): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {
    /* @todo Fix problem X here. */
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['items_per_row'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of images per row'),
      '#options' => [
        '2' => \Drupal::translation()->formatPlural(2, '1 photo', '@count photo per row'),
        '3' => $this->formatPlural(3, '1 photo', '@count photo per row'),
        '4' => $this->formatPlural(4, '1 photo', '@count photo per row'),
      ],
    ];
    return $form;
  }

}
