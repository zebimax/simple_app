<?php
return array(
    'default_language' => 'dutch',
    'solrVariantRetest' => 0,
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
        'linkMaker' => 'Plugins\Controller\LinkMaker',
        'cliColors' => 'Plugins\Controller\Colors\Colors'
    ),
    'solarium.client' => function(array $params = array()) {
        $config = Macaw::getConfig('solarium');
        if (
            isset($config['endpoint']['localhost']['path']) &&
            isset($params['lang_code'])
        ) {
            $config['endpoint']['localhost']['path'] .= '-' . $params['lang_code'];
        }
        return new Solarium\Core\Client\Client($config);
    },
    'default_title_category' => 'Drogisterij',
    'search_sort_options' => array(
        'default' => array(
            array('value' => '', 'name' => 'Sorteer op:'),
            array('value' => 'a', 'name' => 'Laagste prijs'),
            array('value' => 'b', 'name' => 'Hoogste prijs'),
            array('value' => 'c', 'name' => 'Naam'),
            array('value' => 'd', 'name' => 'Populariteit')
        ),
        'specials' => array(
            array('value' => '', 'name' => 'Sorteer op:'),
            array('value' => 'a', 'name' => 'Laagste prijs'),
            array('value' => 'b', 'name' => 'Hoogste prijs'),
            array('value' => 'c', 'name' => 'Naam A-Z'),
            array('value' => 'f', 'name' => 'Naam Z-A'),
            array('value' => 'd', 'name' => 'Populariteit'),
            array('value' => 'e', 'name' => 'Aanbieding'),
        )
    ),
    'search_filter_options' => array(
        'brands' => array(
            'head' => array(
                'class' => 'toggle-options',
                'open_class' => '',
                'title' => 'FILTER OP MERK',
                'selected_part_1' => true,
                'onclick_selected' => 'removeBrandSpecail',
                'onclick_selected_all' => 'closeAllBrandsSpecail',
                'selected' => array()
            ),
            'options' => array(
                'id_name' => 'brands',
                'box_class' => 'options',
                'onclick' => 'checkBrandSpecail',
                'onclick_text' => 'checkBrandSpecailText',
            )
        ),
        'discounts' => array(
            'head' => array(
                'class' => 'toggle-korting',
                'open_class' => '',
                'title' => 'Kortingfilter',
                'selected_part_1' => false,
                'onclick_selected_all' => 'closeAllKorts',
            ),
            'options' => array(
                'id_name' => 'korts',
                'box_class' => 'korting',
                'onclick' => 'checkKorts',
                'onclick_text' => 'checkKortsByText',
                'options' => array(
                    1 => array('id' => 1, 'name' => '0 - 10%'),
                    2 => array('id' => 2, 'name' => '10 - 30%'),
                    3 => array('id' => 3, 'name' => '30 - 50%'),
                    4 => array('id' => 4, 'name' => 'meer dan 50%'),
                )
            )
        )
    ),
    'assembla_api_url' => 'https://api.assembla.com/v1/'
);