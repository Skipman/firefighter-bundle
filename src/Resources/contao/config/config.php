<?php

/*
 * This file is part of Contao Firefighter Bundle.
 * 
 * (c) Ronald Boda 2022 <info@coboda.at>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/skipman/contao-firefighter-bundle
 */

//use Skipman\ContaoFirefighterBundle\ContentElement\ContaoFirefighterMembersElement;
use Skipman\ContaoFirefighterBundle\ContentElement\ContaoFirefighterResourcesElement;
use Skipman\ContaoFirefighterBundle\ContentElement\ContaoFirefighterWebsElement;
use Skipman\ContaoFirefighterBundle\Classes\Firefighter;
use Skipman\ContaoFirefighterBundle\Models\FirefighterModel;
use Skipman\ContaoFirefighterBundle\Modules\ModuleFirefighterList;
use Skipman\ContaoFirefighterBundle\Models\FirefighterArchiveModel;
use Skipman\ContaoFirefighterBundle\Models\FirefighterCategoryModel;
use Skipman\ContaoFirefighterBundle\Modules\ModuleFirefighterReader;

// Register the backend template
$GLOBALS['TL_BE']['default'] = 'backend/be_main';

// Register Backend-Modules
$GLOBALS['BE_MOD']['content']['firefighter'] = [
    'tables' => ['tl_firefighter_archive', 'tl_firefighter', 'tl_firefighter_category', 'tl_content'],
    'icon' => 'bundles/contaofirefighter/flame.svg'
];
$GLOBALS['BE_MOD']['firefighter_settings'] = [
    'departments' => ['tables' => ['tl_firefighter_departments']],
    'functions'   => ['tables' => ['tl_firefighter_functions']],
    'ranks'       => ['tables' => ['tl_firefighter_ranks']],
    'vehicles'    => ['tables' => ['tl_firefighter_vehicles']],
];

// Register Frontend-Modules
$GLOBALS['FE_MOD']['firefighter'] = [
    'firefighterlist' => ModuleFirefighterList::class,
    'firefighterreader' => ModuleFirefighterReader::class,
];

$GLOBALS['TL_MODELS']['tl_firefighter'] = FirefighterModel::class;
$GLOBALS['TL_MODELS']['tl_firefighter_archive'] = FirefighterArchiveModel::class;
$GLOBALS['TL_MODELS']['tl_firefighter_category'] = FirefighterCategoryModel::class;

// Register Content-Elements
$GLOBALS['TL_CTE']['texts']['ff_resources'] = ContaoFirefighterResourcesElement::class;
$GLOBALS['TL_CTE']['texts']['webs']         = ContaoFirefighterWebsElement::class;

$GLOBALS['TL_LANG']['CTE']['ff_resources']  = ['FF-Einsatzressourcen', 'Eingesetzte Ressourcen verwalten'];
$GLOBALS['TL_LANG']['CTE']['webs']          = ['FF-Webs', 'Webseiten und Social Media Links anzeigen'];

/*
 * Register hooks
 */
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = [Firefighter::class, 'getSearchablePages'];

/*
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'firefighter';
$GLOBALS['TL_PERMISSIONS'][] = 'firefighterp';