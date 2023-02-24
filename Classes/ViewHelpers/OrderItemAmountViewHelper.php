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

use RKW\RkwShop\Domain\Model\Product;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * OrderItemAmountViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderItemAmountViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('orderItemList', ObjectStorage::class, 'Object-storage of ordered items', true);
        $this->registerArgument('product', Product::class, 'Product-object to get amount for', true);

    }


    /**
     * Returns ordered amount of given product
     *
     * @return int
     */
    public function render(): int
    {
        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->arguments['product'];

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage <\RKW\RkwShop\Domain\Model\OrderItem> $orderItemList */
        $orderItemList = $this->arguments['orderItemList'];

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
