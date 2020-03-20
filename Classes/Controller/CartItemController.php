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

        //  Die nachfolgenden Actions müssten über einen eigenen Controller CartItemController einzeln gesteuert werden und demzufolge hier raus!
        //  addOrderItem aka addCartItem
        //  removeFromCart aka removeCartItem
        //  changeQuantity

        $this->cartService->add($product, $amount);

        $this->redirect('showCart', 'Checkout', 'tx_rkwshop_cart', null, $this->settings['cartPid']);
    }

    /**
     * action remove cart item
     *
     * @param \RKW\RkwShop\Domain\Model\OrderItem $orderItem
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function removeCartItemAction(\RKW\RkwShop\Domain\Model\OrderItem $orderItem)
    {
        $this->cartService->remove($orderItem);

        $this->redirect('showCart', 'Checkout', 'tx_rkwshop_cart', null, $this->settings['cartPid']);
    }

}
