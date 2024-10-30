<?php

// 更多精品WP资源尽在喵容：miaoroom.com

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Symfony\\Polyfill\\Php72\\' => array($vendorDir . '/symfony/polyfill-php72'),
    'Symfony\\Polyfill\\Mbstring\\' => array($vendorDir . '/symfony/polyfill-mbstring'),
    'Symfony\\Polyfill\\Intl\\Idn\\' => array($vendorDir . '/symfony/polyfill-intl-idn'),
    'Psr\\Http\\Message\\' => array($vendorDir . '/psr/http-message/src'),
    'MRTB\\' => array($baseDir . '/src'),
    'GuzzleHttp\\Psr7\\' => array($vendorDir . '/guzzlehttp/psr7/src'),
    'GuzzleHttp\\Promise\\' => array($vendorDir . '/guzzlehttp/promises/src'),
    'GuzzleHttp\\' => array($vendorDir . '/guzzlehttp/guzzle/src'),
);
