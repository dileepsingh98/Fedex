<?php
//Change these values below.

define('FEDEX_ACCOUNT_NUMBER', '510087780');
define('FEDEX_METER_NUMBER', '114069810');
define('FEDEX_KEY', 'VWKn2p9gLTpPjOFP');
define('FEDEX_PASSWORD', '3gAMt06jnqphtkLJOXB21iJrf');


if (!defined('FEDEX_ACCOUNT_NUMBER') || !defined('FEDEX_METER_NUMBER') || !defined('FEDEX_KEY') || !defined('FEDEX_PASSWORD')) {
    die("The constants 'FEDEX_ACCOUNT_NUMBER', 'FEDEX_METER_NUMBER', 'FEDEX_KEY', and 'FEDEX_PASSWORD' need to be defined in: " . realpath(__FILE__));
}
