<?php

namespace Drupal\disc;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Class DefaultService.
 */
class DefaultService {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }
  public function load(array $entry = []) {
    // Read all the fields from the disc table.
    $select = $this->database->select('disc','m');
    $select->join('users_field_data', 'u', 'm.uid = u.uid');
      $select->addField('m', 'id');
      $select->addField('m', 'name');
      $select->addField('m', 'email');
      $select->addField('m', 'address');
      $select->addField('u', 'name');

    // Add each field and value as a condition to this query.
    foreach ($entry as $field => $value) {
      $select->condition($field, $value);
    }
    // Return the result in object format.
    return $select->execute()->fetchAll();
  }

  public function insert(array $entry) {
    try {
      $return_value = $this->database->insert('disc')
        ->fields($entry)
        ->execute();
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage(t('Insert failed. Message = %message', [
        '%message' => $e->getMessage(),
      ]), 'error');
    }
    return $return_value ?? NULL;
  }
    /**
   * Update an entry in the database.
   *
   * @param array $entry
   *   An array containing all the fields of the item to be updated.
   *
   * @return int
   *   The number of updated rows.
   */
  public function update(array $entry) {
    try {
      // Connection->update()...->execute() returns the number of rows updated.
      $count = $this->database->update('disc')
        ->fields($entry)
        ->condition('id', $entry['id'])
        ->execute();
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage(t('Update failed. Message = %message, query= %query', [
        '%message' => $e->getMessage(),
        '%query' => $e->query_string,
      ]
      ), 'error');
    }
    return $count ?? 0;
  }

}
