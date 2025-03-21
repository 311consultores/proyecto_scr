<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => "",
        ],
        'img' => [
            'driver' => 'local',
            'root' => storage_path('img'),
            'url' => env('APP_URL').'/storage/img',
            'visibility' => 'public',
        ],
        'temps' => [
            'driver' => 'local',
            'root' => storage_path('img_temps'),
            'url' => env('APP_URL').'/storage/img_temps',
            'visibility' => 'public',
        ],
        'gruas' => [
            'driver' => 'local',
            'root' => storage_path('img_gruas'),
            'url' => env('APP_URL').'/storage/img_gruas',
            'visibility' => 'public',
        ],
        'reportes' => [
            'driver' => 'local',
            'root' => base_path('public/storage/img_reportes'),
            'url' => env('APP_URL').'/storage/img_reportes',
            'visibility' => 'public',
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
        
    ],

];