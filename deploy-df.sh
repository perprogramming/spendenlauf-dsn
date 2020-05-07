#!/bin/bash

set -e

echo "" > .env.local
echo "APP_ENV=prod" >> .env.local
echo "APP_SECRET=$APP_SECRET" >> .env.local
echo "DATABASE_URL=mysql://db49905_13:$DB_PASSWORD@mysql5.sinfin.de/db49905_13?serverVersion=5.6.19" >> .env.local
echo "MAILER_DSN=smtps://info%40spendenlauf.perbernhardt.de:$MAIL_PASSWORD@smtprelaypool.ispgateway.de" >> .env.local
echo "MAIL_SENDER=info@spendenlauf.perbernhardt.de" >> .env.local

rsync -r --links --exclude-from=.rsyncignore --delete . ssh-49905-plb@sinfin.de:/kunden/sinfin.de/webseiten/spendenlauf.perbernhardt.de

ssh ssh-49905-plb@sinfin.de "cd /kunden/sinfin.de/webseiten/spendenlauf.perbernhardt.de && /usr/local/bin/php73 bin/console --env=dev doctrine:migrations:migrate -n --allow-no-migration && /usr/local/bin/php73 bin/console cache:clear"

rm .env.local || true
