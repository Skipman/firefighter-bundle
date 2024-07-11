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

 use Contao\Config;
 use Contao\ContentElement;
 use Contao\FilesModel;
 use Contao\StringUtil;
 use Contao\System;
 use Contao\Database;
 use Contao\DataContainer;
 use Contao\Validator;
 
 class FirefighterMembersElement extends ContentElement
 {
     // protected $strTemplate = 'ce_members';
 
     // Generate Label for Backend
     public function generate()
     {
         if (System::getContainer()->get('request_stack')->getCurrentRequest()->get('_scope') === 'backend') {
             return $this->generateBackendLabel();
         }
 
         return parent::generate();
     }
 
     protected function generateBackendLabel()
     {
         $lastname = $this->membersLastname ?: '';
         $firstname = $this->membersFirstname ?: '';
 
         return $lastname . ' ' . $firstname;
     }
 
     /**
      * Template.
      *
      * @var string
      */
     protected $strTemplate = 'ce_members';
 
     /**
      * Generate the content element.
      */
     protected function compile(): void
     {
         // Add the static files URL to images
         if ($staticUrl = System::getContainer()->get('contao.assets.files_context')->getStaticUrl()) {
             $path = Config::get('uploadPath') . '/';
         }
 
         $this->Template->text = StringUtil::encodeEmail($this->text);
         $this->Template->addMembersImage = false;
 
         // Add an image
         if ($this->addMembersImage && $this->singleSRC) {
             $objModel = FilesModel::findByUuid($this->singleSRC);
 
             if ($objModel !== null && is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objModel->path)) {
                 $this->singleSRC = $objModel->path;
                 $this->overwriteMeta = ($this->alt || $this->imageTitle || $this->caption);
 
                 $figure = System::getContainer()
                     ->get('contao.image.studio')
                     ->createFigureBuilder()
                     ->from($objModel->path)
                     ->setSize($this->size)
                     ->setMetadata($this->objModel->getOverwriteMetadata())
                     ->enableLightbox((bool) $this->fullsize)
                     ->buildIfResourceExists();
 
                 if (null !== $figure) {
                     $figure->applyLegacyTemplateData($this->Template, $this->imagemargin, $this->floating);
                 }
             }
 
             $this->Template->addMembersImage = true;
         }
 
         // Block Dienstränge
         $this->Template->membersRankShortAbbr = '';
         $this->Template->membersRankLongAbbr = '';
         $this->Template->rankImage = '';
 
         if ($this->membersRank) {
             $result = Database::getInstance()->prepare("SELECT rank_short, rank_long, singleSRC FROM tl_cob_ranks WHERE id=?")
                                              ->execute($this->membersRank);
 
             if ($result->numRows > 0) {
                 $rankShort = $result->rank_short;
                 $rankLong = $result->rank_long;
                 $rankHonory = (bool) $this->membersRankHonory;
 
                 if ($rankHonory) {
                     $this->Template->membersRankShortAbbr = "E" . $rankShort;
                     $this->Template->membersRankLongAbbr = "Ehren" . strtolower($rankLong);
                 } else {
                     $this->Template->membersRankShortAbbr = $rankShort;
                     $this->Template->membersRankLongAbbr = $rankLong;
                 }
 
                 // Fetch rank image
                 if ($result->singleSRC) {
                     $objRankImage = FilesModel::findByUuid($result->singleSRC);
 
                     if ($objRankImage !== null && is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objRankImage->path)) {
                         $this->Template->rankImage = $objRankImage->path;
                     }
                 }
             }
         }
 
         // Block Funktionen
         $this->Template->membersFunctionLocalShort = '';
         $this->Template->membersFunctionLocalLong = '';
 
         if ($this->membersFunctionLocal) {
             $result = Database::getInstance()->prepare("SELECT function_short, function_long FROM tl_cob_functions WHERE id=?")
                                              ->execute($this->membersFunctionLocal);
 
             if ($result->numRows > 0) {
                 $this->Template->membersFunctionLocalShort = $result->function_short;
                 $this->Template->membersFunctionLocalLong = $result->function_long;
             }
         }
 
         $this->Template->membersFunctionSectionShort = '';
         $this->Template->membersFunctionSectionLong = '';
 
         if ($this->membersFunctionSection) {
             $result = Database::getInstance()->prepare("SELECT function_short, function_long FROM tl_cob_functions WHERE id=?")
                                              ->execute($this->membersFunctionSection);
 
             if ($result->numRows > 0) {
                 $this->Template->membersFunctionSectionShort = $result->function_short;
                 $this->Template->membersFunctionSectionLong = $result->function_long;
             }
         }
 
         // Block Homebase
         $this->Template->membersHomebase = '';
         $this->Template->website = '';
         $this->Template->facebook = '';
         $this->Template->instagram = '';
         $this->Template->youtube = '';
         $this->Template->twitter = '';
         $this->Template->tiktok = '';
 
         if ($this->membersHomebase) {
             $result = Database::getInstance()->prepare("SELECT ffname, website, facebook, instagram, youtube, twitter, tiktok FROM tl_cob_departments WHERE id=?")
                                              ->execute($this->membersHomebase);
 
             if ($result->numRows > 0) {
                 $this->Template->membersHomebase = $result->ffname;
                 $this->Template->website = $result->website;
                 $this->Template->facebook = $result->facebook;
                 $this->Template->instagram = $result->instagram;
                 $this->Template->youtube = $result->youtube;
                 $this->Template->twitter = $result->twitter;
                 $this->Template->tiktok = $result->tiktok;
             }
         }
 
         // Encode members email
         $this->Template->membersEmail = StringUtil::encodeEmail($this->membersEmail);
 
         // Add members email link
         $this->Template->membersEmailLink = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.StringUtil::encodeEmail($this->membersEmail);
 
         // Encode members phone
         // $phone_clean = "tel:0043".substr(preg_replace('/[^0-9]/', '', $this->phone),1);
         $this->Template->membersPhoneLink = "tel:0043".substr(preg_replace('/[^0-9]/', '', $this->membersPhone),1);
     }
 
     // Add label for Backend-View
     public static function addMembersElementLabel(array $row, string $label, DataContainer $dc, array $args): array
     {
         $lastname = $row['membersLastname'] ?: '';
         $firstname = $row['membersFirstname'] ?: '';
 
         $args[0] = $lastname . ' ' . $firstname;
 
         return $args;
     }
 }
 





 /*
 namespace Skipman\FirefighterBundle\ContentElement;

 use Contao\Config;
 use Contao\ContentElement;
 use Contao\FilesModel;
 use Contao\StringUtil;
 use Contao\System;
 use Contao\Database;
 use Contao\DataContainer;
 use Contao\Validator;
 
 class FirefighterMembersElement extends ContentElement
 {
     // protected $strTemplate = 'ce_members';
 
     // Generate Label for Backend
     public function generate()
     {
         if (System::getContainer()->get('request_stack')->getCurrentRequest()->get('_scope') === 'backend') {
             return $this->generateBackendLabel();
         }
 
         return parent::generate();
     }
 
     protected function generateBackendLabel()
     {
         $lastname = $this->membersLastname ?: '';
         $firstname = $this->membersFirstname ?: '';
 
         return $lastname . ' ' . $firstname;
     }
 */
     /**
      * Template.
      *
      * @var string
      */

    /*  
     protected $strTemplate = 'ce_members';
 
     /**
      * Generate the content element.
      */

      /*
     protected function compile(): void
     {
         // Add the static files URL to images
         if ($staticUrl = System::getContainer()->get('contao.assets.files_context')->getStaticUrl()) {
             $path = Config::get('uploadPath') . '/';
         }
 
         $this->Template->text = StringUtil::encodeEmail($this->text);
         $this->Template->addMembersImage = false;
 
         // Add an image
         if ($this->addMembersImage && $this->singleSRC) {
             $objModel = FilesModel::findByUuid($this->singleSRC);
 
             if ($objModel !== null && is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objModel->path)) {
                 $this->singleSRC = $objModel->path;
                 $this->overwriteMeta = ($this->alt || $this->imageTitle || $this->caption);
 
                 $figure = System::getContainer()
                 ->get('contao.image.studio')
                 ->createFigureBuilder()
                 ->from($objModel->path)
                 ->setSize($this->size)
                 ->setMetadata($this->objModel->getOverwriteMetadata())
                 ->enableLightbox((bool) $this->fullsize)
                 ->buildIfResourceExists();
 
                 if (null !== $figure)
                 {
                     $figure->applyLegacyTemplateData($this->Template, $this->imagemargin, $this->floating);
                 }
             }
 
             $this->Template->addMembersImage = true;
         }
 
         // Block Dienstränge
         $this->Template->membersRankShortAbbr = '';
         $this->Template->membersRankLongAbbr = '';
         $this->Template->rankImage = null;
 
         if ($this->membersRank) {
             $result = Database::getInstance()->prepare("SELECT rank_short, rank_long, singleSRC FROM tl_cob_ranks WHERE id=?")
                                              ->execute($this->membersRank);
 
             if ($result->numRows > 0) {
                 $rankShort = $result->rank_short;
                 $rankLong = $result->rank_long;
                 $rankHonory = (bool) $this->membersRankHonory;
 
                 if ($rankHonory) {
                     $this->Template->membersRankShortAbbr = "E" . $rankShort;
                     $this->Template->membersRankLongAbbr = "Ehren" . strtolower($rankLong);
                 } else {
                     $this->Template->membersRankShortAbbr = $rankShort;
                     $this->Template->membersRankLongAbbr = $rankLong;
                 }
 
                 // Fetch rank image
                 if ($result->singleSRC) {
                     $objRankImage = FilesModel::findByUuid($result->singleSRC);
 
                     if ($objRankImage !== null && is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objRankImage->path)) {
                         $figure = System::getContainer()
                             ->get('contao.image.studio')
                             ->createFigureBuilder()
                             ->from($objRankImage->path)
                             ->buildIfResourceExists();
 
                         if (null !== $figure) {
                             $this->Template->rankImage = $figure->getImage();
                         }
                     }
                 }
             }
         }
 
         // Block Funktionen
         $this->Template->membersFunctionLocalShort = '';
         $this->Template->membersFunctionLocalLong = '';
 
         if ($this->membersFunctionLocal) {
             $result = Database::getInstance()->prepare("SELECT function_short, function_long FROM tl_cob_functions WHERE id=?")
                                              ->execute($this->membersFunctionLocal);
 
             if ($result->numRows > 0) {
                 $this->Template->membersFunctionLocalShort = $result->function_short;
                 $this->Template->membersFunctionLocalLong = $result->function_long;
             }
         }
 
         $this->Template->membersFunctionSectionShort = '';
         $this->Template->membersFunctionSectionLong = '';
 
         if ($this->membersFunctionSection) {
             $result = Database::getInstance()->prepare("SELECT function_short, function_long FROM tl_cob_functions WHERE id=?")
                                              ->execute($this->membersFunctionSection);
 
             if ($result->numRows > 0) {
                 $this->Template->membersFunctionSectionShort = $result->function_short;
                 $this->Template->membersFunctionSectionLong = $result->function_long;
             }
         }
 
         // Block Homebase
         $this->Template->membersHomebase = '';
         $this->Template->website = '';
         $this->Template->facebook = '';
         $this->Template->instagram = '';
         $this->Template->youtube = '';
         $this->Template->twitter = '';
         $this->Template->tiktok = '';
 
         if ($this->membersHomebase) {
             $result = Database::getInstance()->prepare("SELECT ffname, website, facebook, instagram, youtube, twitter, tiktok FROM tl_cob_departments WHERE id=?")
                                              ->execute($this->membersHomebase);
 
             if ($result->numRows > 0) {
                 $this->Template->membersHomebase = $result->ffname;
                 $this->Template->website = $result->website;
                 $this->Template->facebook = $result->facebook;
                 $this->Template->instagram = $result->instagram;
                 $this->Template->youtube = $result->youtube;
                 $this->Template->twitter = $result->twitter;
                 $this->Template->tiktok = $result->tiktok;
             }
         }
 
         // Encode members email
         $this->Template->membersEmail = StringUtil::encodeEmail($this->membersEmail);
 
         // Add members email link
         $this->Template->membersEmailLink = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.StringUtil::encodeEmail($this->membersEmail);
 
         // Encode members phone
         // $phone_clean = "tel:0043".substr(preg_replace('/[^0-9]/', '', $this->phone),1);
         $this->Template->membersPhoneLink = "tel:0043".substr(preg_replace('/[^0-9]/', '', $this->membersPhone),1);
     }
 
     // Add label for Backend-View
     public static function addMembersElementLabel(array $row, string $label, DataContainer $dc, array $args): array
     {
         $lastname = $row['membersLastname'] ?: '';
         $firstname = $row['membersFirstname'] ?: '';
 
         $args[0] = $lastname . ' ' . $firstname;
 
         return $args;
     }
 }
*/ 
 


/*
namespace Skipman\FirefighterBundle\ContentElement;

use Contao\Config;
use Contao\ContentElement;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Database;
use Contao\DataContainer;
use Contao\Validator;

class FirefighterMembersElement extends ContentElement
{
    //protected $strTemplate = 'ce_members';

    // Generate Label for Backend
    public function generate()
    {
        if (System::getContainer()->get('request_stack')->getCurrentRequest()->get('_scope') === 'backend') {
            return $this->generateBackendLabel();
        }

        return parent::generate();
    }

    protected function generateBackendLabel()
    {
        $lastname = $this->membersLastname ?: '';
        $firstname = $this->membersFirstname ?: '';

        return $lastname . ' ' . $firstname;
    }

    /**
     * Template.
     *
     * @var string
     */
    //protected $strTemplate = 'ce_members';

    /**
     * Generate the content element.
     */

    /*
    protected function compile(): void
    {
        // Add the static files URL to images
        if ($staticUrl = System::getContainer()->get('contao.assets.files_context')->getStaticUrl()) {
            $path = Config::get('uploadPath') . '/';
        }

        $this->Template->text = StringUtil::encodeEmail($this->text);
        $this->Template->addMembersImage = false;

        // Add an image
        if ($this->addMembersImage && $this->singleSRC) {
            $objModel = FilesModel::findByUuid($this->singleSRC);

            if ($objModel !== null && is_file(System::getContainer()->getParameter('kernel.project_dir') . '/' . $objModel->path)) {
                $this->singleSRC = $objModel->path;
                $this->overwriteMeta = ($this->alt || $this->imageTitle || $this->caption);

                $figure = System::getContainer()
                ->get('contao.image.studio')
                ->createFigureBuilder()
                ->from($objModel->path)
                ->setSize($this->size)
                ->setMetadata($this->objModel->getOverwriteMetadata())
                ->enableLightbox((bool) $this->fullsize)
                ->buildIfResourceExists();

                if (null !== $figure)
                {
                    $figure->applyLegacyTemplateData($this->Template, $this->imagemargin, $this->floating);
                }
            }

            $this->Template->addMembersImage = true;
        }

        // Block Dienstränge
        $this->Template->membersRankShortAbbr = '';
        $this->Template->membersRankLongAbbr = '';

        if ($this->membersRank) {
            $result = Database::getInstance()->prepare("SELECT rank_short, rank_long FROM tl_cob_ranks WHERE id=?")
                                             ->execute($this->membersRank);

            if ($result->numRows > 0) {
                $rankShort = $result->rank_short;
                $rankLong = $result->rank_long;
                $rankHonory = (bool) $this->membersRankHonory;

                if ($rankHonory) {
                    $this->Template->membersRankShortAbbr = "E" . $rankShort;
                    $this->Template->membersRankLongAbbr = "Ehren" . strtolower($rankLong);
                } else {
                    $this->Template->membersRankShortAbbr = $rankShort;
                    $this->Template->membersRankLongAbbr = $rankLong;
                }
            }
        }

        // Block Funktionen
        $this->Template->membersFunctionLocalShort = '';
        $this->Template->membersFunctionLocalLong = '';

        if ($this->membersFunctionLocal) {
            $result = Database::getInstance()->prepare("SELECT function_short, function_long FROM tl_cob_functions WHERE id=?")
                                             ->execute($this->membersFunctionLocal);

            if ($result->numRows > 0) {
                $this->Template->membersFunctionLocalShort = $result->function_short;
                $this->Template->membersFunctionLocalLong = $result->function_long;
            }
        }

        $this->Template->membersFunctionSectionShort = '';
        $this->Template->membersFunctionSectionLong = '';

        if ($this->membersFunctionSection) {
            $result = Database::getInstance()->prepare("SELECT function_short, function_long FROM tl_cob_functions WHERE id=?")
                                             ->execute($this->membersFunctionSection);

            if ($result->numRows > 0) {
                $this->Template->membersFunctionSectionShort = $result->function_short;
                $this->Template->membersFunctionSectionLong = $result->function_long;
            }
        }

        // Block Homebase
        $this->Template->membersHomebase = '';
        $this->Template->website = '';
        $this->Template->facebook = '';
        $this->Template->instagram = '';
        $this->Template->youtube = '';
        $this->Template->twitter = '';
        $this->Template->tiktok = '';

        if ($this->membersHomebase) {
            $result = Database::getInstance()->prepare("SELECT ffname, website, facebook, instagram, youtube, twitter, tiktok FROM tl_cob_departments WHERE id=?")
                                             ->execute($this->membersHomebase);

            if ($result->numRows > 0) {
                $this->Template->membersHomebase = $result->ffname;
                $this->Template->website = $result->website;
                $this->Template->facebook = $result->facebook;
                $this->Template->instagram = $result->instagram;
                $this->Template->youtube = $result->youtube;
                $this->Template->twitter = $result->twitter;
                $this->Template->tiktok = $result->tiktok;
            }
        }

        // Encode members email
        $this->Template->membersEmail = StringUtil::encodeEmail($this->membersEmail);

        // Add members email link
        $this->Template->membersEmailLink = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.StringUtil::encodeEmail($this->membersEmail);

        // Encode members phone
        // $phone_clean = "tel:0043".substr(preg_replace('/[^0-9]/', '', $this->phone),1);
        $this->Template->membersPhoneLink = "tel:0043".substr(preg_replace('/[^0-9]/', '', $this->membersPhone),1);
    }

    // Add label for Backend-View
    public static function addMembersElementLabel(array $row, string $label, DataContainer $dc, array $args): array
    {
        $lastname = $row['membersLastname'] ?: '';
        $firstname = $row['membersFirstname'] ?: '';

        $args[0] = $lastname . ' ' . $firstname;

        return $args;
    }
}
*/