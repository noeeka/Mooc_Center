<?php
echo base64_encode(mcrypt_create_iv(32,MCRYPT_DEV_RANDOM));
