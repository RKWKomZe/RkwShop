<?php

namespace RKW\RkwShop\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * OrderCommandController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    /**
     * objectManager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * objectManager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * orderRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     * @inject
     */
    protected $orderRepository;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * Initialize the controller.
     */
    protected function initializeController()
    {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    }


    /**
     * Cleanup for anonymousToken and anonymousUser
     * !! DANGER !! Cleanup executes a real MySQL-Delete- Query!!!
     *
     * @param integer $hoursFromNow Defines which datasets (in days from now) should be deleted (send date is reference)
     * @return void
     */
    public function cleanupCommand($hoursFromNow = 8760)
    {
        if ($cleanupTimestamp = time() - intval($hoursFromNow) * 60 * 60) {

            if ($queueOrder = $this->orderRepository->findAllOldOrder($cleanupTimestamp)) {

                // delete corresponding data and the mail itself
                foreach ($queueOrder as $order) {

                    // 3. Delete order
                    $this->orderRepository->deleteBySelf($order);

                }

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, 'Successfully cleaned up database.');
            }
        }
    }


    /**
     * Imports publications (copied by pages-elements which are marked as publication)
     * !!! Does only work if the table tx_rkwshop_domain_model_publication is empty !!!
     *
     * @return void
     */
    public function importPublicationsCommand()
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = $objectManager->get('TYPO3\\CMS\Extbase\\Persistence\\Generic\\PersistenceManager');
        /** @var \RKW\RkwShop\Domain\Repository\PublicationRepository $publicationRepository */
        $publicationRepository = $objectManager->get('RKW\\RkwShop\\Domain\\Repository\\PublicationRepository');
        /** @var \RKW\RkwShop\Domain\Repository\BackendUserRepository $backendUserRepository */
        $backendUserRepository = $objectManager->get('RKW\\RkwShop\\Domain\\Repository\\BackendUserRepository');


        // do only something, if there no entries in db
        if (!count($publicationRepository->findAll())) {

            /** @var \RKW\RkwShop\Domain\Repository\PagesRepository $pagesRepository */
            $pagesRepository = $objectManager->get('RKW\\RkwShop\\Domain\\Repository\\PagesRepository');
            // use visible and hidden! (only bypass deleted records!)
            $publicationList = $pagesRepository->findAllVisibleAndHiddenImportedParentPages()->toArray();

            // -------------------------------------------------------
            // --- I. Manually adding of completely deleted pages ----
            // -------------------------------------------------------
            // Add pages which are unique, but completely deleted
            // -> we need them for the data overview and persistence (order overview e.g.)
            $addPagesArray = array(2253, 1557, 913, 1053, 3080, 4141, 2492, 729, 2416, 1036, 2754, 1965, 1963, 1966, 1962, 1957, 1958, 1967, 1968, 1954, 1969, 2068);
            foreach ($addPagesArray as $pagesUid) {
                // Hint: Can't make a summary query for ID list (different data between live and dev: Would crash)
                $additionalPages = $pagesRepository->findOneImportedParentPagesByUid($pagesUid);
                if ($additionalPages) {
                    $publicationList[] = $additionalPages;
                }
            }

            /** @var \RKW\RkwShop\Domain\Model\Pages $pages */
            foreach ($publicationList as $pages) {

                // -------------------------------------------------------
                // ----- II. Manually avoid adding pages (duplicate) -----
                // -------------------------------------------------------
                // Prevent some hidden (not deleted!) pages, which duplicated content of an visible page
                // -> don't create a publication-element for it!
                // -> the relation will added manually below
                $avoidPagesArray = array(930, 4589);
                if (in_array($pages->getUid(), $avoidPagesArray)) {
                    continue;
                }

                /** @var \RKW\RkwShop\Domain\Model\Publication $publication */
                $publication = $objectManager->get('RKW\\RkwShop\\Domain\\Model\\Publication');

                // 1. Copy data of pages element
                $publication->setOldPage($pages);
                $publication->setTitle($pages->getTitle());
                $publication->setSubtitle($pages->getSubtitle());
                $publication->setStock(0);

                if ($pages->getTxRkwbasicsSeries()) {
                    $publication->setSeries($pages->getTxRkwbasicsSeries());
                }

                // 2. Add data of related flexform of order plugin
                $table = 'tt_content';
                $whereClause = 'pid=' . $pages->getUid() . ' AND list_type="rkwshop_rkwshop" ';
                $rkwShopPlugin = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $whereClause);

                foreach ($rkwShopPlugin as $ttContentArray) {
                    $flexformData = $this->serializeFlexformDataFromDb($ttContentArray);
                    if (count($flexformData)) {
                        // set backendUser
                        if ($flexformData['settings.mail.backendUser']) {
                            $backendUserUidArray = GeneralUtility::trimExplode(',', $flexformData['settings.mail.backendUser']);
                            foreach ($backendUserUidArray as $backendUserUid) {
                                /** @var \RKW\RkwShop\Domain\Model\BackendUser $backendUser */
                                $backendUser = $backendUserRepository->findByIdentifier(intval($backendUserUid));
                                if ($backendUser) {
                                    $publication->addBackendUser($backendUser);
                                }
                            }
                        }
                        // set maximum order quantity
                        $publication->setStock(500);
                        if (
                            ($ttContentArray['hidden'])
                            || ($ttContentArray['deleted'])
                        ) {
                            $publication->setStock(0);
                        }
                        // set allow series
                        if ($flexformData['settings.order.allowSeries']) {
                            $publication->setAllowSeries(intval($flexformData['settings.order.allowSeries']));
                        }
                        // set allow subscription
                        if ($flexformData['settings.order.allowSubscription']) {
                            $publication->setAllowSubscription(intval($flexformData['settings.order.allowSubscription']));
                        }
                    }
                }

                $publicationRepository->add($publication);

                // 3. Add new publication as reference to pages element
                $pages->setTxRkwshopPublication($publication);
                $pagesRepository->update($pages);

                // 4. Add new publication also as reference to duplicated pages element (identified by same title + subtitle)
                $hiddenPagesList = $pagesRepository->findAllHiddenAndDeletedImportedParentPagesByTitleAndSubtitle($publication);

                foreach ($hiddenPagesList as $hiddenPages) {
                    $hiddenPages->setTxRkwshopPublication($publication);
                    $pagesRepository->update($hiddenPages);
                }

                // -------------------------------------------------------
                // III. Manually adding of not successfully assigned pages
                // -------------------------------------------------------
                // At the end: Relation-fixing (based on manually excel-file comparing)
                // -> if there are duplicated pages, where the "title AND subtitle" query is not matching
                /** @var \RKW\RkwShop\Domain\Model\Pages $pagesElement */

                // "Betriebliche Suchtprävention in Kleinst- und Kleinunternehmen"
                if ($pages->getUid() == 1235) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1409);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1764);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1526);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Arbeit demografiefest gestalten
                if ($pages->getUid() == 3100) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(3095);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Betriebliche Suchtprävention
                if ($pages->getUid() == 1272) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1259);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Chefsachen 1/2017
                if ($pages->getUid() == 3801) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4332);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Chefsachen 1/2018
                if ($pages->getUid() == 4339) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4333);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Der Europäische Unternehmensförderpreis 2015
                if ($pages->getUid() == 2295) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2172);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Gründungseinstellung in Deutschland
                if ($pages->getUid() == 4409) {
                    // @toDo: Check LIVE area for it: Id 4430 does not exist on dev!
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4430);
                    if ($pagesElement) {
                        $pagesElement->setTxRkwshopPublication($publication);
                        $pagesRepository->update($pagesElement);
                    }
                }
                // Gründungsnetzwerke aufbauen
                if ($pages->getUid() == 4140) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4027);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4128);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // In den Betrieb reinschnuppern
                if ($pages->getUid() == 3373) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2500);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Jobfamilien in mittelständischen Unternehmen
                if ($pages->getUid() == 3342) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2969);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Klein – aber fein
                if ($pages->getUid() == 3272) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2675);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2498);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Kompetent arbeiten
                if ($pages->getUid() == 3207) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(3201);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Mitarbeiterorientierte Personalstrategien im IT-Mittelstand
                if ($pages->getUid() == 952) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1662);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1858);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1477);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // RKW Bücherdienst 1/2012
                if ($pages->getUid() == 2130) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2135);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2151);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2133);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // RKW Bücherdienst 2/2016
                if ($pages->getUid() == 2836) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2835);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2834);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2824);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Strategie für kleine Unternehmen
                if ($pages->getUid() == 1308) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(930);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Vielfaltsbewusste Führung
                if ($pages->getUid() == 4627) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4589);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // ibr - Informationen Bau-Rationalisierung 1/2018
                if ($pages->getUid() == 5477) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4295);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(4280);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // ibr - Informationen Bau-Rationalisierung 1/2017
                if ($pages->getUid() == 3363) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(3347);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(3413);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Produktivität für kleine und mittelständische Unternehmen I
                if ($pages->getUid() == 2034) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(874);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Produktivität für kleine und mittelständische Unternehmen II
                if ($pages->getUid() == 857) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(596);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2063);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Vom Du zum Wir: Jugendliche aktiv ansprechen und für eine Ausbildung gewinnen
                if ($pages->getUid() == 2754) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2499);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Gesundheit geht uns alle an
                if ($pages->getUid() == 1314) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1623);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(1819);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }
                // Produktivitätsmanagement für industrielle Dienstleistungen stärken I
                if ($pages->getUid() == 2385) {
                    $pagesElement = $pagesRepository->findByUidAlsoHiddenAndDeleted(2183);
                    $pagesElement->setTxRkwshopPublication($publication);
                    $pagesRepository->update($pagesElement);
                }

                $persistenceManager->persistAll();
            }
        }
    }


    /**
     * serialize flexform-data of tt_content element from string to array
     *
     * @param array $ttContentArray
     * @return array
     */
    protected function serializeFlexformDataFromDb($ttContentArray)
    {
        $flexformData = array();
        if (is_array($ttContentArray)) {
            $xml = simplexml_load_string($ttContentArray['pi_flexform']);
            if (
                (isset($xml))
                && (isset($xml->data))
                && (is_object($xml->data->sheet))
            ) {
                foreach ($xml->data->sheet as $sheet) {
                    foreach ($sheet->language->field as $field) {
                        $flexformData[str_replace('', '', (string)$field->attributes())] = (string)$field->value;
                    }
                }
            }
        }

        return $flexformData;
        //===
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }

}