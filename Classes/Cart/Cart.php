<?php

namespace RKW\RkwShop\Cart;

use \RKW\RkwShop\Exception;
use Doctrine\Common\Util\Debug;
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
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
 * Class Cart
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Cart implements \TYPO3\CMS\Core\SingletonInterface
{

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
     * Initialize Cart
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param int $amount
     * @param bool $remove
     *
     * @return void
     *
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
    public function initialize(\RKW\RkwShop\Domain\Model\Product $product, $amount = 0, $remove = false)
    {

        if ($this->get()) {
            $this->update($product, $amount, $remove);
        } else {
            $this->create($product, $amount);
        }

    }

    /**
     * Create Cart
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param int $amount
     *
     * @return void
     *
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
    public function create(\RKW\RkwShop\Domain\Model\Product $product, $amount = 0)
    {

        $order = new Order();
        $order->setFrontendUserSessionHash($_COOKIE[FrontendUserAuthentication::getCookieName()]);

        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setAmount($amount);

        $order->addOrderItem($orderItem);

        $this->orderRepository->add($order);
        $this->persistenceManager->persistAll();

        // cleanup & check cartItem
        $this->cleanUpOrderItemList($order);
        if (! count($order->getOrderItem()->toArray())) {
            throw new Exception('orderManager.error.noOrderItem');
        }

    }

    /**
     * Update Cart
     *
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param int $amount
     * @param bool $remove
     *
     * @return void
     *
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
    public function update(\RKW\RkwShop\Domain\Model\Product $product, $amount = 0, $remove = false)
    {

        $order = $this->get();

        //  check, if there are existing orderitems
        $orderItems = $order->getOrderItem();

        if ($remove && $orderItems->count() > 0) {

            $removableItem = $this->containsOrderItem($product, $orderItems);

            $this->removeOrderItem($order, $removableItem);

            //  Check, if order is now empty
            //  If order is empty, delete the order too

        } else {

            //  Check, if product already exists?
            //  If yes and remove === false
            //  update amount

            $existingItem = $this->containsOrderItem($product, $orderItems);

            if ($existingItem) {

                $this->updateOrderItem($amount, $existingItem);

            } else {

                $this->addOrderItem($product, $amount, $order);

            }

        }

        //  on remove please check, if order does contain any order items at all
        //  if empty, you may remove it, too

        $this->persistenceManager->persistAll();

    }

    /**
     * @return \RKW\RkwShop\Domain\Model\Order $order
     */
    public function get()
    {
        return $this->orderRepository->findByFrontendUserSessionHash()->getFirst();
    }

    /**
     * Clean up cart product list
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
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param                                   $amount
     * @param Order                             $order
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function addOrderItem(\RKW\RkwShop\Domain\Model\Product $product, $amount, Order $order)
    {
        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setAmount($amount);

        $order->addOrderItem($orderItem);

        $this->orderRepository->update($order);
    }

    /**
     * @param           $amount
     * @param OrderItem $existingItem
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function updateOrderItem($amount, OrderItem $existingItem)
    {
        $existingItem->setAmount($amount + $existingItem->getAmount());

        $this->orderItemRepository->update($existingItem);
    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Product            $product
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $orderItems
     * @return object|OrderItem
     */
    protected function containsOrderItem(\RKW\RkwShop\Domain\Model\Product $product, \TYPO3\CMS\Extbase\Persistence\ObjectStorage $orderItems)
    {
        if ($orderItems->count() > 0) {
            /** @var \RKW\RkwShop\Domain\Model\OrderItem $existingItem */
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProduct() === $product) {
                    $existingItem = $orderItem;
                }
            }
        }

        return $existingItem;
    }

    /**
     * @param Order     $order
     * @param OrderItem $removableItem
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function removeOrderItem(Order $order, OrderItem $removableItem)
    {
        $order->removeOrderItem($removableItem);

        $this->orderRepository->update($order);

        $this->orderItemRepository->remove($removableItem); //  sets deleted flag
        //  direktes Löschen wäre möglich - siehe https://www.typo3.net/forum/thematik/zeige/thema/116947/


    }


}