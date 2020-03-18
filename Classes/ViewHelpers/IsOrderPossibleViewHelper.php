<?php

namespace RKW\RkwShop\ViewHelpers;


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
 * IsOrderPossibleViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsOrderPossibleViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns current stock or current pre-order stock
     *
     * @param array $products</\RKW\RkwShop\Domain\Model\Product> Array of \RKW\RkwShop\Domain\Model\Product
     * @return bool
     *  @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function render(array $products)
    {

        foreach ($products as $product){

            if ($product instanceof \RKW\RkwShop\Domain\Model\Product) {

                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                /** @var \RKW\RkwShop\Service\Checkout\OrderService */
                $orderService = $objectManager->get(\RKW\RkwShop\Service\Checkout\OrderService::class);

                if (
                    ($orderService->getRemainingStockOfProduct($product))
                    || ($orderService->getPreOrderStockOfProduct($product))
                ){
                    return true;
                }
            }
        }

        return false;

    }
}