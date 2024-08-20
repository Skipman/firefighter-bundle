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

namespace Skipman\ContaoFirefighterBundle\Modules;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Environment;
use Contao\Input;
use Contao\Model\Collection;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\System;
use Contao\FrontendTemplate;
use Contao\ContentModel;
use Contao\FilesModel;
use Contao\Database;
use Skipman\ContaoFirefighterBundle\Models\FirefighterCategoryModel;
use Skipman\ContaoFirefighterBundle\Models\FirefighterModel;
use Skipman\ContaoFirefighterBundle\Classes\Firefighter;

/**
 * Class ModuleFirefighterList.
 *
 * Front end module "firefighter list".
 */
class ModuleFirefighterList extends ModuleFirefighter
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_firefighterlist';

    /**
     * Display a wildcard in the back end.
     */
    public function generate(): string
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FMD']['firefighterlist'][0].' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = StringUtil::specialcharsUrl(System::getContainer()->get('router')->generate('contao_backend', ['do' => 'themes', 'table' => 'tl_module', 'act' => 'edit', 'id' => $this->id]));
            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     *
     * @throws \Exception
     */
    protected function compile(): void
    {
        // Add the "reset categories" link
        if ($this->firefighter_filter_reset) {
            $this->Template->firefighter_filter_reset = $GLOBALS['TL_LANG']['MSC']['filter_reset'];
        }

        // Get the selected categories for filtering
        $selectedCategories = StringUtil::deserialize($this->filter_firefightercategories, true);

        // Fetch categories based on selection
        $objCategories = null;
        if (!empty($selectedCategories)) {
            // Fetch categories based on the selected categories
            $objCategories = FirefighterCategoryModel::findMultipleByIds($selectedCategories, [
                'order' => 'sorting ASC',
            ]);
        }

        $simplifiedCategories = [];
        if ($objCategories !== null) {
            while ($objCategories->next()) {
                if ($objCategories->alias !== null && $objCategories->simplifiedTitle !== null) {
                    $simplifiedCategories[] = [
                        'alias' => $objCategories->alias,
                        'title' => $objCategories->simplifiedTitle
                    ];
                }
            }
        }

        $this->Template->firefightercategories = $simplifiedCategories;

        // Check if group filter should be displayed
        $showGroupFilter = $this->firefighter_filter !== 0;

        // Pass the flag to the template
        $this->Template->showGroupFilter = $showGroupFilter;

        $limit = null;
        $offset = (int) $this->skipFirst;

        // Maximum number of items
        if ($this->numberOfItems > 0) {
            $limit = $this->numberOfItems;
        }

        // Handle featured firefighter-items
        if ('featured' === $this->firefighter_featured) {
            $blnFeatured = true;
        } elseif ('unfeatured' === $this->firefighter_featured) {
            $blnFeatured = false;
        } else {
            $blnFeatured = null;
        }

        $arrColumns = ['tl_firefighter.published=?'];
        $arrValues = ['1'];
        $arrOptions = [
            'order' => 'tl_firefighter.membersLastname ASC, tl_firefighter.membersFirstname ASC',
        ];

        if (!$this->filter_firefightercategories && !empty($limit)) {
            $arrOptions['limit'] = $limit;
        }

        // Handle featured/unfeatured items
        if ('featured' === $this->firefighter_featured || 'unfeatured' === $this->firefighter_featured) {
            $arrColumns[] = 'tl_firefighter.featured=?';
            $arrValues[] = 'featured' === $this->firefighter_featured ? '1' : '';
        }

        $arrPids = StringUtil::deserialize($this->firefighter_archives);
        $arrColumns[] = 'tl_firefighter.pid IN('.implode(',', array_map('\intval', $arrPids)).')';

        $arrFirefighterCategoryIds = [];

        // Pre-filter items based on filter_firefightercategories
        if ($this->filter_firefightercategories) {
            $arrFirefighterCategoryIds = $selectedCategories;
        }

        // Add firefighter pagination
        // Get the total number of items
        $intTotal = $this->countItems($arrPids, $blnFeatured, $arrFirefighterCategoryIds);

        if ($intTotal < 1) {
            return;
        }

        $total = $intTotal - $offset;

        // Split the results
        if ($this->perPage > 0 && (!isset($limit) || $this->numberOfItems > $this->perPage)) {
            // Adjust the overall limit
            if (isset($limit)) {
                $total = min($limit, $total);
            }

            // Get the current page
            $id = 'page_n'.$this->id;
            $page = Input::get($id) ?? 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $this->perPage), 1)) {
                throw new PageNotFoundException('Page not found: '.Environment::get('uri'));
            }

            // Set limit and offset
            $limit = (int) $this->perPage;
            $offset += (max($page, 1) - 1) * $this->perPage;
            $skip = (int) $this->skipFirst;

            // Overall limit
            if ($offset + $limit > $total + $skip) {
                $limit = $total + $skip - $offset;
            }

            // Add the pagination menu
            $objPagination = new Pagination($total, $this->perPage, Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        $objItems = $this->fetchItems($arrPids, $blnFeatured, ($limit ?: 0), $offset, $arrFirefighterCategoryIds);

        if (null !== $objItems) {
            $this->Template->items = $this->parseItems($objItems, false);
        }
    }

    /**
     * Count the total matching items.
     *
     * @param array $firefighterArchives
     * @param bool $blnFeatured
     * @param array $arrFirefighterCategories
     *
     * @return int
     */
    protected function countItems($firefighterArchives, $blnFeatured, $arrFirefighterCategories): int
    {
        return FirefighterModel::countPublishedByPids($firefighterArchives, $blnFeatured, $arrFirefighterCategories);
    }

    /**
     * Fetch the matching items.
     *
     * @param array $firefighterArchives
     * @param bool $blnFeatured
     * @param int $limit
     * @param int $offset
     * @param array $arrFirefighterCategories
     *
     * @return Collection|array<FirefighterModel>|FirefighterModel|null
     */
  
    protected function fetchItems($firefighterArchives, $blnFeatured, $limit, $offset, $arrFirefighterCategories)
    {
        // Fetch items without ordering by function
        $items = FirefighterModel::findPublishedByPids($firefighterArchives, $blnFeatured, $limit, $offset, [], $arrFirefighterCategories);

        if (null === $items) {
            return null;
        }

        $arrItems = $items->getModels();

        // Sort the items manually based on membersFunctionLocalWizard
        
        usort($arrItems, function ($a, $b) {
            // order by ID of membersFunctionLocal (KDT. 1. KDTSTV, 2. KDTSTV, LDV)
            $order = ['3' => 1, '4' => 2, '5' => 3, '83' => 4];
            $aFunction = $this->getHighestPriorityFunction($a->membersFunctionLocalWizard);
            $bFunction = $this->getHighestPriorityFunction($b->membersFunctionLocalWizard);

            $aOrder = $order[$aFunction] ?? 5;
            $bOrder = $order[$bFunction] ?? 5;

            if ($aOrder === $bOrder) {
                return strcmp($a->membersLastname, $b->membersLastname) ?: strcmp($a->membersFirstname, $b->membersFirstname);
            }

            return $aOrder < $bOrder ? -1 : 1;
        });

        return new Collection($arrItems, FirefighterModel::getTable());
    }

    /**
     * Get the highest priority local function from the MultiColumnWizard field
     *
     * @param mixed $membersFunctionLocalWizard
     * @return string|null
     */
    
    protected function getHighestPriorityFunction($membersFunctionLocalWizard): ?string
    {
        $functions = StringUtil::deserialize($membersFunctionLocalWizard, true);
        // order by ID of membersFunctionLocal (KDT, 1. KDTSTV, 2. KDTSTV, LDV)
        $priorityOrder = ['3', '4', '5', '83'];

        foreach ($priorityOrder as $priorityFunction) {
            foreach ($functions as $function) {
                if ($function['membersFunctionLocal'] === $priorityFunction) {
                    return $priorityFunction;
                }
            }
        }

        return null;
    }
    

    /**
     * Parse the items and return them as array.
     *
     * @param Collection|FirefighterModel[] $objItems
     * @param bool $blnAddArchive
     *
     * @return array
     */
    protected function parseItems($objItems, $blnAddArchive = false): array
    {
        $arrItems = [];

        foreach ($objItems as $i => $objItem) {
            $arrItems[] = $this->parseItem($objItem, $blnAddArchive, '', $i);
        }

        return $arrItems;
    }

    /**
     * Parse a single item and return it as string.
     *
     * @param FirefighterModel $objItem
     * @param bool $blnAddArchive
     * @param string $strClass
     * @param int $intCount
     *
     * @return string
     */
    protected function parseItem($objItem, $blnAddArchive = false, $strClass = '', $intCount = 0): string
    {
        global $objPage; // Ensure that the global $objPage is accessible
        $objTemplate = new FrontendTemplate($this->firefighter_template);
        $objTemplate->setData($objItem->row());

        // Get the local functions
        $localFunctions = StringUtil::deserialize($objItem->membersFunctionLocalWizard);
        if (is_array($localFunctions) && !empty($localFunctions)) {
            foreach ($localFunctions as &$function) {
                $function['short'] = $this->getFunctionShortName($function['membersFunctionLocal']);
                $function['period'] = $function['membersFunctionLocalPeriod'] ?? '';
            }
            $objTemplate->membersFunctionLocal = $localFunctions;
        }

        // Get the section functions
        $sectionFunctions = StringUtil::deserialize($objItem->membersFunctionSectionWizard);
        if (is_array($sectionFunctions) && !empty($sectionFunctions)) {
            foreach ($sectionFunctions as &$function) {
                $function['short'] = $this->getFunctionShortName($function['membersFunctionSection']);
                $function['period'] = $function['membersFunctionSectionPeriod'] ?? '';
            }
            $objTemplate->membersFunctionSection = $sectionFunctions;
        }

        // Add other item data to the template
        $objTemplate->class = ('' !== $objItem->cssClass ? ' '.$objItem->cssClass : '').$strClass;
        $objTemplate->headline = $objItem->headline;
        $objTemplate->linkHeadline = $this->generateLink($objItem->headline, $objItem, $blnAddArchive);
        $objTemplate->more = $this->generateLink($GLOBALS['TL_LANG']['MSC']['more'], $objItem, $blnAddArchive, true);
        $objTemplate->link = Firefighter::generateFirefighterUrl($objItem, $blnAddArchive);
        $objTemplate->count = $intCount;
        $objTemplate->text = '';
        $objTemplate->hasText = false;
        $objTemplate->hasTeaser = false;
        $objTemplate->membersFirstname = $objItem->membersFirstname;
        $objTemplate->membersLastname = $objItem->membersLastname;
        $objTemplate->membersRank = $objItem->membersRank;
        $objTemplate->membersRankShortAbbr = '';
        $objTemplate->membersRankLongAbbr = '';
        $objTemplate->rankImage = '';

        // Fetch homebase details
        $homebaseDetails = $this->getHomebaseDetails($objItem->membersHomebase);
        $objTemplate->membersHomebase = $homebaseDetails['name'];
        $objTemplate->membersHomebaseWebsite = $homebaseDetails['website'];
        $objTemplate->membersEmail = $objItem->membersEmail;
        $objTemplate->membersPhone = $objItem->membersPhone;
        $objTemplate->membersPhoneFormatted = $this->formatPhoneNumber($objItem->membersPhone);

        if ($objItem->membersRank) {
            $result = Database::getInstance()->prepare("SELECT rank_short, rank_long, singleSRC FROM tl_firefighter_ranks WHERE id=?")
                                            ->execute($objItem->membersRank);

            if ($result->numRows > 0) {
                $rankShort = $result->rank_short;
                $rankLong = $result->rank_long;
                $rankHonory = (bool) $objItem->membersRankHonory;

                if ($rankHonory) {
                    $objTemplate->membersRankShortAbbr = "E" . $rankShort;
                    $objTemplate->membersRankLongAbbr = "Ehren" . strtolower($rankLong);
                } else {
                    $objTemplate->membersRankShortAbbr = $rankShort;
                    $objTemplate->membersRankLongAbbr = $rankLong;
                }

                // Fetch rank image
                if ($result->singleSRC) {
                    $objRankImage = FilesModel::findByUuid($result->singleSRC);

                    if ($objRankImage !== null && is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objRankImage->path)) {
                        $objTemplate->rankImage = $objRankImage->path;
                    }
                }
            }
        }

        // Clean the RTE output
        if ($objItem->teaser) {
            $objTemplate->hasTeaser = true;
            $objTemplate->teaser = $objItem->teaser;
            $objTemplate->teaser = StringUtil::encodeEmail($objTemplate->teaser);
        }

        // Display the "read more" button for external/article links
        if ('default' !== $objItem->source) {
            $objTemplate->text = true;
        } // Compile the firefighter text
        else {
            $objElement = ContentModel::findPublishedByPidAndTable($objItem->id, 'tl_firefighter');

            if (null !== $objElement) {
                while ($objElement->next()) {
                    $objTemplate->text .= self::getContentElement($objElement->current());
                }
            }

            $objTemplate->hasText = static fn () => ContentModel::countPublishedByPidAndTable($objItem->id, 'tl_firefighter') > 0;
        }

        // Add the meta information
        if ($objItem->firefightercategories) {
            $objTemplate->firefightercategories = '';
            $objCategories = [];
            $objTemplate->category_models = [];
            $categories = StringUtil::deserialize($objItem->firefightercategories);

            foreach ($categories as $category) {
                $objFirefighterCategoryModel = FirefighterCategoryModel::findByPk($category);
                if ($objFirefighterCategoryModel !== null) {
                    $objTemplate->category_models[] = $objFirefighterCategoryModel;
                    $objCategories[] = $objFirefighterCategoryModel->alias;

                    if (!$objTemplate->category_titles) {
                        $objTemplate->category_titles = '<ul class="level_1"><li>'.$objFirefighterCategoryModel->title.'</li>';
                    } else {
                        $objTemplate->category_titles .= '<li>'.$objFirefighterCategoryModel->title.'</li>';
                    }
                }
            }
            $objTemplate->category_titles .= '</ul>';
            $objTemplate->firefightercategories .= implode(',', $objCategories);
        }

        $objTemplate->addImage = false;

        // Add an image
        if ($objItem->addImage && '' !== $objItem->singleSRC) {
            $objModel = FilesModel::findByUuid($objItem->singleSRC);

            $projectDir = System::getContainer()->getParameter('kernel.project_dir');

            if (null !== $objModel && is_file($projectDir.'/'.$objModel->path)) {
                $arrArticle = $objItem->row();

                $imgSize = $objItem->size ?: null;

                // Override the default image size
                if ('' !== $this->imgSize) {
                    $size = StringUtil::deserialize($this->imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                        $arrArticle['size'] = $this->imgSize;
                        $imgSize = $this->imgSize;
                    }
                }

                $figure = System::getContainer()
                    ->get('contao.image.studio')
                    ->createFigureBuilder()
                    ->from($objModel)
                    ->setSize($imgSize)
                    ->setOverwriteMetadata($objItem->getOverwriteMetadata())
                    ->enableLightbox((bool) $objItem->fullsize)
                    ->buildIfResourceExists();

                $figure?->applyLegacyTemplateData($objTemplate);

                // Link to the firefighter reader if no image link has been defined (see #30)
                if (!$objTemplate->fullsize && !$objTemplate->imageUrl && $objTemplate->text) {
                    // Unset the image title attribute
                    $picture = $objTemplate->picture;
                    unset($picture['title']);
                    $objTemplate->picture = $picture;

                    // Link to the firefighter reader
                    $objTemplate->href = $objTemplate->link;
                    $objTemplate->linkTitle = StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $objItem->headline), true);

                    // If the external link is opened in a new window, open the image link in a new window, too
                    if ('external' === $objTemplate->source && $objTemplate->target) {
                        $objTemplate->attributes .= ' target="_blank"';
                    }
                }
            }
        }

        return $objTemplate->parse();
    }

    /**
     * Format phone number to remove all non-digit characters and add the international dialing code.
     *
     * @param string $phoneNumber
     *
     * @return string
     */
    protected function formatPhoneNumber($phoneNumber): string
    {
        // Remove all non-digit characters
        $formattedPhone = preg_replace('/\D/', '', $phoneNumber);
        // Remove leading zero and add international dialing code
        return 'tel:+43' . ltrim($formattedPhone, '0');
    }

    /**
     * Get the short name of a function based on its ID.
     *
     * @param int $functionId
     *
     * @return string
     */
    protected function getFunctionShortName($functionId): string
    {
        $function = Database::getInstance()
            ->prepare("SELECT function_short FROM tl_firefighter_functions WHERE id=?")
            ->execute($functionId);

        return $function->numRows ? $function->function_short : '';
    }

    /**
     * Fetch homebase details from the database.
     *
     * @param int $homebaseId
     *
     * @return array
     */
    protected function getHomebaseDetails($homebaseId): array
    {
        $result = Database::getInstance()
            ->prepare("SELECT ffname, socialChannels FROM tl_firefighter_departments WHERE id=?")
            ->execute($homebaseId);

        if ($result->numRows > 0) {
            $socialChannels = StringUtil::deserialize($result->socialChannels, true);
            $website = '';
            $facebook = '';

            foreach ($socialChannels as $channel) {
                if ($channel['platform'] === 'Webseite' && !empty($channel['url'])) {
                    $website = $channel['url'];
                } elseif ($channel['platform'] === 'Facebook' && !empty($channel['url'])) {
                    $facebook = $channel['url'];
                }
            }

            return [
                'name' => $result->ffname,
                'website' => $website ?: $facebook,
            ];
        }

        return [
            'name' => '',
            'website' => '',
        ];
    }
}
