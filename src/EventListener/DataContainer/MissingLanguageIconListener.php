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

 namespace Skipman\ContaoFirefighterBundle\EventListener\DataContainer;

 use Skipman\ContaoFirefighterBundle\Models\FirefighterArchiveModel;
 use Skipman\ContaoFirefighterBundle\Models\FirefighterModel;
 use Terminal42\ChangeLanguage\Helper\LabelCallback;

class MissingLanguageIconListener
{
    private static array $callbacks = [
        'tl_firefighter' => 'onFirefighterChildRecords',
    ];

    /**
     * Override core labels to show missing language information.
     */
    public function register(string $table): void
    {
        if (\array_key_exists($table, self::$callbacks)) {
            LabelCallback::createAndRegister(
                $table,
                fn (array $args, $previousResult) => $this->{self::$callbacks[$table]}($args, $previousResult)
            );
        }
    }

    /**
     * Generate missing translation warning for child records.
     */
    public function onFirefighterChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        $archive = FirefighterArchiveModel::findByPk($row['pid']);

        if (
            null !== $archive
            && $archive->master
            && (!$row['languageMain'] || null === FirefighterModel::findByPk($row['languageMain']))
        ) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    private function generateLabelWithWarning(string $label, string $imgStyle = ''): string
    {
        return $label.sprintf(
            '<span style="padding-left:3px"><img src="%s" alt="%s" title="%s" style="%s"></span>',
            'bundles/terminal42changelanguage/language-warning.png',
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $imgStyle
        );
    }
}
