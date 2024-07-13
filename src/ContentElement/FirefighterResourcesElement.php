<?php

namespace Skipman\FirefighterBundle\ContentElement;

use Contao\ContentElement;
use Contao\StringUtil;
use Contao\Database;

class FirefighterResourcesElement extends ContentElement
{
    protected $strTemplate = 'ce_resources';

    protected function compile()
    {
        $arrData = [];
        $firefighterDetails = StringUtil::deserialize($this->firefighterDetails);

        if (is_array($firefighterDetails)) {
            foreach ($firefighterDetails as $detail) {
                if (!empty($detail['ffname']) || !empty($detail['vehicles']) || !empty($detail['team'])) {
                    $departmentData = Database::getInstance()->prepare("SELECT * FROM tl_cob_departments WHERE id=?")->execute($detail['ffname'])->fetchAssoc();
                    if ($departmentData) {
                        $selectedVehicles = StringUtil::deserialize($detail['vehicles'], true);
                        $vehicles = [];
                        foreach (StringUtil::deserialize($departmentData['fleet'], true) as $fleet) {
                            if (in_array($fleet['vehicle'], $selectedVehicles)) {
                                $vehicleData = Database::getInstance()->prepare("SELECT vehicle_short FROM tl_cob_vehicles WHERE id=?")->execute($fleet['vehicle'])->fetchAssoc();
                                if ($vehicleData) {
                                    if ($fleet['link']) {
                                        $vehicles[] = sprintf('<a href="%s">%s</a>', $this->generateFrontendUrl($fleet['link']), $vehicleData['vehicle_short']);
                                    } elseif ($fleet['url']) {
                                        $vehicles[] = sprintf('<a href="%s">%s</a>', $fleet['url'], $vehicleData['vehicle_short']);
                                    } else {
                                        $vehicles[] = $vehicleData['vehicle_short'];
                                    }
                                }
                            }
                        }
                        $arrData[] = [
                            'ffname' => $departmentData['ffname'],
                            'vehicles' => implode(', ', $vehicles),
                            'team' => $detail['team']
                        ];
                    }
                }
            }
        }

        // Filter out empty otherOrganisation entries if the checkbox is checked
        $filteredOtherOrganisations = [];
        if ($this->addOtherOrganisationDetails) {
            $otherOrganisationDetails = StringUtil::deserialize($this->otherOrganisationDetails);
            if (is_array($otherOrganisationDetails)) {
                foreach ($otherOrganisationDetails as $organisation) {
                    if (!empty($organisation['otherOrganisationName']) || !empty($organisation['otherOrganisationVehicles']) || !empty($organisation['team'])) {
                        $filteredOtherOrganisations[] = $organisation;
                    }
                }
            }
        }

        $this->Template->departments = $arrData;
        $this->Template->otherOrganisations = $filteredOtherOrganisations;
    }
}
