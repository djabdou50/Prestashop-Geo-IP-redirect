<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit189261cd5b6b8031f72bbf19863d7229
{
    public static $files = array (
        'a7cee959d5f14eb7484e7f8e7182e03d' => __DIR__ . '/..' . '/geoip/geoip/src/geoip.inc',
        '8cf74b4cf02ad0591c257dcfb7edbc8d' => __DIR__ . '/..' . '/geoip/geoip/src/geoipcity.inc',
        'd114bd5194e69687495c9150ff6be780' => __DIR__ . '/..' . '/geoip/geoip/src/timezone.php',
    );

    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MaxMind\\WebService\\' => 19,
            'MaxMind\\Exception\\' => 18,
            'MaxMind\\Db\\' => 11,
        ),
        'G' => 
        array (
            'GeoIp2\\' => 7,
        ),
        'C' => 
        array (
            'Composer\\CaBundle\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MaxMind\\WebService\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/WebService',
        ),
        'MaxMind\\Exception\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind/web-service-common/src/Exception',
        ),
        'MaxMind\\Db\\' => 
        array (
            0 => __DIR__ . '/..' . '/maxmind-db/reader/src/MaxMind/Db',
        ),
        'GeoIp2\\' => 
        array (
            0 => __DIR__ . '/..' . '/geoip2/geoip2/src',
        ),
        'Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit189261cd5b6b8031f72bbf19863d7229::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit189261cd5b6b8031f72bbf19863d7229::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}