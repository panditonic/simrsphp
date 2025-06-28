<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../observers/SatusehatObserver.php';

$observer = new SatusehatObserver();
$satusehatId = $observer->searchSatusehatIdByNik('9202125210070001');
print_r($satusehatId);