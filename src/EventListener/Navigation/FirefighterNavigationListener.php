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

namespace Skipman\ContaoFirefighterBundle\EventListener\Navigation;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageModel;
use Skipman\ContaoFirefighterBundle\Models\FirefighterArchiveModel;
use Skipman\ContaoFirefighterBundle\Models\FirefighterModel;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;
use Terminal42\ChangeLanguage\EventListener\Navigation\AbstractNavigationListener;

/**
 * @Hook("changelanguageNavigation")
 */
class FirefighterNavigationListener extends AbstractNavigationListener
{
    protected function getUrlKey(): string
    {
        return 'auto_item';
    }

    protected function findCurrent(): ?FirefighterModel
    {
        $alias = $this->getAutoItem();

        if ('' === $alias) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (null === ($archives = FirefighterArchiveModel::findBy('jumpTo', $objPage->id))) {
            return null;
        }

        // Fix Contao bug that returns a collection (see contao-changelanguage#71)
        $options = ['limit' => 1, 'return' => 'Model'];

        return FirefighterModel::findPublishedByParentAndIdOrAlias($alias, $archives->fetchEach('id'), $options);
    }

    protected function findPublishedBy(array $columns, array $values = [], array $options = []): ?FirefighterModel
    {
        return FirefighterModel::findOneBy(
            $this->addPublishedConditions($columns, FirefighterModel::getTable()),
            $values,
            $options
        );
    }
}
