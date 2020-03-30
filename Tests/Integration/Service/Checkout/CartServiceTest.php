<?php

namespace RKW\RkwShop\Tests\Integration\Service\Checkout;

use RKW\RkwBasics\Helper\Common;
use TYPO3\CMS\Extbase\Mvc\Request;
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwRegistration\Tools\Authentication;
use RKW\RkwShop\Service\Checkout\CartService;
use RKW\RkwShop\Domain\Model\ShippingAddress;
use RKW\RkwShop\Domain\Repository\CartRepository;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
 * CartServiceTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartServiceTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_shop',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwShop\Service\Checkout\CartService
     */
    private $subject = null;

    /**
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\CartRepository
     */
    private $cartRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     */
    private $orderItemRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        /*$this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Stock.xml');
*/

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Functional/Orders/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );


        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->subject = $this->objectManager->get(CartService::class);

        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        $this->cartRepository = $this->objectManager->get(CartRepository::class);

        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);

    }

    /**
     * @test
     */
    public function getCartCreatesANewCartForSessionUserIfACartDoesNotAlreadyExist ()
    {

        /**
         * Scenario:
         *
         * Given a guest tries to create a cart
         * Given he has not created a cart yet
         * When I get the cart
         * Then a new cart is created
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check10.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $carts = $this->cartRepository->findAll();

        static::assertEquals(0, count($carts));

        $this->subject->getCart();

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals(1, count($cartAfter));
        static::assertEquals('1', $cartAfter->getUid());

    }

    /**
     * @test
     */
    public function getCartCreatesANewCartForFrontendUserIfACartDoesNotAlreadyExistAndFrontendUserIsAlreadyLoggedIn ()
    {

        /**
         * Scenario:
         *
         * Given a logged in user tries to create a cart
         * Given he has not created a cart yet
         * When I get the cart
         * Then a new cart is created
         * And can be retrieved by frontend user
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check11.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $carts = $this->cartRepository->findAll();

        static::assertEquals(0, count($carts));

        $this->subject->getCart();

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash($frontendUser);

        static::assertEquals(1, count($cartAfter));
        static::assertEquals('1', $cartAfter->getUid());

    }

    /**
     * @test
     */
    public function getCartReturnsCartForSessionUser ()
    {

        /**
         * Scenario:
         *
         * Given a guest adds a product to the cart
         * Given he has already created a cart
         * When I try to get the cart
         * Then the existing cart is returned
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check15.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $existingCart */
        $existingCart = $this->cartRepository->findByUid(1);

        static::assertEquals(1, count($existingCart));

        /** @var \RKW\RkwShop\Domain\Model\Cart $cart */
        $cart = $this->subject->getCart();

        static::assertSame($existingCart, $cart);

    }

    /**
     * @test
     */
    public function addAddsAProductToCartForSessionUser()
    {

        /**
         * Scenario:
         *
         * Given my cart does already exist
         * When I add a product
         * Then the existing cart is updated
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check20.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $cartBefore = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartBefore->getUid());
        static::assertEquals(0, count($cartBefore->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $selectedProduct = $this->productRepository->findByUid(1);

        $this->subject->add($cartBefore, $selectedProduct, $amount = 1);

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartAfter->getUid());
        static::assertEquals(1, count($cartAfter->getOrderItem()));

    }

    /**
     * @test
     */
    public function addSameProductUpdatesQuantityForProductPlusOne()
    {

        /**
         * Scenario:
         *
         * Given my cart does already exist
         * Given my cart contains 1 item of a product
         * When I add 1 additional item of this product
         * Then the existing cart contains 2
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check30.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $cartBefore = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartBefore->getUid());
        static::assertEquals(1, count($cartBefore->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $selectedProduct = $this->productRepository->findByUid(1);

        $this->subject->add($cartBefore, $selectedProduct, $amount = 1);

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals(1, count($cartAfter));
        static::assertEquals('1', $cartAfter->getUid());
        static::assertEquals(1, $cartAfter->getOrderItem()->count());

        $orderItems = $cartAfter->getOrderItem();
        $orderItems->rewind();

        static::assertEquals(2, $orderItems->current()->getAmount());

    }

    /**
     * @test
     */
    public function removeRemovesOrderItemFromCartForSessionUser()
    {

        /**
         * Scenario:
         *
         * Given my cart does already exist
         * Given 2 order items are already added
         * When I remove the second order item
         * Then the cart will be updated
         * And will contain only the first order item
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check40.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $cartBefore = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartBefore->getUid());
        static::assertEquals(2, count($cartBefore->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $removableItem = $this->orderItemRepository->findByUid(2);

        $this->subject->remove($removableItem, $cartBefore);

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartAfter->getUid());
        static::assertEquals(1, count($cartAfter->getOrderItem()));

        $orderItems = $cartAfter->getOrderItem();
        $orderItems->rewind();

        static::assertSame($this->orderItemRepository->findByUid(1), $orderItems->current());

    }

    /**
     * @test
     */
    public function changeQuantityOfOrderItemUpdatesQuantity()
    {

        /**
         * Scenario:
         *
         * Given my cart does already exist
         * Given my cart contains 1 item of a product
         * When I change the quantity of this item to 5
         * Then the existing cart should show a quantity of 5 for this existing product
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check50.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $cartBefore = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals(1, count($cartBefore));
        static::assertEquals('1', $cartBefore->getUid());
        static::assertEquals(1, count($cartBefore->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $selectedOrderItems = $cartBefore->getOrderItem();
        $selectedOrderItems->rewind();

        $selectedOrderItem = $selectedOrderItems->current();

        $this->subject->changeQuantity($cartBefore, $selectedOrderItem, $amount = 5);

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartAfter->getUid());
        static::assertEquals(1, $cartAfter->getOrderItem()->count());

        $orderItems = $cartAfter->getOrderItem();
        $orderItems->rewind();

        static::assertEquals(5, $orderItems->current()->getAmount());

    }

    /**
     * @test
     */
    public function changeQuantityOfOrderItemToZeroRemovesOrderItemFromCart()
    {

        /**
         * Scenario:
         *
         * Given my cart does already exist
         * Given my cart contains 1 item of a product
         * When I change the quantity of this item to 0
         * Then the order item will be removed from the cart
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check60.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $cartBefore = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartBefore->getUid());
        static::assertEquals(1, count($cartBefore->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $selectedOrderItems = $cartBefore->getOrderItem();
        $selectedOrderItems->rewind();

        $selectedOrderItem = $selectedOrderItems->current();

        $this->subject->changeQuantity($cartBefore, $selectedOrderItem, $amount = 0);

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals('1', $cartAfter->getUid());
        static::assertEquals(0, $cartAfter->getOrderItem()->count());

    }

    /**
     * @test
     */
    public function convertCartReturnsAnOrder()
    {

        /**
         * Scenario:
         *
         * Given my cart does already exist
         * Given I am logged in as a frontend user
         * When I want to confirm my cart
         * Then an order is returned
         * And my shipping address will be set to my billing address
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check70.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cart */
        $cart = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals(1, count($cart));
        static::assertEquals('1', $cart->getUid());

        $order = $this->subject->convertCart($cart);

        static::assertEquals($cart->getFrontendUser(), $order->getFrontendUser());
        static::assertEquals($order->getFrontendUser()->getCity(), $order->getShippingAddress()->getCity());
        static::assertEquals($cart->getOrderItem(), $order->getOrderItem());
        static::assertEquals(1, $order->getShippingAddressSameAsBillingAddress());

    }

    /**
     * @test
     */
    public function createCartSetsFrontendUserOnCartIfAlreadyLoggedIn()
    {

        /**
         * Scenario:
         *
         * Given I am logged in as a frontend user
         * When I initialize a cart
         * Then I am set on the corresponding cart as a frontend user
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check80.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartBefore */
        $cartBefore = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash($frontendUser);

        static::assertEquals(0, count($cartBefore));

        $this->subject->getCart();

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartAfter */
        $cartAfter = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash($frontendUser);

        static::assertEquals(1, count($cartAfter));
        static::assertEquals('1', $cartAfter->getUid());
        static::assertEquals('', $cartAfter->getFrontendUserSessionHash());

    }

    //  @todo: Timestamp setzen, wenn die Bestellung tatsächlich abgeschickt wird!!!! Damit kann dann zwischen Warenkörben und Bestellungen unterschieden werden.

    //  @todo: Die nachfolgenden Checks müssen über einen Validator erfolgen!????
    //  siehe OrderServiceTest
    public function orderCartChecksForTermsIfNotLoggedIn() {
        //  @todo: Nicht notwendig, da User bereits angemeldet und zugeordnet!?
    }

    public function createOrderChecksForTermsIfUserNotRegistered() {}
    public function createOrderChecksForPrivacyTermsIfNotLoggedIn() {}
    public function createOrderChecksForPrivacyTermIfUserIsLoggedIn() {}
    public function createOrderChecksForValidEmail() {}
    public function createOrderChecksForValidShippingAddress() {}

    /**
     * @test
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
    public function orderCartChecksForOrderItems()
    {

        /**
         * Scenario:
         *
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given no product is ordered
         * When I make an order
         * Then an error is thrown
         */
        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setEmail('email@rkw.de');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setAddress('Emmenthaler Allee 15');
        $shippingAddress->setZip('12345');
        $shippingAddress->setCity('Gauda');
        $order->setShippingAddress($shippingAddress);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderService.error.noOrderItem');

        $this->subject->orderCart($order, $request, null, true);

    }

    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \Exception
     */
    public function orderCartChecksAllOrderItemAmountsAreGreaterThanZero()
    {
        /**
         * Scenario:
         *
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given a orderItem with product is added
         * Given a product is ordered with amount less than one
         * When I make an order
         * Then an noOrderItem- error is thrown
         */
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check120.xml');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setEmail('email@rkw.de');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setAddress('Emmenthaler Allee 15');
        $shippingAddress->setZip('12345');
        $shippingAddress->setCity('Gauda');
        $order->setShippingAddress($shippingAddress);

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = GeneralUtility::makeInstance(OrderItem::class);
        $orderItem->setProduct($product);
        $orderItem->setAmount(0);
        $order->addOrderItem($orderItem);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderService.error.noOrderItem');

        $this->subject->orderCart($order, $request, null);

    }


    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \Exception
     */
    public function orderCartChecksForStockOfProduct() {
        /**
         * Scenario:
         *
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given an product is ordered which is out of stock
         * When I make an order
         * Then an outOfStock- error is thrown
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check150.xml');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setEmail('email@rkw.de');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setAddress('Emmenthaler Allee 15');
        $shippingAddress->setZip('12345');
        $shippingAddress->setCity('Gauda');
        $order->setShippingAddress($shippingAddress);

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = GeneralUtility::makeInstance(OrderItem::class);
        $orderItem->setProduct($product);
        $orderItem->setAmount(10);
        $order->addOrderItem($orderItem);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderService.error.outOfStock');

        $this->subject->orderCart($order, $request, null);

    }

    public function createOrderChecksForPersistedOrders() {

        //  @todo: Wie prüfe ich auf eine bereits abgesendete Bestellung?

    }

    public function createOrderSavesOrderIfProductOutOfStockCanBePreOrdered() {}
    public function createOrderSavesOrderIfProductOutOfStockIsSubscription() {}
    public function createOrderSavesOrderIfUserIsLoggedIn() {}
    public function createOrderCreatesRegistrationIfUserIsNotLoggedIn() {}

    public function removeAllOrdersOfFrontendUserSignalSlotRemovesAllOpenOrdersOfGivenUser() {}
    public function getRemainingStockSubstractsOrderedAndOrderedExternalFromStock() {}
    public function getRemainingStockReturnsZeroIfAmountOfOrdersIsGreaterThanStock() {}
    public function getRemainingStockReturnsStockOfProductBundle() {}
    public function getRemainingStockReturnsStockOfProductIfSingleOrderIsAllowed() {}
    public function getPreOrderStockSubstractsOrderedAndOrderedExternalFromStock() {}
    public function getPreOrderStockReturnsZeroIfAmountOfOrdersIsGreaterThanStock() {}
    public function getPreOrderStockReturnsStockOfProductBundle() {}
    public function getPreOrderStockReturnsStockOfProductIfSingleOrderIsAllowed() {}
    public function cleanUpOrderItemListRemovesOrderItemObjectsWithAmountLowerThanOne() {}
    public function getBackendUsersForAdminMailsAddsAdminFromTextField() {}
    public function getBackendUsersForAdminMailsChecksForValidEmails() {}
    public function getBackendUsersForAdminMailsReturnsFallbackAdmin() {}
    public function getBackendUsersForAdminMailsReturnsAdminsOfProductBundle() {}

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}