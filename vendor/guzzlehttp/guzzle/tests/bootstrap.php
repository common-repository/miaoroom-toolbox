<?php

namespace {
    setlocale(LC_ALL, 'C');
}

namespace GuzzleHttp\Test {
    require __DIR__ . '/../vendor/autoload.php';
    require __DIR__ . '/Server.php';
    use GuzzleHttp\Tests\Server;

    Server::start();
    register_shutdown_function(function () {
        Server::stop();
    });
}

// 更多精品WP资源尽在喵容：miaoroom.com
namespace GuzzleHttp\Handler {
    function curl_setopt_array($handle, array $options)
    {
        if (!empty($_SERVER['curl_test'])) {
            $_SERVER['_curl'] = $options;
        } else {
            unset($_SERVER['_curl']);
        }
        return \curl_setopt_array($handle, $options);
    }

    function curl_multi_setopt($handle, $option, $value)
    {
        if (!empty($_SERVER['curl_test'])) {
            $_SERVER['_curl_multi'][$option] = $value;
        } else {
            unset($_SERVER['_curl_multi']);
        }
        return \curl_multi_setopt($handle, $option, $value);
    }
}
