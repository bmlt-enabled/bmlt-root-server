sed -i '/gkey/d' /var/www/auto-config.inc.php
sed -i '/dbName/d' /var/www/auto-config.inc.php
sed -i '/dbUser/d' /var/www/auto-config.inc.php
sed -i '/dbPassword/d' /var/www/auto-config.inc.php
sed -i '/dbServer/d' /var/www/auto-config.inc.php
sed -i '/dbPrefix/d' /var/www/auto-config.inc.php
sed -i '/do_not_force_port/d' /var/www/auto-config.inc.php

if [ ! -z $GKEY ]
then
    echo "\$gkey = \"$GKEY\";" >> /var/www/auto-config.inc.php
fi

if [ ! -z $DBNAME ]
then
    echo "\$dbName = \"$DBNAME\";" >> /var/www/auto-config.inc.php
fi

if [ ! -z $DBUSER ]
then
    echo "\$dbUser = \"$DBUSER\";" >> /var/www/auto-config.inc.php
fi

if [ ! -z $DBPASSWORD ]
then
    echo "\$dbPassword = \"$DBPASSWORD\";" >> /var/www/auto-config.inc.php
fi

if [ ! -z $DBSERVER ]
then
    echo "\$dbServer = \"$DBSERVER\";" >> /var/www/auto-config.inc.php
fi

if [ ! -z $DBPREFIX ]
then
    echo "\$dbPrefix = \"$DBPREFIX\";" >> /var/www/auto-config.inc.php
fi

if [ ! -z "$DO_NOT_FORCE_PORT" -a "$DO_NOT_FORCE_PORT" == "true" ]
then
    echo "\$g_do_not_force_port = true;" >> /var/www/auto-config.inc.php
fi

apachectl -D FOREGROUND
