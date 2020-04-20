#!/bin/bash

set -e

echo "" > .env.local
echo "APP_ENV=dev" >> .env.local
echo "DATABASE_URL=mysql://db49905_13:$DB_PASSWORD@mysql5.sinfin.de/db49905_13?serverVersion=5.6.19" >> .env.local

composer install

rsync --progress -r --links --exclude-from=.rsyncignore --delete . ssh-49905-plb@sinfin.de:/kunden/sinfin.de/webseiten/spendenlauf.perbernhardt.de

rm .env.local || true
