<?php
namespace Drupal\music_module\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form definition for the salutation message.
 */
class MusicModuleForm extends FormBase {

  /**protected $tempStoreFactory;

  public function __construct(
    PrivateTempStoreFactory $tempStoreFactory
  ) {
    $this->tempStoreFactory = $tempStoreFactory;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('tempstore.private')
    );
  }**/

  public function getFormId() {
    return 'music_module_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['artist_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Artist Name')
    ];


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;

  }

  public function getArtistMultipleServices(string $artist) {
    #I know this is a criminal offence punishable by death but it's crunch time :P
    $discogs_key = 'fNYIIJNrBiwOfteHarRo';
    $discogs_secret = 'BWKuedzmfFkvKQNJQNcwLZCVSobSgKtO';
    $discogs_uri = 'https://api.discogs.com/database/search?q=' . $artist . '&per_page=3&page=1&key=' . $discogs_key . ' &secret=' . $discogs_secret;
    $discogs_data = $this->apiCaller($discogs_uri);
    $discogs_array = json_decode($discogs_data, true);
    $discogs_artist_id = $discogs_array['results'][0]['id'];
    $discogs_artist_uri = 'https://api.discogs.com/artists/' . $discogs_artist_id;
    $discogs_singular_artist_data = $this->apiCaller($discogs_artist_uri);
    #Here's the same thing for musicbrainz below.
    $musicbrainz_uri = 'http://musicbrainz.org/ws/2/artist/?query=artist:'. $artist . '%20';
    $brainz_data = $this->apiCaller($musicbrainz_uri);
    $brainz_array = json_decode($brainz_data, true);
    $brainz_artist_id = $brainz_array['artists'][0]['id'];
    $brainz_artist_uri = 'http://musicbrainz.org/ws/2/artist/' . $brainz_artist_id . '?inc=aliases';
    $brainz_singular_artist_data = $this->apiCaller($brainz_artist_uri);
    #return array because we need to return both discogs and musicbrainz answers.
    $return_array = array();
    array_push($return_array, $discogs_singular_artist_data);
    array_push($return_array, $brainz_singular_artist_data);
    return $return_array;
  }
  public function apiCaller($uri) {
    #universal API get function to get a json represponse from the external API
    $artist_results = null;

    $options = array(
      'method' => 'GET',
      'timeout' => 3,
      'headers' => array(
        'Accept' => 'application/json',
      ),
    );
    try {
      $artist_results = \Drupal::httpClient()->get($uri, $options);
      $artist_results = (string) $artist_results->getBody();
      if(empty($artist_results)) {
        return FALSE;
      }

    }
    catch( \Drupal\Core\Queue\RequeueException $e) {
      return FALSE;
    }
  return $artist_results;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $artist_data = $this->getArtistMultipleServices($form_state->getValue('artist_id'));
    #Temp storage set up to move onto the later form. Discogs and musicbrainz store in different places. 
    $factory = \Drupal::service('tempstore.private');
    $store = $factory->get('music_module.temp_collection');
    $store->set('discogs_data', $artist_data[0]);
    $store->set('brainz_data', $artist_data[1]);
    $form_state->setRedirect('music_module.music_form_2');
  }
}
