<?php declare(strict_types=1);

/*
 * This file is part of Contao Firefighter Bundle.
 * 
 * (c) Ronald Boda 2022 <info@coboda.at>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/skipman/contao-firefighter-bundle
 */

 namespace Skipman\ContaoFirefighterBundle\Models;

 use Contao\Model;

/**
 * Reads and writes firefighter items.
 */
class FirefighterCategoryModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_firefighter_category';
}
