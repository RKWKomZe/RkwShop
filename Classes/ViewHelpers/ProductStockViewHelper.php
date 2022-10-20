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
 * ProductStockViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductStockViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('product', Product::class, 'Product-object to get current stock for', true);
        $this->registerArgument('preOrder', 'bool', 'Return pre-orders instead of current stock', false, false);

    }


    /**
     * Returns current stock or current pre-order stock
     *
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function render(): int
    {
        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->arguments['product'];

        /** @var bool $preOrder */
        $preOrder = $this->arguments['preOrder'];

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \RKW\RkwShop\Orders\OrderManager $orderManager */
        $orderManager = $objectManager->get('RKW\\RkwShop\\Orders\\OrderManager');

        if ($preOrder) {
            return $orderManager->getPreOrderStockOfProduct($product);
        }

        return $orderManager->getRemainingStockOfProduct($product);
    }
}
