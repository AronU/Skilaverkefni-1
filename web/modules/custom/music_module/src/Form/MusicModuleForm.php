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
      '#title' => $this->t('Artist ID')
    ];


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;

  }
  public function getArtist(string $artist) {
    #client = \Drupal::httpClient();
    #client->request('GET', 'https://api.discogs.com/database/search?q=' .$artist .'&per_page=3&page=1&key=fNYIIJNrBiwOfteHarRo&secret=BWKuedzmfFkvKQNJQNcwLZCVSobSgKtO');
    #request = $client->get('https://api.discogs.com/database/search?q=' .$artist .'&per_page=3&page=1&key=fNYIIJNrBiwOfteHarRo&secret=BWKuedzmfFkvKQNJQNcwLZCVSobSgKtO');
    #response = $request->getBody();
    #returnesponse;
    $artist_results = null;
    $uri = 'https://api.discogs.com/artists/';
    $options = array(
      'method' => 'GET',
      'timeout' => 3,
      'headers' => array(
        'Accept' => 'application/json',
      ),
    );
    try {
      $artist_results = \Drupal::httpClient()->get($uri . $artist, $options);
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
    $artist_data = $this->getArtist($form_state->getValue('artist_id'));
    $factory = \Drupal::service('tempstore.private');
    $store = $factory->get('music_module.temp_collection');
    $store->set('fetched_data', $artist_data);
    $form_state->setRedirect('music_module.music_form_2');
  }
}
