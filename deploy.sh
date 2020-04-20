#!/bin/bash

set -e

echo "" > .env.local
echo "APP_ENV=dev" >> .env.local
echo "DATABASE_URL=mysql://db49905_13:$DB_PASSWORD@mysql5.sinfin.de/db49905_13?serverVersion=5.6.19" >> .env.local

composer install

rsync -r --links --exclude-from=.rsyncignore --delete . ssh-49905-plb@sinfin.de:/kunden/sinfin.de/webseiten/spendenlauf.perbernhardt.de

ssh ssh-49905-plb@sinfin.de "cd /kunden/sinfin.de/webseiten/spendenlauf.perbernhardt.de && /usr/local/bin/php73 bin/console doctrine:migrations:migrate -n --allow-no-migration"

rm .env.local || true
