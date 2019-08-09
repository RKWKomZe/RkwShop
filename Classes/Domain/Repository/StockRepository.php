<?php

namespace RKW\RkwShop\Domain\Repository;
use RKW\RkwBasics\Helper\QueryTypo3;

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
 * Class StockRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StockRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{



    /**
     * Get stock sum of one product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param bool $preOrder
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getStockSumByProductAndPreOrder(\RKW\RkwShop\Domain\Model\Product $product, $preOrder = false)
    {

        $whereAddition = ' AND tx_rkwshop_domain_model_stock.delivery_start < ' . time();
        if ($preOrder) {
            $whereAddition = ' AND tx_rkwshop_domain_model_stock.delivery_start > ' . time() ;
        }

        $query = $this->createQuery();
        $query->statement('
            SELECT SUM(amount) as sum FROM tx_rkwshop_domain_model_stock 
            WHERE tx_rkwshop_domain_model_stock.product = ' . intval($product->getUid()) .
            $whereAddition .
            QueryTypo3::getWhereClauseForVersioning('tx_rkwshop_domain_model_stock') .
            QueryTypo3::getWhereClauseForEnableFields('tx_rkwshop_domain_model_stock') . '
        
        ');

        $result = $query->execute(true);
        return intval($result[0]['sum']);
        //====
    }




}