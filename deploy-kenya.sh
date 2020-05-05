#!/bin/bash

set -ex

echo "" > .env.local
echo "APP_ENV=prod" >> .env.local
echo "APP_SECRET=$APP_SECRET" >> .env.local
echo "DATABASE_URL=mysql://germansc_charity:$DB_PASSWORD_KENYA@127.0.0.1:3306/germansc_charity?serverVersion=10.3.22" >> .env.local
echo "MAILER_DSN=smtps://info%40spendenlauf.perbernhardt.de:$MAIL_PASSWORD@smtprelaypool.ispgateway.de" >> .env.local

cat <<EOF > .htaccess
# php -- BEGIN cPanel-generated handler, do not edit
# Set the “ea-php71” package as the default “PHP” programming language.
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php71 .php .php7 .phtml
</IfModule>
# php -- END cPanel-generated handler, do not edit
EOF

# copy to jumphost
rsync -r --links --exclude-from=.rsyncignore --delete . PB@timbernhardt.synology.me:/var/services/homes/PB/spendenlauf

# rsync from jumphost to target
ssh -t -A PB@timbernhardt.synology.me "cd spendenlauf && rsync -e \"ssh -t -p 6543\" -r --links --delete . germansc@germanschool.co.ke:/home/germansc/charity.germanschool.co.ke"

# run migrations on target
ssh -t -A PB@timbernhardt.synology.me "ssh -t -p 6543 germansc@germanschool.co.ke \"cd /home/germansc/charity.germanschool.co.ke && php bin/console --env=dev doctrine:migrations:migrate -n --allow-no-migration && php bin/console cache:clear\""

rm .env.local || true
