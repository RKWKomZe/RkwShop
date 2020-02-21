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
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class CartController
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @inject
     */
    protected $productRepository = null;

    /**
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param int                               $amount
     */
    public function updateAction(\RKW\RkwShop\Domain\Model\Product $product, $amount = 0)
    {

        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setAmount($amount);

        $cartItems = $GLOBALS['TSFE']->fe_user->getKey('ses', 'cart');

        $orderItemExists = false;
        foreach ($cartItems as $key => $cartItem) {
            if ($cartItem->getProduct()->getUid() === $orderItem->getProduct()->getUid()) {
                $amount = $cartItem->getAmount() + $orderItem->getAmount();
                $cartItem->setAmount($amount);

                $cartItems[$key] = $cartItem;

                $orderItemExists = true;
            }
        }

        if (! $orderItemExists) {
            $cartItems[] = $orderItem;
        }

        $GLOBALS['TSFE']->fe_user->setKey('ses', 'cart', $cartItems);
        $GLOBALS['TSFE']->fe_user->storeSessionData();

        $this->redirect('show', 'Cart', 'tx_rkwshop_cart', null, $this->settings['cartPid']);

    }

    /**
     * action show
     *
     * @return void
     */
    public function showAction()
    {
        $cartItems = $GLOBALS['TSFE']->fe_user->getKey('ses', 'cart');

        $listItemsPerView = (int)$this->settings['itemsPerPage'] ? (int)$this->settings['itemsPerPage'] : 10;

//        $productList = DivUtility::prepareResultsList($queryResult, $listItemsPerView);

        $this->view->assignMultiple([
            'cartItems'   => $cartItems,
        ]);

    }
}