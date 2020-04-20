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

use RKW\RkwShop\Domain\Model\Product;
use RKW\RkwShop\Helper\DivUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ManageProductsController
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ManageProductsController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
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

        $products = $this->productRepository->findAll();

        $this->view->assign('products', $products);

    }

    /**
     * action show
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     *
     * @ignorevalidation $product
     */
    public function showAction(\RKW\RkwShop\Domain\Model\Product $product = null)
    {
        if (empty($product)) {
            $this->forward('list');
        }

        //  $this->view->assign('user', $GLOBALS['TSFE']->fe_user->user);
        $this->view->assign('product', $product);

        //  $this->assignCurrencyTranslationData();
    }

}