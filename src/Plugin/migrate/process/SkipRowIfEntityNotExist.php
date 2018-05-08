<?php

namespace Drupal\compro_migrate_tools\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Skip processing the current row when a destination entity does not exist.
 *
 * The skip_row_if_entity_not_exist process plugin checks whether an entity with
 * given entity information exists. If the entity exists, it is returned.
 * Otherwise, a MigrateSkipRowException is thrown.
 *
 * Available configuration keys:
 * - entity_type: The destination entity to search.  Defaults to "node".
 * - property_name: The destination property to search. Defaults to "nid".
 * - source: The source field to use in the search.
 * - message: (optional) Log a message in the {migrate_message_*} table.
 *
 * @code
 *  process:
 *    uid:
 *    -
 *      plugin: migration_lookup
 *      migration: users_migration
 *      source: uuid
 *    -
 *      # Check if a node exists in the destination database.
 *      plugin: skip_row_if_entity_not_exist
 *      entity:_type profile
 *      property_name: uid
 *      message: 'User entity not found.'
 * @endcode
 *
 * This will return the uid found in the migration_lookup plugin, if one exists.
 * Otherwise, the row will be skipped and the message will be logged.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "skip_row_if_entity_not_exist",
 *   handle_multiples = TRUE
 * )
 */
class SkipRowIfEntityNotExist extends ProcessPluginBase {

  /**
   * The entity type being searched.
   *
   * @var string
   */
  protected $entity_type = 'node';

  /**
   * The name of the property being searched.
   *
   * @var string
   */
  protected $property_name = 'nid';

  /**
   * {@inheritdoc}
   */
  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (!empty($configuration['entity'])) {
      $this->entity = $configuration['entity'];
    }

    if (!empty($configuration['property'])) {
      $this->property = $configuration['property'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $count = \Drupal::entityQuery($this->entity)
      ->condition($this->property, $value)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    if (!$count) {
      $message = isset($this->configuration['message']) ? $this->configuration['message'] : '';
      throw new MigrateSkipRowException($message);
    }

    return $value;
  }

}
