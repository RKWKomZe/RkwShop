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

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class CartItemController
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartItemController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * CartService
     *
     * @var \RKW\RkwShop\Service\Checkout\CartService
     * @inject
     */
    protected $cartService;

    /**
     * action add cart item
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param int                               $amount
     */
    public function addCartItemAction(\RKW\RkwShop\Domain\Model\Product $product, $amount = 0)
    {
        $cart = $this->cartService->getCart();

        $this->cartService->add($cart, $product, $amount);

        $this->redirect('showCart', 'Checkout', 'tx_rkwshop_cart', null, $this->settings['cartPid']);
    }

    /**
     * action remove cart item
     *
     * @param \RKW\RkwShop\Domain\Model\OrderItem $orderItem
     *
     * @ignorevalidation $orderItem
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function removeCartItemAction(\RKW\RkwShop\Domain\Model\OrderItem $orderItem)
    {
        $cart = $this->cartService->getCart();

        //  Warum schlägt die Validierung fehl, so dass ich @ignorevalidation nutzen muss?
        $this->cartService->remove($orderItem, $cart);

        $this->redirect('showCart', 'Checkout', 'tx_rkwshop_cart', null, $this->settings['cartPid']);
    }

    /**
     * action change cart item quantity
     *
     * @param \RKW\RkwShop\Domain\Model\OrderItem $orderItem
     * @param int $amount
     *
     * @todo Warum ist hier ignorevalidation erforderlich?
     * @ignorevalidation $orderItem
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function changeCartItemQuantityAction(\RKW\RkwShop\Domain\Model\OrderItem $orderItem, $amount = 0)
    {
        $cart = $this->cartService->getCart();

        $this->cartService->changeQuantity($cart, $orderItem, $amount);

        $this->redirect('showCart', 'Checkout', 'tx_rkwshop_cart', null, $this->settings['cartPid']);
    }

}
