<?php

require 'setup-app.php';

use CryptoUnifier\Cryptocurrency\CoinGecko;

// Get asset information
$asset = CoinGecko::getAssetInformation('bitcoin');

var_dump($asset->last_updated);

// Flush asset information
// CoinGecko::flushAssetInformation('bitcoin');

// sleep(2);

// Get asset information again
$asset = CoinGecko::getAssetInformation('bitcoin');

var_dump($asset->last_updated);
