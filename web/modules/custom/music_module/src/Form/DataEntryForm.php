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
    $discogs_data = $store->get('discogs_data');
    $brainz_data = $store->get('brainz_data');

    $discogs_obj = json_decode($discogs_data, true);
    $brainz_obj = json_decode($brainz_data, true);

    #Both discogs and musicbrainz offer a band title so they're both represented in the form.
    $form['settings']['title'] = array(
      '#type' => 'radios',
      '#title' => $this
        ->t('Artist Title'),
      '#default_value' => 0,
      '#options' => array(
        0 => $this
          ->t('Discogs title: ' . $discogs_obj['name']),
        1 => $this
          ->t('Musicbrainz title: ' . $brainz_obj['sort-name']),
      ),
    );
    #Unfortunately only discogs has some form of website link for the band.
    $form['settings']['artist_link'] = array(
      '#type' => 'radios',
      '#title' => $this
        ->t('Artist Links'),
      '#default_value' => 0,
      '#options' => array(
        0 => $this
          ->t('Discogs link: ' . $discogs_obj['uri']),
      ),
    );
    #Again discogs only has a description.
    $form['settings']['description'] = array(
      '#type' => 'radios',
      '#title' => $this
        ->t('Artist description'),
      '#default_value' => 0,
      '#options' => array(
        0 => $this
          ->t('Discogs description: ' . $discogs_obj['profile']),
      ),
    );
    #Now musicbrainz is the over of some unique values. Musicbrainz offers a start and end date for the artist.
    $form['settings']['birthday'] = array(
      '#type' => 'radios',
      '#title' => $this
        ->t('Artists established date'),
      '#default_value' => 0,
      '#options' => array(
        0 => $this
          ->t('Musicbrainz beginning: ' . $brainz_obj['life-span']['begin']),
      ),
    );
    #Some bands don't have a formal end date, so I check to make sure there's something there before displaying some
    #kind of form for the value.
    if($brainz_obj['life-span']['end'] != null) {
      $form['settings']['end'] = array(
        '#type' => 'radios',
        '#title' => $this
          ->t('Artists end date'),
        '#default_value' => 0,
        '#options' => array(
          0 => $this
            ->t('Musicbrainz end: ' . $brainz_obj['life-span']['end']),
        ),
      );
    }

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
    $factory = \Drupal::service('tempstore.private');
    $store = $factory->get('music_module.temp_collection');
    $discogs_data = $store->get('discogs_data');
    $brainz_data = $store->get('brainz_data');

    $discogs_obj = json_decode($discogs_data, true);
    $brainz_obj = json_decode($brainz_data, true);

    if($form_state->getValue('title') == 0) {
      $chosen_title = $discogs_obj['name'];
    } else {
      $chosen_title = $brainz_obj['sort-name'];
    }

    if($form_state->getValue('artist_link') == 0) {
      $chosen_uri = $discogs_obj['uri'];
    }
    if($form_state->getValue('description') == 0) {
      $chosen_description = $discogs_obj['profile'];
    }
    if($form_state->getValue('birthday') == 0) {
      $chosen_birthday = strtotime($brainz_obj['life-span']['begin']);
    }
    $values = [
      'field_artist_link' => $chosen_uri,
      'field_artist_title' => $chosen_title,
      'field_description' => $chosen_description,
      'field_birthday' => $chosen_birthday
    ];

    if($brainz_obj['life-span']['end'] != null) {
      $chosen_end = strtotime($brainz_obj['life-span']['end']);
      $values = [
        'type' => 'artist',
        'field_artist_link' => $chosen_uri,
        'field_artist_title' => $chosen_title,
        'field_description' => $chosen_description,
        'field_birthday' => $chosen_birthday,
        'field_date_of_death' => $chosen_end
      ];
    }
    #I could not figure out in time how to put the info into the artist database table.
    $node = \Drupal::entityTypeManager()->getStorage('node')->create($values);
    $node->save();
  }
}
