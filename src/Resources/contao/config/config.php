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

 use Skipman\FirefighterBundle\ContentElement\FirefighterResourcesElement;
 use Skipman\FirefighterBundle\ContentElement\FirefighterMembersElement;
 use Skipman\FirefighterBundle\ContentElement\FirefighterWebsElement;
 
// Backend-Module registrieren

$GLOBALS['BE_MOD']['firefighter'] = [
  'departments' => ['tables' => ['tl_cob_departments']],
  'functions'   => ['tables' => ['tl_cob_functions']],
  'ranks'       => ['tables' => ['tl_cob_ranks']],
  'vehicles'    => ['tables' => ['tl_cob_vehicles']]
];

$GLOBALS['TL_CTE']['texts']['ff_resources'] = FirefighterResourcesElement::class;
$GLOBALS['TL_CTE']['texts']['members'] = FirefighterMembersElement::class;
$GLOBALS['TL_CTE']['texts']['webs'] = FirefighterWebsElement::class;

$GLOBALS['TL_LANG']['CTE']['ff_resources'] = ['Einsatzresourcen', 'Eingesetzte Resourcen verwalten'];
$GLOBALS['TL_LANG']['CTE']['members'] = ['FF-Mitglieder', 'Mitglieder anzeigen'];
$GLOBALS['TL_LANG']['CTE']['webs'] = ['FF-Webs', 'Webseiten und Social Media Links anzeigen'];
