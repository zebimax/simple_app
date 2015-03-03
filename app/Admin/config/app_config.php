<?php
return array(
    'plugins' => array(
        'paginationLinks' => 'Plugins\View\PaginationLinks',
        'defaultValue' => 'Plugins\Controller\DefaultValue',
        'paginationLinksMaker' => 'Plugins\Controller\PaginationLinksMaker',
        'image' => array(
            'initializer' => function () {
                return new \Plugins\View\Image(
                    new \String\Output\Html\Printer()
                );
            }
        ),
    ),
);