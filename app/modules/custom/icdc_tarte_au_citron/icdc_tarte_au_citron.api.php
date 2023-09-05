<?php

/**
 * @file
 * Hooks related to icdc_tarte_au_citron API.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the render array to add library and settings needed to use the tarte au citron service.
 *
 * @param array $attachments
 *   The render array.
 */
function hook_icdc_tarte_au_citron_SERVICE_ID_alter(&$attachments) {
  $attachments['#attached']['library'][] = 'my library';
  $attachments['#attached']['drupalSettings']['mymodule'] = array('key' => 'value');
}

/**
 * @} End of "addtogroup hooks".
 */
