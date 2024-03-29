#!/usr/bin/env bash
# shellcheck disable=SC2029

usage() {
  printf "install.sh [integ|staging|preprod|prod] [all|default]\n"
}

environment=''

if [ -n "$1" ]; then
  environment=$1
fi

# Check that all required parameters are present.
if [ -z "${environment}" ]; then
  echo "Missing target environment parameter."
  usage
  exit 1
elif [ "${environment}" = "local" ]; then
  echo "This script is not meant to be launched for local environment."
  usage
  exit 1
fi

# shellcheck source=scripts/script-parameters.sh
. "$(dirname "${BASH_SOURCE[0]}")"/script-parameters.sh "${environment}"

# shellcheck source=scripts/selection-site.sh
. "$(dirname "${BASH_SOURCE[0]}")"/selection-site.sh "$2"

# Check that the main front server is reachable.
if ! ping -c 1 "${FRONT_MAIN_SERVER_HOST}"; then
  echo -e "${COLOR_LIGHT_RED}Server ${FRONT_MAIN_SERVER_HOST}: Impossible to ping the server.${COLOR_NC}"
  exit 1
fi

for DRUPAL_SITE in "${DRUPAL_SITES_LIST[@]}"
do
  FOLDER_NAME="DRUPAL_SITE_${DRUPAL_SITE^^}_FOLDER_NAME"
  DRUSH_ALIAS="DRUPAL_SITE_${DRUPAL_SITE^^}_DRUSH_ALIAS"
  HAS_EXPORTED_CONFIG="DRUPAL_SITE_${DRUPAL_SITE^^}_HAS_EXPORTED_CONFIG"
  DRUSH="${DEPLOYMENT_PATH}/sites/${!FOLDER_NAME}/current/vendor/bin/drush"

  REDIS="DRUPAL_SITE_${DRUPAL_SITE^^}_REDIS"
  REDIS_HOST="DRUPAL_SITE_${DRUPAL_SITE^^}_REDIS_HOST"
  REDIS_PORT="DRUPAL_SITE_${DRUPAL_SITE^^}_REDIS_PORT"
  REDIS_PASSWORD="DRUPAL_SITE_${DRUPAL_SITE^^}_REDIS_PASSWORD"
  REDIS_BASE="DRUPAL_SITE_${DRUPAL_SITE^^}_REDIS_BASE"

  INSTALLATION_PROFILE="DRUPAL_SITE_${DRUPAL_SITE^^}_INSTALLATION_PROFILE"
  ACCOUNT_MAIL="DRUPAL_SITE_${DRUPAL_SITE^^}_ACCOUNT_MAIL"
  ACCOUNT_NAME="DRUPAL_SITE_${DRUPAL_SITE^^}_ACCOUNT_NAME"
  ACCOUNT_PASS="DRUPAL_SITE_${DRUPAL_SITE^^}_ACCOUNT_PASS"
  SITE_MAIL="DRUPAL_SITE_${DRUPAL_SITE^^}_SITE_MAIL"
  SITE_NAME="DRUPAL_SITE_${DRUPAL_SITE^^}_SITE_NAME"
  DEFAULT_LANGUAGE="DRUPAL_SITE_${DRUPAL_SITE^^}_DEFAULT_LANGUAGE"

  # Test if the "current" symlink exists for this website.
  if $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "test -e ${DEPLOYMENT_PATH}/sites/${!FOLDER_NAME}/current"; then
    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Disable Crontab.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${PROJECT_USER} touch ${DEPLOYMENT_PATH}/sites/${!FOLDER_NAME}/disabled_cron"

    if [ "${REDIS}" = "yes" ]; then
      echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Clear Redis cache because otherwise it is no emptied on site install and it provokes errors.${COLOR_NC}"
      if [ -z "${!REDIS_PASSWORD}" ]; then
        $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${PROJECT_USER} redis-cli -h ${!REDIS_HOST} -p ${!REDIS_PORT} -n ${!REDIS_BASE} FLUSHDB"
      else
        $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${PROJECT_USER} redis-cli -h ${!REDIS_HOST} -p ${!REDIS_PORT} -n ${!REDIS_BASE} -a ${!REDIS_PASSWORD} FLUSHDB"
      fi
    fi

    if [ "${!HAS_EXPORTED_CONFIG}" = "true" ]; then
      echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Install Drupal using existing config.${COLOR_NC}"
      $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH site:install ${!INSTALLATION_PROFILE} \
        --account-mail='${!ACCOUNT_MAIL}' \
        --account-name='${!ACCOUNT_NAME}' \
        --account-pass='${!ACCOUNT_PASS}' \
        --site-mail='${!SITE_MAIL}' \
        --site-name='${!SITE_NAME}' \
        --locale='${!DEFAULT_LANGUAGE}' \
        --sites-subdir='${!FOLDER_NAME}' \
        --existing-config \
        -y"
    else
      echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Install Drupal.${COLOR_NC}"
      $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH site:install ${!INSTALLATION_PROFILE} \
        --account-mail='${!ACCOUNT_MAIL}' \
        --account-name='${!ACCOUNT_NAME}' \
        --account-pass='${!ACCOUNT_PASS}' \
        --site-mail='${!SITE_MAIL}' \
        --site-name='${!SITE_NAME}' \
        --locale='${!DEFAULT_LANGUAGE}' \
        --sites-subdir='${!FOLDER_NAME}' \
        -y"
    fi

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Sometimes there is a cache issue after the install. Launch a command to help fix the cache.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} core:status"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Flush caches to be clean.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} cache:rebuild"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Fix user 1 in case of install problem.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} php:script install-fix-user-1 \
      '${!ACCOUNT_NAME}' \
      '${!ACCOUNT_MAIL}' \
      '${!ACCOUNT_PASS}' \
      --script-path='${DEPLOYMENT_PATH}/sites/${!FOLDER_NAME}/current/drush/scripts'"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Add the administrator role to user 1.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} user:role:add administrator '${!ACCOUNT_NAME}'"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Launch database updates.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} updatedb --no-cache-clear -y"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Launch database updates a second time. Just in case...${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} updatedb --no-cache-clear -y"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Clear cache to be sure cache are cleared even if there is no update.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} cache:rebuild"

    if [ "${!HAS_EXPORTED_CONFIG}" = "true" ]; then
      echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Import configuration.${COLOR_NC}"
      $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} config:import -y"

      echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Import configuration a second time. In case the config of modules altering configuration import has changed.${COLOR_NC}"
      $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} config:import -y"
    fi

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
      $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} pm:install ${MODULES} -y"
    fi

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Install the modules required to import translations.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} pm:install locale -y"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Update translations status.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} locale:check"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Update translations.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} locale:update"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Import custom translations.${COLOR_NC}"
    # Expected translation file pattern is "XXX.[langcode].po".
    for TRANSLATION_FILE_PATH in $($SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "find ${DEPLOYMENT_PATH}/sites/${!FOLDER_NAME}/current/app/translations/custom/*.po -type f")
    do
      FILE_NAME=$(basename "${TRANSLATION_FILE_PATH}")
      LANGCODE=$(echo "${FILE_NAME}" | cut -d'.' -f2)
      $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} locale:import \
        ${LANGCODE} \
        ${TRANSLATION_FILE_PATH} \
        --type=not-customized \
        --override=all"
    done

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Run deploy hooks.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} deploy:hook -y"

    #echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Import default content.${COLOR_NC}"
    echo -e "${COLOR_BROWN_ORANGE}Implements default content import (to implement per project).${COLOR_NC}"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Flush caches to be clean.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${WEBSERVER_USER} $DRUSH ${!DRUSH_ALIAS} cache:rebuild"

    echo -e "${COLOR_LIGHT_GREEN}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Enable Crontab.${COLOR_NC}"
    $SSH "${SSH_USER}"@"${FRONT_MAIN_SERVER_HOST}" "sudo -u ${PROJECT_USER} rm -f ${DEPLOYMENT_PATH}/sites/${!FOLDER_NAME}/disabled_cron"
  else
    echo -e "${COLOR_BROWN_ORANGE}Server ${FRONT_MAIN_SERVER_HOST}: ${DRUPAL_SITE}: Is not enabled yet (no current symlink).${COLOR_NC}"
  fi
done
