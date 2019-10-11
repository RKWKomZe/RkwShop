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
 * GetListOfAdminsForProductViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetListOfAdminsForProductViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Returns admins for given product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param array $backendUserForProductMap
     * @return string
     */
    public function render(\RKW\RkwShop\Domain\Model\Product $product, $backendUserForProductMap)
    {
        if (! empty($backendUserForProductMap)) {
            if (isset($backendUserForProductMap[$product->getUid()])) {
               return $backendUserForProductMap[$product->getUid()];
            }
        }
        return '';

    }
}