<?php

namespace RKW\RkwShop\Domain\Repository;

use RKW\RkwBasics\Helper\QueryTypo3;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

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
 * Class OrderItemRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderItemRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * Get ordered sum of one product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param bool $preOrder
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getOrderedSumByProductAndPreOrder(\RKW\RkwShop\Domain\Model\Product $product, $preOrder = false)
    {

        $whereAddition = ' AND tx_rkwshop_domain_model_orderitem.is_pre_order = 0';
        if ($preOrder) {
            $whereAddition = ' AND tx_rkwshop_domain_model_orderitem.is_pre_order = 1';
        }

        $query = $this->createQuery();
        $query->statement('
            SELECT SUM(amount) as sum FROM tx_rkwshop_domain_model_orderitem 
            WHERE tx_rkwshop_domain_model_orderitem.product = ' . intval($product->getUid()) .
            $whereAddition .
            QueryTypo3::getWhereClauseForVersioning('tx_rkwshop_domain_model_orderitem') .
            QueryTypo3::getWhereClauseForEnableFields('tx_rkwshop_domain_model_orderitem') . '
        
        ');

        $result = $query->execute(true);
        return intval($result[0]['sum']);
    }



    /**
     * Find all order items by order uid
     *
     * @api used by RKW Soap
     * @param integer $orderUid
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByOrderUidSoap($orderUid)
    {

        $query = $this->createQuery();

        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('order', intval($orderUid))
        );

        return $query->execute();
    }



    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uid The identifier of the object to find
     * @return \RKW\RkwShop\Domain\Model\OrderItem The matching object if found, otherwise NULL
     * @api used by RKW Soap
     */
    public function findByUidSoap($uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);

        $query->matching(
            $query->equals('uid', $uid)
        );

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }




}