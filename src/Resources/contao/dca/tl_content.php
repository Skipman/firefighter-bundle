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

use Contao\Backend;
use Contao\Database;
use Contao\StringUtil;



$GLOBALS['TL_DCA']['tl_content']['palettes']['members'] =
    '{type_legend},type;'
    . '{ffMemberName_legend},membersFirstname,membersLastname,membersRank,membersRankHonory;'
    . '{ffMemberFunction_legend},membersFunctionLocal,membersFunctionSection;'
    . '{ffMemberContact_legend:hide},membersHomebase,membersEmail,membersPhone;'
    . '{image_legend:hide},addMembersImage;'
    . '{template_legend:hide},customTpl;'
    . '{protected_legend:hide},protected;'
    . '{expert_legend:hide},guests,cssID,space;'
    . '{invisible_legend:hide},invisible,start,stop';

// Registrieren des Subpalette-Selector-Feldes
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'addMembersImage';

// Definieren der Subpalette
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['addMembersImage'] = 'singleSRC,alt,size,imagemargin,imageUrl,fullsize,caption,floating';

// Add the label callback to the existing array
$GLOBALS['TL_DCA']['tl_content']['list']['label']['label_callback'] = ['Skipman\FirefighterBundle\ContentElement\FirefighterMembersElement', 'addMembersElementLabel'];

// Add ContentElement FF-Webs
$GLOBALS['TL_DCA']['tl_content']['palettes']['webs'] = '{type_legend},type;{website_legend},webDepartment;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

// Add ContentElement FF_Resources
$GLOBALS['TL_DCA']['tl_content']['palettes']['ff_resources'] = '{type_legend},type;{firefighter_legend},firefighterDetails;{otherOrganisation_legend},otherOrganisationDetails;{protected_legend:hide},protected;{expert_legend:hide},guests,invisible,start,stop';

/*
 * Add fields for content-element members
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['membersFirstname'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'tl_class' => 'w25',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['membersLastname'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'tl_class' => 'w25',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['membersRank'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'select',
    'foreignKey' => 'tl_cob_ranks.rank_short',
    'options_callback'  => ['members_element', 'getRankShortOptions'],
    'eval' => [
        'maxlength' => 255,
        'chosen' => true,
        'includeBlankOption' => true,
        'tl_class' => 'w25',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['membersRankHonory'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'checkbox',
    'eval' => [
        'isBoolean'=> true, 'tl_class' => 'm12 w25'
    ],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['membersFunctionLocal'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'select',
    'options_callback' => ['members_element', 'getFunctionLocalShortOptions'],
    'eval' => [
        'maxlength' => 255,
        'chosen' => true,
        'includeBlankOption' => true,
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['membersFunctionSection'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'select',
    'options_callback' => ['members_element', 'getFunctionSectionShortOptions'],
    'eval' => [
        'maxlength' => 255,
        'chosen' => true,
        'includeBlankOption' => true,
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['membersHomebase'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'select',
    'options_callback' => ['Skipman\FirefighterBundle\Helper\FirefighterHelper', 'getDepartments'],
    'eval' => [
        'maxlength' => 255,
        'chosen' => true,
        'includeBlankOption' => true,
        'tl_class' => 'w33',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['membersEmail'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'rgxp' => 'email',
        'decodeEntities' => true,
        'tl_class' => 'w33',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['membersPhone'] = [
    'exclude' => true,
    'search' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 255,
        'tl_class' => 'w33',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['addMembersImage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['addMembersImage'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => [
        'submitOnChange' => true,
    ],
    'sql' => "char(1) NOT NULL default ''",
];

/*
 * Add fields for content-element ff-webs
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['webDepartment'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['Skipman\FirefighterBundle\Helper\FirefighterHelper', 'getDepartments'],
    'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default 0"
];



/*
 * Add fields for content-element resources
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['firefighterDetails'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['firefighterDetails'],
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'eval' => [
        'columnFields' => [
            'ffname' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['ffname'],
                'inputType' => 'select',
                'options_callback' => ['Skipman\FirefighterBundle\Helper\FirefighterHelper', 'getDepartments'],
                'eval' => ['style' => 'width:200px', 'includeBlankOption' => true, 'chosen' => true, 'submitOnChange' => true]
            ],
            'vehicles' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['vehicles'],
                'inputType' => 'checkboxWizard',
                'options_callback' => ['tl_content_firefighter', 'getVehiclesByDepartment'],
                'eval' => ['multiple' => true, 'style' => 'width:200px float:left']
            ],
            'team' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['team'],
                'inputType' => 'text',
                'eval' => ['rgxp' => 'digit', 'maxlength' => 2, 'style' => 'width:80px']
            ],
        ],
        'tl_class' => 'clr'
    ],
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['otherOrganisationDetails'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['otherOrganisationDetails'],
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'eval' => [
        'columnFields' => [
            'otherOrganisationName' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['otherOrganisationName'],
                'inputType' => 'text',
                'eval' => ['maxlenght' => 255,'style' => 'width:300px']
            ],
            'otherOrganisationVehicles' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['otherOrganisationVehicles'],
                'inputType' => 'text',
                'eval' => ['maxlenght' => 255,'style' => 'width:300px']
            ],
            'team' => [
                'label' => &$GLOBALS['TL_LANG']['tl_content']['otherOrganisationTeam'],
                'inputType' => 'text',
                'eval' => ['maxlenght' => 255,'style' => 'width:100px']
            ],
        ],
        'tl_class' => 'clr'
    ],
    'sql' => "blob NULL"
];


class members_element extends Contao\Backend {
    public function getRankShortOptions(Contao\DataContainer $dc)
    {
        $options = [];
        $result = Contao\Database::getInstance()->prepare("SELECT id, rank_short FROM tl_cob_ranks ORDER BY rank_short ASC")->execute();

        while ($result->next()) {
            $options[$result->id] = $result->rank_short;
        }

        return $options;
    }

    public function getFunctionLocalShortOptions(Contao\DataContainer $dc)
    {
        $options = [];
        $result = Contao\Database::getInstance()->prepare("SELECT id, function_short FROM tl_cob_functions WHERE function_overlocal = 0 ORDER BY function_short ASC")->execute();

        while ($result->next()) {
            $options[$result->id] = $result->function_short;
        }

        return $options;
    }

    public function getFunctionSectionShortOptions(Contao\DataContainer $dc)
    {
        $options = [];
        $result = Contao\Database::getInstance()->prepare("SELECT id, function_short FROM tl_cob_functions WHERE function_overlocal = 1 ORDER BY function_short ASC")->execute();

        while ($result->next()) {
            $options[$result->id] = $result->function_short;
        }

        return $options;
    }
}

class tl_content_firefighter extends Backend
{

    public function getVehiclesByDepartment($dc)
    {
        $vehicles = [];

        if ($dc instanceof \MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard) {
            // Get the active row index
            $activeRowIndex = $dc->activeRow;
            // Get the value of the active row
            $activeRowValue = $dc->value[$activeRowIndex];

            if (isset($activeRowValue['ffname']) && $activeRowValue['ffname'] != '') {
                $ffnameId = $activeRowValue['ffname'];
            } else {
                return $vehicles;
            }
        } else {
            return $vehicles;
        }

        if ($ffnameId) {
            $result = Database::getInstance()->prepare("SELECT fleet FROM tl_cob_departments WHERE id=?")
                                            ->execute($ffnameId);

            if ($result->numRows) {
                $fleetData = StringUtil::deserialize($result->fleet, true);

                foreach ($fleetData as $fleet) {
                    if (isset($fleet['vehicle'])) {
                        $vehicleData = Database::getInstance()->prepare("SELECT vehicle_short FROM tl_cob_vehicles WHERE id=?")
                                                ->execute($fleet['vehicle']);
                        if ($vehicleData->numRows) {
                            $vehicleRow = $vehicleData->fetchAssoc();
                            $vehicles[$fleet['vehicle']] = $vehicleRow['vehicle_short'];
                        }
                    }
                }
            }
        }

        return $vehicles;
    }
}