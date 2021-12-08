<?php
/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title'       => 'Configuration Object',
    'description' => 'Transform any configuration plain array into a dynamic and configurable object structure, and pull apart configuration handling from the main logic of your script. Use provided services to add more functionality to your objects: cache, parents, persistence and much more.',
    'version'     => '2.0.0',
    'state'       => 'stable',
    'category'    => 'backend',

    'author'       => 'Romain Canon',
    'author_email' => 'romain.hydrocanon@gmail.com',

    'constraints' => [
        'depends'   => [
            'typo3' => '9.5.0-11.5.99',
            'php'   => '7.2.0-7.99.99'
        ],
        'conflicts' => [],
        'suggests'  => []
    ],

    'clearCacheOnLoad' => 1
];
