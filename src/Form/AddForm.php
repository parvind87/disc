<?php

namespace Drupal\disc\Form;
//use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\disc\DefaultService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AddForm.
 */
class AddForm extends FormBase {
//class AddForm implements FormInterface, ContainerInjectionInterface {

    use StringTranslationTrait;
    use MessengerTrait;
  /**
   * Drupal\disc\DefaultService definition.
   *
   * @var \Drupal\disc\DefaultService
   */
  protected $repository;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // $instance = parent::create($container);
    // $instance->discDefault = $container->get('disc.default');
    // $instance->currentUser = $container->get('current_user');
    // return $instance;
    $form = new static(
      $container->get('disc.default'),
      $container->get('current_user')
    );
    // The StringTranslationTrait trait manages the string translation service
    // for us. We can inject the service here.
    $form->setStringTranslation($container->get('string_translation'));
    $form->setMessenger($container->get('messenger'));
    return $form;
  }
    /**
   * Construct the new form object.
   */
  public function __construct(DefaultService $repository, AccountProxyInterface $current_user) {
    $this->repository = $repository;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('Name'),
      '#maxlength' => 64,
      '#size' => 64,
      '#weight' => '0',
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#description' => $this->t('Email Address'),
      '#weight' => '1',
    ];
    $form['address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Address'),
      '#description' => $this->t('Address'),
      '#weight' => '2',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#weight' => '3',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->currentUser->isAnonymous()) {
      $form_state->setError($form['add'], $this->t('You must be logged in to add values to the database.'));
    }
    // Confirm that name is not blank.
     if (empty($form_state->getValue('name'))) {
       $form_state->setErrorByName('name', $this->t('Name must be filled.'));
     }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }


       // Gather the current user so the new record has ownership.
       $account = $this->currentUser;
       // Save the submitted entry.
       $entry = [
         'name' => $form_state->getValue('name'),
         'email' => $form_state->getValue('email'),
         'address' => $form_state->getValue('address'),
         'uid' => $account->id(),
       ];
       $return = $this->repository->insert($entry);
       if ($return) {
         $this->messenger()->addMessage($this->t('Created entry @entry', ['@entry' => print_r($entry, TRUE)]));
       }
     
  }

}
