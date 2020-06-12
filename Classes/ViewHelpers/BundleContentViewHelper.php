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
 * BundleContentViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BundleContentViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns content of productBundle of one product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function render(\RKW\RkwShop\Domain\Model\Product $product)
    {
        $parentProducts = $product->getParentProducts();

        // @todo: Was passiert, wenn ein Produkt in mehreren Collections enthalten ist?

        /*
        if ($product->getRecordType() == '\RKW\RkwShop\Domain\Model\ProductBundle') {
            $parentProducts = $product;
        }
        */

        /*
        if (
            ($parentProducts)
            && ($parentProducts->getRecordType() != '\RKW\RkwShop\Domain\Model\ProductSubscription')
        ){
        */

        if ($parentProducts) {

            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            /** @var \RKW\RkwShop\\Domain\Repository\ProductRepository $orderRepository */
            $productRepository = $objectManager->get('RKW\\RkwShop\\Domain\\Repository\\ProductRepository');

            $results = [];

            foreach ($parentProducts as $parentProduct) {

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $children = $parentProduct->getChildProducts();

                // only if there are more than the one we triggered this helper with
                if (count($children) > 1) {
                    $results[$parentProduct->getUid()] = $children;
                }
            }

            return $results;

        }

        return null;
    }
}