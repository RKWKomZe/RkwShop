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

/**
 * GetListOfAdminsForProductViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetListOfAdminsForProductViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{


    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('product', Product::class, 'Product-object for which the backend-users are to be fetched', true);
        $this->registerArgument('backendUserForProductMap', 'array', 'Array of BackendUser-objects to select from', true);
    }


    /**
     * Returns admins for given product
     *
     * @return string
     */
    public function render(): string
    {
        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->arguments['product'];
        $backendUserForProductMap = $this->arguments['backendUserForProductMap'];

        if (! empty($backendUserForProductMap)) {
            if (isset($backendUserForProductMap[$product->getUid()])) {
               return $backendUserForProductMap[$product->getUid()];
            }
        }
        return '';

    }
}
