#!/usr/bin/env bash

CURRENT_SITE_DRUSH_ALIAS="DRUPAL_SITE_${DRUPAL_SITE^^}_DRUSH_ALIAS"

if [ "${DRUPAL_INSTALL_OPTIONAL_MODULES}" = "yes" ]; then
  echo -e "${COLOR_LIGHT_GREEN}${DRUPAL_SITE}: Install optional modules.${COLOR_NC}"
  MODULES=''
  # shellcheck disable=2153
  for OPTIONAL_MODULE in "${OPTIONAL_MODULES[@]}"
  do
    MODULES="${MODULES} ${OPTIONAL_MODULE}"
  done
  # shellcheck disable=2086
  # Avoid double quotes around $MODULES because we specifically wants word
  # splitting.
  $DRUSH "${!CURRENT_SITE_DRUSH_ALIAS}" pm:install ${MODULES} -y
fi
