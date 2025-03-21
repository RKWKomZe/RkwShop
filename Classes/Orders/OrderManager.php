<?php

namespace RKW\RkwShop\Orders;

use Madj2k\FeRegister\Domain\Model\FrontendUser;
use Madj2k\FeRegister\Registration\FrontendUserRegistration;
use RKW\RkwShop\Domain\Repository\BackendUserRepository;
use RKW\RkwShop\Domain\Repository\CategoryRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\StockRepository;
use RKW\RkwShop\Exception;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

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
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?OrderRepository $orderRepository = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?OrderItemRepository $orderItemRepository = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?ProductRepository $productRepository = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\StockRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?StockRepository $stockRepository = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\CategoryRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?CategoryRepository $categoryRepository;


    /**
     * @var \RKW\RkwShop\Domain\Repository\BackendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?BackendUserRepository $backendUserRepository = null;


    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?Dispatcher $signalSlotDispatcher = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?PersistenceManager $persistenceManager = null;


    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?ConfigurationManagerInterface $configurationManager = null;


    /**
     * @var  \TYPO3\CMS\Extbase\Object\ObjectManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?ObjectManager $objectManager = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected ?Logger $logger = null;


    /**
     * @param \RKW\RkwShop\Domain\Repository\OrderRepository $orderRepository
     */
    public function injectOrderRepository(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\OrderItemRepository $orderItemRepository
     */
    public function injectMailRepository(OrderItemRepository $orderItemRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\ProductRepository $productRepository
     */
    public function injectProductRepository(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\StockRepository $stockRepository
     */
    public function injectStockRepository(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\BackendUserRepository $backendUserRepository
     */
    public function injectBackendUserRepository(BackendUserRepository $backendUserRepository)
    {
        $this->backendUserRepository = $backendUserRepository;
    }


    /**
     * @param \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
     */
    public function injectDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }


    /**
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }


    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManagerInterface(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }


    /**
     * Create Order
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param \TYPO3\CMS\Extbase\Mvc\Request|null $request
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser|null $frontendUser
     * @param int $targetGroup
     * @param bool $digitalOnly
     * @return string
     * @throws Exception
     * @throws \Madj2k\FeRegister\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function createOrder (
        \RKW\RkwShop\Domain\Model\Order $order,
        \TYPO3\CMS\Extbase\Mvc\Request $request = null,
        \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser = null,
        int $targetGroup = 0,
        bool $digitalOnly = false
   ): string {

        // check given e-mail
        if (! \Madj2k\FeRegister\Utility\FrontendUserUtility::isEmailValid($order->getEmail())) {
            throw new Exception('orderManager.error.invalidEmail');
        }

        // check targetGroup
        if ($targetGroup) {
            $order->addTargetGroup($this->categoryRepository->findByUid($targetGroup));
        }

        if (! $digitalOnly) {
            // check for shippingAddress
            if (
                (!$order->getShippingAddress())
                || (!$order->getShippingAddress()->getAddress())
                || (!$order->getShippingAddress()->getZip())
                || (!$order->getShippingAddress()->getCity())
            ) {
                throw new Exception('orderManager.error.noShippingAddress');
            }
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
            \Madj2k\FeRegister\DataProtection\ConsentHandler::add($request, $frontendUser, $order, 'new order');

            return 'orderManager.message.created';
        }


        // handling for new users
        // register new user or simply send opt-in to existing user
        /** @var \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FrontendUser::class);
        $frontendUser->setEmail($order->getEmail());

        /** @var \Madj2k\FeRegister\Registration\FrontendUserRegistration $registration */
        $registration = $this->objectManager->get(FrontendUserRegistration::class);
        $registration->setFrontendUser($frontendUser)
            ->setData($order)
            ->setCategory('rkwShop')
            ->setRequest($request)
            ->startRegistration();

        return 'orderManager.message.createdOptIn';

    }


    /**
     * saveOrder
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @return bool
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function saveOrder (\RKW\RkwShop\Domain\Model\Order $order, \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser): bool
    {

        // check order
        if (! $order->_isNew()) {
            throw new Exception('orderManager.error.orderAlreadyPersisted');
        }

        // check frontendUser
        if ($frontendUser->_isNew()) {
            throw new Exception('orderManager.error.frontendUserNotPersisted');
        }

        // add frontendUser to order and shippingAddress
        //  @todo: FrontendUser has no firstname or last name etc., when added through order optInRequest?!
        $order->setFrontendUser($frontendUser);

        if ($order->getShippingAddress()) {
            $order->getShippingAddress()->setFrontendUser($frontendUser);
        }

        //  @todo: Temporary, until rkw_soap delivers shippedTstamp from AVS
        $order->setShippedTstamp(time());

        // save it
        $this->orderRepository->add($order);
        $this->persistenceManager->persistAll();

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

        $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Saved order with uid %s of user with uid %s via signal-slot.', $order->getUid(), $frontendUser->getUid()));
        return true;
    }


    /**
     * Intermediate function for saving of orders - used by SignalSlot
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \Madj2k\FeRegister\Domain\Model\OptIn $optIn
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderSignalSlot(\Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser, \Madj2k\FeRegister\Domain\Model\OptIn $optIn): void
    {
        // get order from registration
        if (
            ($order = $optIn->getData())
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
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function removeAllOrdersOfFrontendUserSignalSlot(\Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser): void
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
                $this->signalSlotDispatcher->dispatch(__CLASS__, self::SIGNAL_AFTER_ORDER_DELETED_ADMIN, array(array_unique($backendUsersList), $frontendUser, $order, $backendUsersForProductMap));
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
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function getRemainingStockOfProduct (\RKW\RkwShop\Domain\Model\Product $product): int
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
     * Check, if the product is still deliverable
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return bool
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function isProductDeliverable (\RKW\RkwShop\Domain\Model\Product $product): bool
    {
        if ($product->getProductBundle()) {
            $product = $product->getProductBundle();
        }

        return ($product->getDeliveryEnd() > 0 && $product->getDeliveryEnd() < time()) ? false : true;
    }


    /**
     * Get pre-order stock of product
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @return int
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function getPreOrderStockOfProduct (\RKW\RkwShop\Domain\Model\Product $product): int
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
    public function cleanUpOrderItemList (\RKW\RkwShop\Domain\Model\Order $order): void
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
    public function getBackendUsersForAdminMails (\RKW\RkwShop\Domain\Model\Product $product): array
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
    protected function getSettings(): array
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
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }


}
