<?php

namespace RKW\RkwShop\Orders;

use \RKW\RkwShop\Exception;

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
 * Class OrderManager
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderManager implements \TYPO3\CMS\Core\SingletonInterface
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
    const SIGNAL_AFTER_ORDER_CREATED_USER = 'afterOrderCreatedUser';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_DELETED_ADMIN = 'afterOrderDeletedAdmin';

    /**
     * Signal name for use in ext_localconf.php
     *
     * @const string
     */
    const SIGNAL_AFTER_ORDER_DELETED_USER = 'afterOrderDeletedUser';


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
     * Create Order
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param \TYPO3\CMS\Extbase\Mvc\Request|null $request
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     * @param bool $terms
     * @param bool $privacy
     * @return string
     * @throws \RKW\RkwShop\Exception
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function createOrder (\RKW\RkwShop\Domain\Model\Order $order, \TYPO3\CMS\Extbase\Mvc\Request $request = null, \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser = null, $terms = false, $privacy = false)
    {

        // check terms if user is not logged in
        if (
            (! $terms)
            && (
                (! $frontendUser)
                || ($frontendUser->_isNew())
            )
        ) {
            throw new Exception('orderManager.error.acceptTerms');
        }

        // check privacy flag
        if (! $privacy) {
            throw new Exception('orderManager.error.acceptPrivacy');
        }

        // check given e-mail
        if (! \RKW\RkwRegistration\Tools\Registration::validEmail($order->getEmail())) {
            throw new Exception('orderManager.error.invalidEmail');
        }

        // check for shippingAddress
        if (! $order->getShippingAddress()) {
            throw new Exception('orderManager.error.noShippingAddress');
        }


        // cleanup & check orderItem
        $this->cleanUpOrderItemList($order);
        if (! count($order->getOrderItem()->toArray())) {
            throw new Exception('orderManager.error.noOrderItem');
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
                    throw new Exception('orderManager.error.outOfStock');
                }
            }
        }

        // handling for existing and logged in users
        if (
            ($frontendUser)
            && (! $frontendUser->_isNew())
        ) {

            /// simply save order
            $this->saveOrder($order, $frontendUser);

            // add privacy info
            \RKW\RkwRegistration\Tools\Privacy::addPrivacyData($request, $frontendUser, $order, 'new order');

            return 'orderManager.message.created';
        }


        // handling for new users
        // register new user or simply send opt-in to existing user
        /** @var \RKW\RkwRegistration\Tools\Registration $registration */
        $registration = $this->objectManager->get('RKW\\RkwRegistration\\Tools\\Registration');
        $registration->register(
            array(
                'email' => $order->getEmail(),
            ),
            false,
            $order,
            'rkwShop',
            $request
        );

        return 'orderManager.message.createdOptIn';

    }


    /**
     * saveOrder
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return bool
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrder (\RKW\RkwShop\Domain\Model\Order $order, \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {

        // check order
        if (! $order->_isNew()) {
            throw new Exception('orderManager.error.orderAlreadyPersisted');
        }

        // check frontendUser
        if ($frontendUser->_isNew()) {
            throw new Exception('orderManager.error.frontendUserNotPersisted');
        }

        // check shippingAddress
        if (! $order->getShippingAddress()) {
            throw new Exception('orderManager.error.noShippingAddress');
        }

        // cleanup & check orderItem
        $this->cleanUpOrderItemList($order);
        if (! count($order->getOrderItem()->toArray())) {
            throw new Exception('orderManager.error.noOrderItem');
        }

        // add frontendUser to order and shippingAddress
        $order->setFrontendUser($frontendUser);
        $order->getShippingAddress()->setFrontendUser($frontendUser);

        // save it
        $this->orderRepository->add($order);
        $this->persistenceManager->persistAll();

        // send final confirmation mail to user
        $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_CREATED_USER, array($frontendUser, $order));

        // send mail to admins
        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach ($order->getOrderItem() as $orderItem) {
            $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_CREATED_ADMIN, array($this->getBackendUsersForAdminMails($orderItem->getProduct()), $order));
        }

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Saved order with uid %s of user with uid %s via signal-slot.', $order->getUid(), $frontendUser->getUid()));
        return true;
    }


    /**
     * Intermediate function for saving of orders - used by SignalSlot
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\Registration $registration
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderSignalSlot(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser, \RKW\RkwRegistration\Domain\Model\Registration $registration)
    {
        // get order from registration
        if (
            ($order = $registration->getData())
            && ($order instanceof \RKW\RkwShop\Domain\Model\Order)
        ) {

            try {
                $this->saveOrder($order, $frontendUser);

            } catch (\RKW\RkwShop\Exception $exception) {
                // do nothing
            }
        }
    }



    /**
     * Removes all open orders of a FE-User - used by SignalSlot
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function removeAllOrdersOfFrontendUserSignalSlot(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {

        $orders = $this->orderRepository->findByFrontendUser($frontendUser);
        if ($orders) {

            /** @var \RKW\RkwShop\Domain\Model\Order $order $order */
            foreach ($orders as $order) {

                // delete order
                $this->orderRepository->remove($order);
                $this->persistenceManager->persistAll();

                // send final confirmation mail to user
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_DELETED_USER, array($frontendUser, $order));

                // send mail to admins
                /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
                foreach ($order->getOrderItem() as $orderItem) {
                    $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_DELETED_ADMIN, array($this->getBackendUsersForAdminMails($orderItem->getProduct()), $order));
                }
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Deleted order with uid %s of user with uid %s via signal-slot.', $order->getUid(), $frontendUser->getUid()));
            }
        }
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


}