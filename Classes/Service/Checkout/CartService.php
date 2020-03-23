<?php

namespace RKW\RkwShop\Service\Checkout;

use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
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
     * cart
     *
     * @var \RKW\RkwShop\Domain\Model\Order
     */
    protected $cart;

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
     * @return \RKW\RkwShop\Domain\Model\Order $order
     */
    public function getCart()
    {

        $this->cart = $this->orderRepository->findByFrontendUserSessionHash()->getFirst();

        return ($this->cart) ? $this->cart : $this->createCart();
    }

    /**
     * Create Cart
     *
     * @return \RKW\RkwShop\Domain\Model\Order  $order
     *
     */
    public function createCart()
    {

        $order = new Order();
        $order->setFrontendUserSessionHash($_COOKIE[FrontendUserAuthentication::getCookieName()]);

        $this->orderRepository->add($order);
        $this->persistenceManager->persistAll();

        return $order;

    }

    /**
     * @param \RKW\RkwShop\Domain\Model\Product $product
     * @param                                   $amount
     */
    public function add(\RKW\RkwShop\Domain\Model\Product $product, $amount)
    {
        $this->cart = $this->getCart();

        $orderItem = $this->cart->containsProduct($product);

        if ($orderItem) {
            $this->changeQuantity($orderItem, $amount + $orderItem->getAmount());
        } else {
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setAmount($amount);
            $this->cart->addOrderItem($orderItem);

            $this->orderRepository->update($this->cart);
        }

    }

    /**
     * @param int       $amount
     * @param OrderItem $orderItem
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function changeQuantity(OrderItem $orderItem, $amount)
    {
        $orderItem->setAmount($amount);

        $this->orderItemRepository->update($orderItem);
    }

    /**
     * @param OrderItem $removableItem
     */
    public function remove(OrderItem $removableItem)
    {
        $this->getCart();

        $this->cart->removeOrderItem($removableItem);

        $this->orderRepository->update($this->cart);

        $this->orderItemRepository->remove($removableItem); //  sets deleted flag
        //  direktes Löschen wäre möglich - siehe https://www.typo3.net/forum/thematik/zeige/thema/116947/

        $this->persistenceManager->persistAll();
    }

}