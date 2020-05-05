#!/bin/bash

set -e

composer install
yarn install
yarn encore production
