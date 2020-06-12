<?php
namespace RKW\RkwShop\Updates;

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
 * Class GenerateOrderNumberForExistingOrders
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

use RKW\RkwShop\Service\Checkout\OrderService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

class GenerateOrderNumberForExistingOrders extends AbstractUpdate
{
    /**
     * @var string
     */
    protected $title = 'Bestellnummern';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \RKW\RkwShop\Service\Checkout\OrderService
     * @injects
     */
    protected $orderService;

    /**
     * Constructor function.
     */
    public function __construct()
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->orderService = $this->objectManager->get(OrderService::class);
    }


    /**
     * Checks if an update is needed
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {

        // @todo: Version check!

        if ($this->isWizardDone() || !ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return false;
        }

        $description = 'Generieren und Speichern von Bestellnummern für bereits bestehende Bestellungen';

        return true;
    }

    /**
     * Performs the database update
     *
     * @param array &$databaseQueries Queries done in this update
     * @param mixed &$customMessages Custom messages
     * @return bool
     */
    public function performUpdate(array &$databaseQueries, &$customMessages)
    {

        if ($this->hasLock(__FUNCTION__)) {
            return false;
        }

        $ordersNeedingUpdate = $this->getUpdatableOrders();

        $newOrderNumber = 1;

        if (!empty($ordersNeedingUpdate)) {
            $uids = array_column($ordersNeedingUpdate, 'uid');

            foreach ($uids as $uid) {

                $this->getDatabaseConnection()->exec_UPDATEquery(
                    'tx_rkwshop_domain_model_order',
                    'uid = ' . $uid . '',
                    [
                        'order_number' => $this->orderService->buildOrderNumber($newOrderNumber)
                    ]
                );

                $newOrderNumber++;

                $databaseQueries[] = $this->getDatabaseConnection()->debug_lastBuiltQuery;

            }

        }

        $this->markWizardAsDone();

        return true;
    }

    /**
     * Get orders which need to be updated
     *
     * @return array|NULL
     */
    protected function getUpdatableOrders()
    {
        return $this->getDatabaseConnection()->exec_SELECTgetRows('uid', 'tx_rkwshop_domain_model_order', 'order_number IS NULL');
    }

    /**
     * Checks the lock
     *
     * @param string $method
     * @return bool
     */
    protected function hasLock ($method)
    {
        return file_exists(PATH_site . 'typo3temp/var/locks/tx_rkwshop_' . $method . '.lock');
    }

}
