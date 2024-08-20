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

 use Contao\CoreBundle\DataContainer\PaletteManipulator;

 // Extend the default palettes
 PaletteManipulator::create()
     ->addLegend('firefighter_legend', 'amg_legend', PaletteManipulator::POSITION_BEFORE)
     ->addField(['firefighter', 'firefighterp'], 'firefighter_legend', PaletteManipulator::POSITION_APPEND)
     ->applyToPalette('extend', 'tl_user')
     ->applyToPalette('custom', 'tl_user')
 ;
 
 // Add fields to tl_user
 $GLOBALS['TL_DCA']['tl_user']['fields']['firefighter'] = [
     'exclude' => true,
     'inputType' => 'checkbox',
     'foreignKey' => 'tl_firefighter_archive.title',
     'eval' => ['multiple' => true],
     'sql' => 'blob NULL',
 ];
 
 $GLOBALS['TL_DCA']['tl_user']['fields']['firefighterp'] = [
     'exclude' => true,
     'inputType' => 'checkbox',
     'options' => ['create', 'delete'],
     'reference' => &$GLOBALS['TL_LANG']['MSC'],
     'eval' => ['multiple' => true],
     'sql' => 'blob NULL',
 ];
 