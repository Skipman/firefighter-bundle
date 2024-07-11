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

namespace Skipman\FirefighterBundle\Helper;

use Contao\Database;

class FirefighterHelper
{
    public static function getDepartments(): array
    {
        $departments = [];
        $result = Database::getInstance()->execute("SELECT id, ffname FROM tl_cob_departments ORDER BY ffname ASC");

        while ($result->next()) {
            $departments[$result->id] = $result->ffname;
        }

        return $departments;
    }
}
