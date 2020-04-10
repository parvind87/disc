<?php

namespace Drupal\disc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\disc\DefaultService;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  /**
   * The repository for our specialized queries.
   *
   * @var \Drupal\disc\DefaultService
   */
  protected $repository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $controller = new static($container->get('disc.default'));
   // $controller->setStringTranslation($container->get('string_translation'));
    return $controller;
  }

  /**
   * Construct a new controller.
   *
   * @param \Drupal\disc\DefaultService $repository
   *   The repository service.
   */
  public function __construct(DefaultService $repository) {
    $this->repository = $repository;
  }

  /**
   * List.
   *
   * @return string
   *   Return Hello string.
   */
  public function list() {
    // return [
    //   '#type' => 'markup',
    //   '#markup' => $this->t('Implement method: list')
    // ];
    $content = [];

    $content['message'] = [
      '#markup' => $this->t('Generate a list of all entries in the database. There is no filter in the query.'),
    ];

    $rows = [];
    $headers = [
      $this->t('Id'),
      $this->t('Name'),
      $this->t('Email'),
      $this->t('Address'),
      $this->t('Author'),
      //$this->t('Age'),
    ];

    foreach ($entries = $this->repository->load() as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\Html::escape', (array) $entry);
    }
    $content['table'] = [
      '#type' => 'table',
      '#attributes' => ['id' => 'disc-members-list'],
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No entries available.'),
    ];
    // Don't cache this page.
    $content['#cache']['max-age'] = 0;

    return $content;
  }

}
