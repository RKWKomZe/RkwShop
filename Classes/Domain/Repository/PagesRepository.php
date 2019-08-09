<?php

namespace RKW\RkwShop\Domain\Repository;


/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class PagesRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 */
class PagesRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{






    //=================================================

    /**
     * findByUidAlsoHiddenAndDeleted
     *
     * @param int $uid
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface

    public function findByUidAlsoHiddenAndDeleted($uid)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('uid', intval($uid))
        );

        return $query->execute()->getFirst();
        //===

    }
     * */


    /**
     * Returns all parent pages that have been imported via bm_pdf2content
     *
     * @api Used by RKW Soap
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface

    public function findAllImportedParentPages()
    {
        // Check if extension is installed
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bm_pdf2content')) {

            $query = $this->createQuery();
            $query->getQuerySettings()->setRespectStoragePage(false);
            $query->getQuerySettings()->setIncludeDeleted(true);
            $query->getQuerySettings()->setIgnoreEnableFields(true);

            $query->matching(
                $query->logicalAnd(
                    $query->equals('tx_bmpdf2content_is_import', 1),
                    $query->equals('tx_bmpdf2content_is_import_sub', 0)
                )
            );

            return $query->execute();
            //===
        }

        return null;
        //===
    }
     * */


    /**
     * Returns all parent pages that have been imported via bm_pdf2content
     *
     * @param integer $uid
     * @return null|\RKW\RkwShop\Domain\Model\Pages

    public function findOneImportedParentPagesByUid($uid)
    {
        // Check if extension is installed
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bm_pdf2content')) {

            $query = $this->createQuery();
            $query->getQuerySettings()->setRespectStoragePage(false);
            $query->getQuerySettings()->setIncludeDeleted(true);
            $query->getQuerySettings()->setIgnoreEnableFields(true);

            $query->matching(
                $query->logicalAnd(
                    $query->equals('uid', intval($uid)),
                    $query->equals('tx_bmpdf2content_is_import', 1),
                    $query->equals('tx_bmpdf2content_is_import_sub', 0)
                )
            );

            return $query->execute()->getFirst();
            //===
        }

        return null;
        //===
    }
     * */


    /**
     * Returns all visible parent pages that have been imported via bm_pdf2content
     *
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface

    public function findAllVisibleAndHiddenImportedParentPages()
    {

        // Check if extension is installed
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bm_pdf2content')) {

            $query = $this->createQuery();
            $query->getQuerySettings()->setRespectStoragePage(false);
            $query->getQuerySettings()->setIgnoreEnableFields(true);

            $query->matching(
                $query->logicalAnd(
                    $query->equals('tx_bmpdf2content_is_import', 1),
                    $query->equals('tx_bmpdf2content_is_import_sub', 0),
                    $query->logicalOr(
                        $query->equals('hidden', 1),
                        $query->equals('hidden', 0)
                    )
                )
            );

            return $query->execute();
            //===
        }

        return null;
        //===

    }
     * */


    /**
     * Returns all hidden and deleted parent pages that have been imported via bm_pdf2content and a certain title + subtitle
     *
     * @param \RKW\RkwShop\Domain\Model\Publication $publication
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface

    public function findAllHiddenAndDeletedImportedParentPagesByTitleAndSubtitle(\RKW\RkwShop\Domain\Model\Publication $publication)
    {
        // Check if extension is installed
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bm_pdf2content')) {

            $query = $this->createQuery();
            $query->getQuerySettings()->setRespectStoragePage(false);
            $query->getQuerySettings()->setIncludeDeleted(true);
            $query->getQuerySettings()->setIgnoreEnableFields(true);

            $query->matching(
                $query->logicalAnd(
                    $query->equals('tx_bmpdf2content_is_import', 1),
                    $query->equals('tx_bmpdf2content_is_import_sub', 0),
                    $query->logicalOr(
                        $query->equals('hidden', 1),
                        $query->equals('deleted', 1)
                    ),
                    $query->equals('title', $publication->getTitle()),
                    $query->equals('subtitle', $publication->getSubtitle())
                )
            );

            return $query->execute();
            //===
        }

        return null;
        //===
    }
     * */


    /**
     * findByTxRkwshopPublication
     *
     * Hint: Written because magic function does not work by any reason
     *
     * @param \RKW\RkwShop\Domain\Model\Publication $publication
     * @return null|\RKW\RkwShop\Domain\Model\Pages

    public function findByTxRkwshopPublication(\RKW\RkwShop\Domain\Model\Publication $publication)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->equals('txRkwshopPublication', $publication)
        );

        return $query->execute()->getFirst();
        //===

    }
     * */
}