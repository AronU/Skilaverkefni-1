<?php

namespace Drupal\music_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form definition for the salutation message.
 */
class DataEntryForm extends FormBase
{
  public function getFormId()
  {
    return 'data_entry_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $factory = \Drupal::service('tempstore.private');
    $store = $factory->get('music_module.temp_collection');
    $value = $store->get('fetched_data');

    $obj = json_decode($value, true);

    $form['title'] = array(
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Title: '.$obj['name']),
    );

    $form['artist_link'] = array(
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Artist Link: '.$obj['uri']),
    );

    $form['description'] = array(
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Description: '.$obj['profile']),
    );

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = [
      'field_artist_link' => $form_state->getValue('artist_link'),
      'field_artist_title' => $form_state->getValue('title'),
      'field_description' => $form_state->getValue('description')
    ];
    $node = \Drupal::entityTypeManager()->getStorage('node_type')->load('artist')->create($values);
    $node->save();
  }
}
