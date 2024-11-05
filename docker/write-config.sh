sed -i '/gkey/d' /var/www/html/auto-config.inc.php
sed -i '/dbName/d' /var/www/html/auto-config.inc.php
sed -i '/dbUser/d' /var/www/html/auto-config.inc.php
sed -i '/dbPassword/d' /var/www/html/auto-config.inc.php
sed -i '/dbServer/d' /var/www/html/auto-config.inc.php
sed -i '/dbPrefix/d' /var/www/html/auto-config.inc.php
sed -i '/do_not_force_port/d' /var/www/html/auto-config.inc.php
sed -i '/default_duration_time/d' /var/www/html/auto-config.inc.php
sed -i '/g_enable_language_selector/d' /var/www/html/auto-config.inc.php

if [ ! -z $GKEY ]
then
    echo "\$gkey = \"$GKEY\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DB_DATABASE ]
then
    echo "\$dbName = \"$DB_DATABASE\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DB_USER ]
then
    echo "\$dbUser = \"$DB_USER\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DB_PASSWORD ]
then
    echo "\$dbPassword = \"$DB_PASSWORD\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DB_HOST ]
then
    echo "\$dbServer = \"$DB_HOST\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DB_PREFIX ]
then
    echo "\$dbPrefix = \"$DB_PREFIX\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z "$DO_NOT_FORCE_PORT" -a "$DO_NOT_FORCE_PORT" == "true" ]
then
    echo "\$g_do_not_force_port = true;" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z "$NEW_UI_ENABLED" -a "$NEW_UI_ENABLED" == "true" ]
then
    echo "\$new_ui_enabled = true;" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z "$AGGREGATOR_MODE_ENABLED" -a "$AGGREGATOR_MODE_ENABLED" == "true" ]
then
    echo "\$aggregator_mode_enabled = true;" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z "$AGGREGATOR_MAX_GEO_WIDTH_KM" ]
then
    echo "\$aggregator_max_geo_width_km = ${AGGREGATOR_MAX_GEO_WIDTH_KM};" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z "$MEETING_STATES_AND_PROVINCES" ]
then
    echo "\$meeting_states_and_provinces = \"${MEETING_STATES_AND_PROVINCES}\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z "$MEETING_COUNTIES_AND_SUB_PROVINCES" ]
then
    echo "\$meeting_counties_and_sub_provinces = \"${MEETING_COUNTIES_AND_SUB_PROVINCES}\";" >> /var/www/html/auto-config.inc.php
fi

echo "\$default_duration_time = \"${DEFAULT_DURATION_TIME:-01:00:00}\";" >> /var/www/html/auto-config.inc.php

if [ "$ENABLE_LANGUAGE_SELECTOR" == "false" ]; then
    echo "\$g_enable_language_selector = false;" >> /var/www/html/auto-config.inc.php
else
    echo "\$g_enable_language_selector = true;" >> /var/www/html/auto-config.inc.php
fi
