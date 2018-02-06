<?php

/**
 * @file
 * Custom migrate process plugin for formatting phone numbers.
 */

namespace Drupal\compro_migrate_tools\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Role translation process plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "format_phone_number"
 * )
 *
 * Example, full configuration:
 * @code
 *   field_phone:
 *     plugin: format_phone_number
 *     source: old_phone_number
 *     # Format defaults to $1-$2-$3, but is configurable.
 *     format: "($1) $2-$3"
 * @endcode
 */
class FormatPhoneNumber extends ProcessPluginBase {

  /**
   * @var $numberFormat
   */
  protected $numberFormat;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Get the number format.
    $this->numberFormat = isset($this->configuration['format']) ? $this->configuration['format'] : '$1-$2-$3';
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($value)) {
      // Remove any non digits from the phone number.
      $value_remove_nondigits = preg_replace('/[^\d]/', "", $value);
      // Dat US number has to be 10 digits.
      if (strlen($value_remove_nondigits) == 10) {
        $clean_number = preg_replace('/(\d{3})(\d{3})(\d{4})/', $this->numberFormat, $value_remove_nondigits);
        if (!empty($clean_number)) {
          // We should have a good number here.
         return $clean_number;
        }
      }

      // If we get here, the phone number isn't valid, so just return empty or
      // the original number, depending on the plugin configuration.
      if ($this->configuration['invalid_return_empty']) {
        $value = '';
      }

      return $value;
    }
  }

}
