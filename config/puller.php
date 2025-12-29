<?php
return [
    'default' => env('PULLER_CONNECTION', 'database'),
    'route'=>[
        'path'=>'/puller/messages',
        'middleware'=>[]
    ],
    /**
     * Sleep time in seconds each loop to check for new messages
     */
    'sleep'=>0.1,
    /**
     * Maximum execution time for puller request in seconds
     */
    'time_limit'=>3600,
    /**
     * Time in seconds between keepalive signals
     */
    'keepalive_time'=>10,
    'connections' => [
        'database' => [
            'driver' => 'database',
            'connection' => null,
            'table' => 'puller_messages',
            'remove_after' => 90,
            'fetch_size' => 10,

        ],
        /*'redis' => [
            'driver' => 'redis',
            'connection' => 'puller',
            'table' => 'puller_messages',
            'remove_after' => 90,

        ],*/
    ],

];
