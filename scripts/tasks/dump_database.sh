#!/usr/bin/env bash

CURRENT_SITE_DRUSH_ALIAS="DRUPAL_SITE_${DRUPAL_SITE^^}_DRUSH_ALIAS"
CURRENT_SITE_FOLDER_NAME="DRUPAL_SITE_${DRUPAL_SITE^^}_FOLDER_NAME"

echo -e "${COLOR_LIGHT_GREEN}${DRUPAL_SITE}: Create backup folder.${COLOR_NC}"
mkdir -p "${PROJECT_PATH}/backups/${!CURRENT_SITE_FOLDER_NAME}/${CURRENT_DATE}"

echo -e "${COLOR_LIGHT_GREEN}${DRUPAL_SITE}: Database backup.${COLOR_NC}"
$DRUSH "${!CURRENT_SITE_DRUSH_ALIAS}" sql:dump \
  --result-file="${PROJECT_PATH}/backups/${!CURRENT_SITE_FOLDER_NAME}/${CURRENT_DATE}/${!CURRENT_SITE_FOLDER_NAME}.sql" \
  --gzip
