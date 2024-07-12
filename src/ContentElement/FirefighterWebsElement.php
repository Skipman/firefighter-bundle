<?php

/*
 * This file is part of Contao Firefighter Bundle.
 * 
 * (c) Ronald Boda 2022 <info@coboda.at>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/skipman/firefighter-bundle
 */

namespace Skipman\FirefighterBundle\ContentElement;

use Contao\ContentElement;
use Contao\Database;

class FirefighterWebsElement extends ContentElement
{
    protected $strTemplate = 'ce_webs';

    protected function compile()
    {
        $departmentId = $this->webDepartment;
        
        if ($departmentId) {
            $result = Database::getInstance()->prepare("SELECT ffname, website, facebook, instagram, youtube, twitter, tiktok FROM tl_cob_departments WHERE id = ?")
                                             ->execute($departmentId);

            if ($result->numRows > 0) {
                $this->Template->department = $result->row();
                $this->Template->departmentName = $result->ffname;
            }
        }
    }
}
