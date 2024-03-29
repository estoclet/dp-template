#!/usr/bin/env bash

CURRENT_SITE_DRUSH_ALIAS="DRUPAL_SITE_${DRUPAL_SITE^^}_DRUSH_ALIAS"
CURRENT_SITE_CONFIG_SPLIT_OVERRIDES="DRUPAL_SITE_${DRUPAL_SITE^^}_CONFIG_SPLIT_OVERRIDES"

if [ "${!CURRENT_SITE_CONFIG_SPLIT_OVERRIDES}" = "1" ]; then
  echo -e "${COLOR_LIGHT_GREEN}${DRUPAL_SITE}: Export overrides config split.${COLOR_NC}"
  $DRUSH "${!CURRENT_SITE_DRUSH_ALIAS}" config-split:export overrides -y
fi
