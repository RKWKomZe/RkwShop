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
        $productForList = $product->getProductBundle();
        if ($product->getRecordType() == '\RKW\RkwShop\Domain\Model\ProductBundle') {
            $productForList = $product;
        }
        
        if (
            ($productForList)
            && ($productForList->getRecordType() != '\RKW\RkwShop\Domain\Model\ProductSubscription')
        ){
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

            /** @var \RKW\RkwShop\\Domain\Repository\ProductRepository $orderRepository */
            $productRepository = $objectManager->get('RKW\\RkwShop\\Domain\\Repository\\ProductRepository');

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $productRepository->findByProductBundle($productForList);

            // only if there are more than the one we triggered this helper with
            if (count($results->toArray()) > 1) {
                return $results;
            }
        }

        return null;
    }
}