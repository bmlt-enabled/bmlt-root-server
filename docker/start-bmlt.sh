sed -i '/gkey/d' /var/www/html/auto-config.inc.php
sed -i '/dbName/d' /var/www/html/auto-config.inc.php
sed -i '/dbUser/d' /var/www/html/auto-config.inc.php
sed -i '/dbPassword/d' /var/www/html/auto-config.inc.php
sed -i '/dbServer/d' /var/www/html/auto-config.inc.php
sed -i '/dbPrefix/d' /var/www/html/auto-config.inc.php

if [ ! -z $GKEY ]
then
    echo "\$gkey = \"$GKEY\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DBNAME ]
then
    echo "\$dbName = \"$DBNAME\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DBUSER ]
then
    echo "\$dbUser = \"$DBUSER\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DBPASSWORD ]
then
    echo "\$dbPassword = \"$DBPASSWORD\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DBSERVER ]
then
    echo "\$dbServer = \"$DBSERVER\";" >> /var/www/html/auto-config.inc.php
fi

if [ ! -z $DBPREFIX ]
then
    echo "\$dbPrefix = \"$DBPREFIX\";" >> /var/www/html/auto-config.inc.php
fi

apachectl -D FOREGROUND
