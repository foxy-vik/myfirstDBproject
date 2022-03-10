<?php

namespace Drupal\paragraph_behaviors\Plugin\paragraphs\Behavior;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
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
   * If true - will be use for ALL paragraphs.
   *
   * {@inheritdoc}
   */
  public static function isApplicable(ParagraphsType $paragraphs_type): bool {
    return $paragraphs_type->id() == "image_only";
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {
    $images_per_row = $paragraph->getBehaviorSetting($this->getPluginId(), 'item_per_row', 4);
    $bem_block = 'paragraph-' . $paragraph->bundle() . '--images-per-row-' . $images_per_row;
    /* Html::getClass - fixed issues in our class. */
    $build['#attributes']['class'][] = Html::getClass($bem_block);
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['items_per_row'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of images per row'),
      '#options' => [
        '2' => $this->formatPlural(2, '1 photo', '@count photo per row'),
        '3' => $this->formatPlural(3, '1 photo', '@count photo per row'),
        '4' => $this->formatPlural(4, '1 photo', '@count photo per row'),
      ],
      '#default' => 4,
    ];
    return $form;
  }

}
