<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'whitelisting';
$app['version'] = '2.0.0';
$app['release'] = '2';
$app['vendor'] = 'RedPiranha';
$app['packager'] = 'RedPiranha';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('whitelisting_app_description');
$app['tooltip'] = lang('whitelisting_app_tooltip');
$app['delete_dependency'] = array('app-whitelisting-core','app-whitelisting',
);

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('whitelisting_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('whitelisting_app_name');
$app['menu_enabled'] = true;
