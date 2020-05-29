<?php

namespace RKW\RkwShop\Controller;

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

use RKW\RkwShop\Helper\DivUtility;

/**
 * Class ManageProductsController
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @inject
     */
    protected $productRepository = null;

    /**
     * action list products
     *
     * @return void
     */
    public function listAction()
    {
        $listItemsPerView = (int)$this->settings['itemsPerPage'] ? (int)$this->settings['itemsPerPage'] : 10;

        $queryResult = $this->productRepository->findAll();
        $productList = DivUtility::prepareResultsList($queryResult, $listItemsPerView);

        $this->view->assignMultiple([
            'productList'   => $productList,
            'showPid'       => (int)$this->settings['showPid']
        ]);
    }

    /**
     * action show single product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     *
     * @return void
     */
    public function showAction(\RKW\RkwShop\Domain\Model\Product $product)
    {
        $this->view->assignMultiple([
            'product'   => $product,
            'cartPid'   => (int)$this->settings['cartPid']
        ]);
    }
}