<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
return array(
    'solarium' => array(
        'endpoint' => array(
            'localhost' => array(
                'host' => '178.79.180.131',
                'port' => 8983,
                'path' => '/solr/dod',
            )
        )
    ),
    'mysql' => array(
        'log_queries' => false,
        'log_file' => '/home/users/deonoftp/data/logs/dberror.log',
        'host' => 'localhost',
        'port' => 3601,
        'user' => 'root',
        'password' => '',
        'db' => 'dod'
    )
);