#!/bin/bash
curl http://conversemsc.ru/data/axiomusCheckout/?type=updateStatusPack > /srv/conversemsc/web/axiomus_status.log
curl http://conversemsc.ru/data/axiomusCheckout/?type=updateRegions >> /srv/conversemsc/web/axiomus_status.log
php /srv/conversemsc/web/cli.php shop gdeposylkaUpdate >> /srv/conversemsc/web/axiomus_status.log
