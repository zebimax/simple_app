<?php
return array(
    'translate_base_language_id' => 4,
    'translate_language_id' => 5,
    'plugins' => array(
        'calculateCustomPrice' => 'Plugins\CalculateCustomPrice',
        'paginationLinks' => 'Plugins\View\PaginationLinks',
        'headerNavigation' => 'Plugins\View\HeaderNavigation',
        'breadcrumb' => 'Plugins\View\Breadcrumb',
        'defaultValue' => 'Plugins\Controller\DefaultValue',
        'conditionTemplate' => 'Plugins\View\ConditionTemplate',
        'brandsArrayLinks' => 'Plugins\View\BrandsArrayLinks',
        'discountsArrayLinks' => 'Plugins\View\DiscountsArrayLinks',
        'brandOptions' => 'Plugins\View\BrandOptions',
        'stringByCondition' => 'Plugins\View\StringByCondition',
        'discountsOptions' => 'Plugins\View\DiscountsOptions',
        'skinRenderer' => 'Plugins\View\SkinRenderer',
        'searchSortOptions' => 'Plugins\View\SearchSortOptions',
        'paginationLinksMaker' => 'Plugins\Controller\PaginationLinksMaker',
    ),
    'mysql_log_editor' => array(
        'text_field' => 'message',
        'user_field' => 'user',
        'date_field' => 'date',
        'unique_id_field' => 'unique_id',
        'label_field' => 'label',
        'additional_field' => 'additional'
    )
);