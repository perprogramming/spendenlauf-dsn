#!/bin/bash

set -e

echo "" > .env.local
echo "APP_ENV=prod" >> .env.local

composer install

rsync --progress -r --links --exclude-from=.rsyncignore --delete . ssh-49905-plb@sinfin.de:/kunden/sinfin.de/webseiten/spendenlauf.perbernhardt.de

rm .env.local || true
