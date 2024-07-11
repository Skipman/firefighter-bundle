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

namespace Skipman\FirefighterBundle\ContentElement;

use Contao\Backend;
use Contao\Database;
use Contao\StringUtil;
use Contao\DataContainer;

class tl_content extends Backend
{
    public function getDepartments()
    {
        $departments = [];
        $result = Database::getInstance()->execute("SELECT id, ffname FROM tl_cob_departments ORDER BY ffname ASC");

        while ($result->next()) {
            $departments[$result->id] = $result->ffname;
        }

        return $departments;
    }

    public function getVehiclesByDepartment(DataContainer $dc)
    {
        $vehicles = [];
        if ($dc->activeRecord->department) {
            $result = Database::getInstance()->prepare("SELECT fleet FROM tl_cob_departments WHERE id=?")
                                             ->execute($dc->activeRecord->department);
            $fleetData = StringUtil::deserialize($result->fleet, true);

            foreach ($fleetData as $fleet) {
                $vehicles[$fleet['vehicle']] = $fleet['vehicle'];
            }
        }

        return $vehicles;
    }
}
