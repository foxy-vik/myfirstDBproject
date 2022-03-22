<?php

/**
 * @file
 */

use Drupal\file\Entity\File;

/**
 * Implements template_preprocess_HOOK().
 */
function template_preprocess_cat_twig_table(&$variables) {
  $date_formatter = \Drupal::service('date.formatter');
  // Variables 'date' = {{ date }}.
  $variables['date'] = $date_formatter->format(time());

  $query = \Drupal::database()->select('foxywp', 'm')->fields('m', ['pid']);
  $data = $query->execute()->fetchAssoc();
  $pid = intval($data['pid']);
  $photo = [
    '#theme' => 'image_style',
    '#style_name' => 'thumbnail',
    '#uri' => File::load($pid)->getFileUri(),
  ];
  $renderer = \Drupal::service('renderer');
  $photo = $renderer->render($photo);
  $variables['photo'] = $photo;
}
