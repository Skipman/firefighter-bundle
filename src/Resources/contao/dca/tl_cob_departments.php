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

use Contao\DC_Table;
use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;

$GLOBALS['TL_DCA']['tl_cob_departments'] = [
    'config' => [
        'dataContainer' => DC_Table::class,        
        'enableVersioning' => true,
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['ffname'],
            'flag' => 1,
            'length' => 3,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['ffnumber', 'ffname'],
            'format' => '%s (%s)',
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ]
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg'
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'{{conf}}\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg'
            ]
        ]
    ],
    'palettes' => [
    'default' =>  '{department_legend},ffnumber,ffname,bfk,afk;'
                    .'{social_legend:hide},website,facebook,instagram,youtube,twitter,tiktok;'
                    .'{fleet_legend:hide},fleet'
],

    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0]
        ],
        'ffnumber' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['ffnumber'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 10, 'rgxp' => 'alnum', 'unique' => true, 'sorting' => true, 'search' => true, 'tl_class' => 'w25'],
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'ffname' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['ffname'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255,  'sorting' => true, 'search' => true, 'flag' => 3, 'lenght' => 3, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'bfk' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['bfk'],
            'inputType' => 'select',
            'options' => ['Amstetten', 'Baden', 'Bruck an der Leitha', 'Gänserndorf', 'Gmünd', 'Hollabrunn', 'Horn', 'Korneuburg', 'Krems', 'Lilienfeld', 'Melk', 'Mistelbach', 'Mödling', 'Neunkirchen', 'Scheibbs', 'St. Pölten', 'Tulln', 'Waidhofen an der Thaya', 'Wiener Neustadt', 'Zwettl'],
            'eval' => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w25'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'afk' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['afk'],
            'inputType' => 'select',
            'options' => ['St. Pölten-Stadt', 'St. Pölten-Ost', 'St. Pölten-West'],
            'eval' => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w25'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'website' => [
          'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['website'],
          'inputType' => 'text',
          'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
          'sql' => "varchar(255) NOT NULL default ''"
        ],
        'facebook' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['facebook'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'instagram' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['instagram'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'youtube' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['youtube'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'twitter' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['twitter'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'tiktok' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['tiktok'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'fleet' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['vehicles'],
            'inputType' => 'multiColumnWizard',
            'eval' => [                
                'columnFields' => [
                    'vehicle' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['vehicle'],
                        'inputType' => 'select',
                        'options_callback' => ['tl_cob_departments', 'getVehicles'],
                        'eval' => ['style' => 'width:180px', 'chosen' => true, 'includeBlankOption' => true,],
                    ],
                    'link' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['link'],
                        'inputType' => 'pageTree',
                        'eval' => ['fieldType' => 'radio']
                    ],
                    'url' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_cob_departments']['url'],
                        'inputType' => 'text',
                        'eval' => ['rgxp' => 'url', 'style' => 'width:400px'],
                    ],
                ],
            ],
            'sql' => 'blob NULL'
        ]
        
    ]
];

class tl_cob_departments extends Backend
{
    public function getVehicles()
    {
        $vehicles = [];
        $result = Database::getInstance()->execute("SELECT id, vehicle_short FROM tl_cob_vehicles ORDER BY vehicle_short ASC");

        while ($result->next()) {
            $vehicles[$result->id] = $result->vehicle_short;
        }

        return $vehicles;
    }
}
