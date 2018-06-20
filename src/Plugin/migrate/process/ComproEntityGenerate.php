<?php

namespace Drupal\compro_migrate_tools\Plugin\migrate\process;

use Drupal\migrate_plus\Plugin\migrate\process\EntityGenerate;

/**
 * This plugin extends entity_generate to allow for the use of source data.
 *
 * @MigrateProcessPlugin(
 *   id = "compro_entity_generate"
 * )
 *
 * @see EntityLookup
 *
 * All the configuration from the lookup plugin applies here. In its most
 * simple form, this plugin needs no configuration. If there are fields on the
 * generated entity that are required or need some default value, that can be
 * provided via a default_values configuration option.
 *
 * Example usage with default_values configuration:
 * @code
 * destination:
 *   plugin: 'entity:node'
 * process:
 *   type:
 *     plugin: default_value
 *     default_value: page
 *   field_tags:
 *     plugin: entity_generate
 *     source: tags
 *     default_values:
 *       description: source_field_name
 *       field_long_description: Default long description
 * @endcode
 */
class ComproEntityGenerate extends EntityGenerate {

  /**
   * Fabricate an entity.
   *
   * @param mixed $value
   *   Primary value to use in creation of the entity.
   *
   * @return array
   *   Entity value array.
   */
  protected function entity($value) {
    $entity_values = [$this->lookupValueKey => $value];
    $source = $this->migration->getSourcePlugin()->current()->getSource();

    if ($this->lookupBundleKey) {
      $entity_values[$this->lookupBundleKey] = $this->lookupBundle;
    }

    // Gather any default values (or field references) for properties/fields.
    if (isset($this->configuration['default_values']) && is_array($this->configuration['default_values'])) {
      foreach ($this->configuration['default_values'] as $key => $value) {
        if (isset($source[$value])) {
          $entity_values[$key] = $source[$value];
        }
        else {
          $entity_values[$key] = $value;
        }
      }
    }

    return $entity_values;
  }

}
