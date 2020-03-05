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

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * BundleItemsViewHelper
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BundleItemsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns child products of one product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwShop\Domain\Model\Product> $results
     */
    public function render(\RKW\RkwShop\Domain\Model\Product $product)
    {
        $results = $product->getChildProducts();

        if (count($results->toArray()) > 1) {
            return $results;
        }

        return null;
    }
}