<?php

namespace RKW\RkwShop\Service\Checkout;

use RKW\RkwShop\Domain\Model\Cart;
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use RKW\RkwShop\Domain\Model\ShippingAddress;
use RKW\RkwShop\Exception;
use RKW\RkwShop\Exceptions\CartHashNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
 * Class CartService
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartService implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_CREATED_ADMIN = 'afterOrderCreatedAdmin';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_CREATED_USER = 'afterOrderCreatedUser';    //  @todo: Ist das die richtige Benennung für dieses Event?

    /**
     * cart
     *
     * @var \RKW\RkwShop\Domain\Model\Order
     */
    protected $cart;

    /**
     * cartRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\CartRepository
     * @inject
     */
    protected $cartRepository;

    /**
     * orderRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     * @inject
     */
    protected $orderRepository;


    /**
     * orderItemRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     * @inject
     */
    protected $orderItemRepository;

    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @inject
     */
    protected $productRepository;

    /**
     * stockRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\StockRepository
     * @inject
     */
    protected $stockRepository;

    /**
     * BackendUserRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\BackendUserRepository
     * @inject
     */
    protected $backendUserRepository;

    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * logged in FrontendUser
     *
     * @var \RKW\RkwShop\Domain\Model\FrontendUser
     */
    protected $frontendUser = null;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * Persistence Manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * configurationManager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var  \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * Returns TYPO3 settings
     *
     * @return array
     */
    protected function getSettings()
    {
        $settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            'Rkwshop'
        );

        return $settings['plugin.']['tx_rkwshop.']['settings.'];
    }

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * Create Cart
     *
     * @return \RKW\RkwShop\Domain\Model\Cart  $cart
     *
     */
    public function createCart()
    {

        $cart = new Cart();

        $this->cartRepository->add($cart);
        $this->persistenceManager->persistAll();

        return $this->cart = $cart;

    }

    /**
     * @return \RKW\RkwShop\Domain\Model\Cart $cart
     */
    public function getCart()
    {
        $existingCart = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash($this->getFrontendUser());

        $cart = ($existingCart) ? $existingCart : $this->createCart();

        return $this->assignCart($cart);
    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Order $order
     */
    public function setCart(\RKW\RkwShop\Domain\Model\Order $order)
    {
        //  @todo: Check, if this does update the correct cart!
        $this->cart = $order;
    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Cart   $cart
     */
    public function assignCart(\RKW\RkwShop\Domain\Model\Cart $cart)
    {
        if ($frontendUser = $this->getFrontendUser()) {

            $cart->setFrontendUserSessionHash('');
            $cart->setFrontendUser($frontendUser);

        } else {

            $cart->setFrontendUserSessionHash($_COOKIE[FrontendUserAuthentication::getCookieName()]);

        }

        $this->cartRepository->update($cart);
        $this->persistenceManager->persistAll();

        return $cart;
    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Cart   $cart
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param                                   $amount
     */
    public function add(\RKW\RkwShop\Domain\Model\Cart $cart, \RKW\RkwShop\Domain\Model\Product $product, $amount)
    {
        $orderItem = $cart->containsProduct($product);

        if ($orderItem) {
            $this->changeQuantity($cart, $orderItem, $amount + $orderItem->getAmount());
        } else {
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setAmount($amount);
            $cart->addOrderItem($orderItem);

            $this->cartRepository->update($cart);
        }

    }

    /**
     * @param Cart     $cart
     * @param OrderItem $orderItem
     * @param int       $amount
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function changeQuantity(\RKW\RkwShop\Domain\Model\Cart $cart, OrderItem $orderItem, $amount)
    {

        if ($amount === 0) {
            $this->remove($orderItem, $cart);
        } else {
            $orderItem->setAmount($amount);

            $this->orderItemRepository->update($orderItem);
        }

    }

    /**
     * @param OrderItem $removableItem
     * @param \RKW\RkwShop\Domain\Model\Cart   $cart

     */
    public function remove(OrderItem $removableItem, \RKW\RkwShop\Domain\Model\Cart $cart)
    {
        $cart->removeOrderItem($removableItem);

        $this->cartRepository->update($cart);

        $this->orderItemRepository->remove($removableItem); //  sets deleted flag
        //  direktes Löschen wäre möglich - siehe https://www.typo3.net/forum/thematik/zeige/thema/116947/

        $this->persistenceManager->persistAll();
    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Cart $cart
     * @return \RKW\RkwShop\Domain\Model\Order $order
     */
    public function convertCart(Cart $cart)
    {
        $shippingAddress = $this->makeShippingAddress($cart);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);

        $order->setFrontendUser($cart->getFrontendUser());
        $order->setShippingAddress($shippingAddress);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach ($cart->getOrderItem() as $orderItem) {
            $order->addOrderItem($orderItem);
        }

        return $order;
    }

    /**
     * Create Order
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param \TYPO3\CMS\Extbase\Mvc\Request|null $request
     * @param bool $privacy
     * @return string
     */
    public function orderCart(\RKW\RkwShop\Domain\Model\Order $order, \TYPO3\CMS\Extbase\Mvc\Request $request = null, $privacy = false)
    {

        //  @todo: Wird Privacy überhaupt gebraucht, schließlich arbeiten wir hier nur mit bereits registrierten Benutzern? Nein, denn dies wird ja schon bei der eigentlichen Registrierung bestätigt!!!

        //  Check from orderService - how to implement it

        // check for shippingAddress
//        if (
//            (! $order->getShippingAddress())
//            || (! $order->getShippingAddress()->getAddress())
//            || (! $order->getShippingAddress()->getZip())
//            || (! $order->getShippingAddress()->getCity())
//        ){
//            throw new Exception('orderService.error.noShippingAddress');
//        }

        // cleanup & check orderItem
        $this->cleanUpOrderItemList($order);
        if (! count($order->getOrderItem()->toArray())) {
            throw new Exception('orderService.error.noOrderItem');
        }

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach($order->getOrderItem() as $orderItem) {

            if (
                (! $orderItem->getProduct() instanceof \RKW\RkwShop\Domain\Model\ProductSubscription)
                && ($orderItem->getProduct()->getRecordType() != '\RKW\RkwShop\Domain\Model\ProductSubscription')
            ){
                $stock = $this->getRemainingStockOfProduct($orderItem->getProduct());
                $stockPreOrder = $this->getPreOrderStockOfProduct($orderItem->getProduct());

                if ($orderItem->getAmount() > ($stock + $stockPreOrder)) {
                    throw new Exception('orderService.error.outOfStock');
                }
            }
        }

        // handling for existing and logged in users
        if ($this->getFrontendUser()) {

            if ($this->persistOrder($order)) {
                return 'orderService.message.created';
            }

        }

    }

    /**
     * persistOrder
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return bool
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function persistOrder(\RKW\RkwShop\Domain\Model\Order $order)
    {
        //  @todo: Check state
        // check order
//        if ($order->getStatus() > 0) {
//            throw new Exception('orderService.error.orderAlreadyPersisted');
//        }

        // save it
        $this->orderRepository->add($order);
        $this->persistenceManager->persistAll();

        //  @todo: $order->getFrontendUser() === $this->getFrontendUser()
        $frontendUser = $order->getFrontendUser();

        // send final confirmation mail to user
        $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_CREATED_USER, array($frontendUser, $order));

        // send mail to admins
        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $backendUsersList = [];
        $backendUsersForProductMap = [];
        foreach ($order->getOrderItem() as $orderItem) {

            $backendUsersForProduct = $this->getBackendUsersForAdminMails($orderItem->getProduct());
            $backendUsersList = array_merge($backendUsersList, $backendUsersForProduct);
            $tempBackendUserForProductMap = [];
            /** @var \RKW\RkwShop\Domain\Model\BackendUser $backendUser */
            foreach ($backendUsersForProduct as $backendUser) {
                if ($backendUser->getRealName()) {
                    $tempBackendUserForProductMap[] = $backendUser->getRealName();
                } else if ($backendUser->getEmail()) {
                    $tempBackendUserForProductMap[] = $backendUser->getEmail();
                }
            }
            $backendUsersForProductMap[$orderItem->getProduct()->getUid()] = implode(', ', $tempBackendUserForProductMap);
        }
        $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_CREATED_ADMIN, array(array_unique($backendUsersList), $order, $backendUsersForProductMap));

        $this->deleteCart($this->getCart());

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Saved order with uid %s of user with uid %s via signal-slot.', $order->getUid(), $frontendUser->getUid()));

        return true;

    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Cart $cart
     */
    protected function deleteCart(\RKW\RkwShop\Domain\Model\Cart $cart)
    {
        $this->cartRepository->remove($cart);
        $this->persistenceManager->persistAll();
    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Cart $cart
     * @return \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAdress
     */
    public function makeShippingAddress(\RKW\RkwShop\Domain\Model\Cart $cart)
    {
        $frontendUser = $cart->getFrontendUser();

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);

        $shippingAddress->setFrontendUser($frontendUser);
        $shippingAddress->setGender($frontendUser->getTxRkwregistrationGender());
        $shippingAddress->setFirstName($frontendUser->getFirstName());
        $shippingAddress->setLastName($frontendUser->getLastName());
        $shippingAddress->setCompany($frontendUser->getCompany());
        $shippingAddress->setAddress($frontendUser->getAddress());
        $shippingAddress->setZip($frontendUser->getZip());
        $shippingAddress->setCity($frontendUser->getCity());

        return $shippingAddress;

    }

    /**
     * Returns current logged in user object
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     */
    protected function getFrontendUser()
    {

        if (!$this->frontendUser) {

            $frontendUser = $this->frontendUserRepository->findByUidNoAnonymous($this->getFrontendUserId());
            if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser) {
                $this->frontendUser = $frontendUser;
            }
        }

        return $this->frontendUser;
        //===
    }



    /**
     * Id of logged User
     *
     * @return integer|null
     */
    protected function getFrontendUserId()
    {
        // is $GLOBALS set?
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            return intval($GLOBALS['TSFE']->fe_user->user['uid']);
            //===
        }

        return null;
        //===
    }


    /**
     * Get remaining stock of product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getRemainingStockOfProduct (\RKW\RkwShop\Domain\Model\Product $product)
    {
        if (
            ($product->getProductBundle())
            && (! $product->getProductBundle()->getAllowSingleOrder())
        ){
            $product = $product->getProductBundle();
        }

        $orderedSum = $this->orderItemRepository->getOrderedSumByProductAndPreOrder($product);
        $stockSum = $this->stockRepository->getStockSumByProductAndPreOrder($product);

        $remainingStock = intval($stockSum) - (intval($orderedSum) + intval($product->getOrderedExternal()));
        return (($remainingStock > 0) ? $remainingStock : 0);
    }

    /**
     * Get pre-order stock of product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getPreOrderStockOfProduct (\RKW\RkwShop\Domain\Model\Product $product)
    {
        if (
            ($product->getProductBundle())
            && (! $product->getProductBundle()->getAllowSingleOrder())
        ){
            $product = $product->getProductBundle();
        }

        $orderedSum = $this->orderItemRepository->getOrderedSumByProductAndPreOrder($product, true);
        $stockSum = $this->stockRepository->getStockSumByProductAndPreOrder($product, true);

        $preOrderStock = intval($stockSum) - intval($orderedSum);
        return (($preOrderStock > 0) ? $preOrderStock : 0);
    }

    /**
     * Clean up order product list
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return void
     */
    public function cleanUpOrderItemList (\RKW\RkwShop\Domain\Model\Order $order)
    {

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach ($order->getOrderItem()->toArray() as $orderItem) {
            if (! $orderItem->getAmount()) {
                $order->removeOrderItem($orderItem);
            }
        }
    }

    /**
     * Get all BackendUsers for sending admin mails
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return array <\RKW\RkwShop\Domain\Model\BackendUser> $backendUsers
     */
    public function getBackendUsersForAdminMails (\RKW\RkwShop\Domain\Model\Product $product)
    {

        $backendUsers = [];
        $settings = $this->getSettings();
        if (! $settings['disableAdminMails']) {

            $productTemp = $product;
            if ($product->getProductBundle()) {
                $productTemp  = $product->getProductBundle();
            }

            // go through ObjectStorage
            foreach ($productTemp->getBackendUser() as $backendUser) {
                if ((\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($backendUser->getEmail()))) {
                    $backendUsers[] = $backendUser;
                }
            }

            // get field for alternative e-emails
            if ($email = $productTemp->getAdminEmail()) {

                /** @var \RKW\RkwShop\Domain\Model\BackendUser $backendUser */
                $backendUser = $this->backendUserRepository->findOneByEmail($email);
                if (
                    ($backendUser)
                    && (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($backendUser->getEmail()))
                ) {
                    $backendUsers[] = $backendUser;
                }
            }

            // fallback-handling
            if (
                (count($backendUsers) < 1)
                && ($fallbackBeUser = $settings['fallbackBackendUserForAdminMails'])
            ) {

                /** @var \RKW\RkwShop\Domain\Model\BackendUser $beUser */
                $backendUser = $this->backendUserRepository->findOneByUsername($fallbackBeUser);
                if (
                    ($backendUser)
                    && (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($backendUser->getEmail()))
                ) {
                    $backendUsers[] = $backendUser;
                }
            }
        }

        return $backendUsers;
    }


}