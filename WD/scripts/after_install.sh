#!/bin/bash

cd /var/www/nsclc_amgen/
sudo chmod +x vendor/bin/phinx
sudo chmod +x vendor/robmorgan/phinx/bin/phinx
vendor/bin/phinx migrate -e development