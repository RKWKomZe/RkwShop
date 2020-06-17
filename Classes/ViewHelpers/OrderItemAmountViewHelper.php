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
 * OrderItemAmountViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderItemAmountViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns ordered amount of given product
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage <\RKW\RkwShop\Domain\Model\OrderItem> $orderItemList
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return int
     */
    public function render($orderItemList, \RKW\RkwShop\Domain\Model\Product $product)
    {

        if ($orderItemList instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage ) {

            /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
            foreach ($orderItemList as $orderItem){

                if ($orderItem->getProduct()->getUid() == $product->getUid()) {
                    return $orderItem->getAmount();
                }

            }
        }

        return 0;
    }
}