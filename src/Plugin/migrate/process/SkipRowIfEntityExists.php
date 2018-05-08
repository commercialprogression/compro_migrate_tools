<?php

namespace Drupal\compro_migrate_tools\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Skip processing the current row when a destination entity exists (or not).
 *
 * The skip_row_if_entity_exists process plugin checks whether an entity with
 * given entity information exists. If the entity exists, the source value is
 * returned. Otherwise, a MigrateSkipRowException is thrown.
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
 *      # Check to make sure a user entity exists in the destination database.
 *      plugin: skip_row_if_entity_exists
 *      entity:_type profile
 *      property_name: uid
 *      inverse_check: FALSE
 *      message: 'User entity not found.'
 * @endcode
 *
 * This will return the source value if a user entity exists. Otherwise, the
 * row will be skipped and the message will be logged.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "skip_row_if_entity_exists",
 *   handle_multiples = TRUE
 * )
 */
class SkipRowIfEntityExists extends ProcessPluginBase {

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
   * Force the entity check to be inverse.
   */
  protected $inverse_check = FALSE;

  /**
   * {@inheritdoc}
   */
  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (!empty($configuration['entity_type'])) {
      $this->entity_type = $configuration['entity_type'];
    }

    if (!empty($configuration['property_name'])) {
      $this->property_name = $configuration['property_name'];
    }

    if (!empty($configuration['inverse_check'])) {
      $this->inverse_check = $configuration['inverse_check'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $count = \Drupal::entityQuery($this->entity_type)
      ->condition($this->property_name, $value)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    // If there is a matching entity and inverse check is false or if there is
    // no matching entity and inverse check is true, we want to fail this row.
    if ($count && $this->inverse_check === FALSE || !$count && $this->inverse_check === TRUE) {
      $message = isset($this->configuration['message']) ? $this->configuration['message'] : '';
      throw new MigrateSkipRowException($message);
    }

    return $value;
  }

}
