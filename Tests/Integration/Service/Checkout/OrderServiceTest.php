<?php
namespace RKW\RkwShop\Tests\Integration\Service\Checkout;

use RKW\RkwBasics\Helper\Common;
use TYPO3\CMS\Extbase\Mvc\Request;
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwRegistration\Tools\Authentication;
use RKW\RkwShop\Domain\Model\ShippingAddress;
use RKW\RkwShop\Service\Checkout\OrderService;
use RKW\RkwShop\Domain\Repository\CartRepository;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwShop\Domain\Repository\ShippingAddressRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;

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
 * OrderServiceTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderServiceTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Service\Checkout\OrderService
     */
    private $subject = null;

    /**
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ShippingAddressRepository
     */
    private $shippingAddressRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\CartRepository
     */
    private $cartRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     */
    private $orderItemRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository
     */
    private $privacyRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    private $registrationRepository;

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

        /*$this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Stock.xml');
*/

        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Integration/Service/Checkout/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->subject = $this->objectManager->get(OrderService::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->shippingAddressRepository = $this->objectManager->get(ShippingAddressRepository::class);
        $this->cartRepository = $this->objectManager->get(CartRepository::class);
        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);
        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->privacyRepository = $this->objectManager->get(PrivacyRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);


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
    public function createOrderChecksForValidShippingAddress ()
    {

        /**
         * Scenario:
         *
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given shipping address has no city given
         * When I make an order
         * Then an noShippingAddress-error is thrown
         */
        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setAddress('Emmenthaler Allee 15');
        $shippingAddress->setZip('12345');
        $order->setShippingAddress($shippingAddress);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderService.error.noShippingAddress');

        $this->subject->createOrder($order, $request, null, true, true);

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
    public function createOrderChecksForStockOfProduct ()
    {

        /**
         * Scenario:
         *
         * Given I'm not logged in
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given an product is ordered which is out of stock
         * When I make an order
         * Then an outOfStock- error is thrown
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check50.xml');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);

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

        $this->subject->createOrder($order, $request, null, true, true);

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
    public function createOrderChecksForPersistedOrders ()
    {

        /**
         * Scenario:
         *
         * Given I'm not logged in
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given an product is ordered which is out of stock
         * When I make an order
         * Then an outOfStock- error is thrown
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check80.xml');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderService.error.orderAlreadyPersisted');

        $this->subject->createOrder($order, $request, $frontendUser, true, true);

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
    public function createOrderSavesOrderIfProductOutOfStockCanBePreOrdered ()
    {

        /**
         * Scenario:
         *
         * Given I'm not logged in
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given an product is ordered which is out of stock
         * Given that ordered product can be pre-ordered
         * When I make an order
         * Then the order is saved as registration
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check60.xml');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);

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

        static::assertEquals(
            'orderService.message.createdOptIn',
            $this->subject->createOrder($order, $request, null, true, true)
        );

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
    public function persistOrderSavesOrder()
    {

        /**
         * Scenario:
         *
         * Given I'm logged in
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given an product is ordered which is out of stock
         * Given that ordered product can be pre-ordered
         * When I make an order
         * Then the order is saved
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check65.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setFrontendUser($frontendUser);
        $order->setRemark('Was ist hier los?');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setFrontendUser($frontendUser);
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

        $this->subject->persistOrder($order, $frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $orderDb */
        $orderDb = $this->orderRepository->findByUid(1);

        $orderDb->getOrderItem()->rewind();
        $order->getOrderItem()->rewind();

        static::assertInstanceOf('\RKW\RkwShop\Domain\Model\Order', $orderDb);
        static::assertEquals($order->getRemark(), $orderDb->getRemark());

        static::assertEquals($frontendUser->getUid(), $orderDb->getFrontendUser()->getUid());
        static::assertEquals($frontendUser->getUid(), $orderDb->getShippingAddress()->getFrontendUser()->getUid());

        /** ToDo: Check for title object!!!! */
        static::assertEquals($order->getShippingAddress()->getFirstName(), $orderDb->getShippingAddress()->getFirstName());
        static::assertEquals($order->getShippingAddress()->getLastName(), $orderDb->getShippingAddress()->getLastName());
        static::assertEquals($order->getShippingAddress()->getCompany(), $orderDb->getShippingAddress()->getCompany());
        static::assertEquals($order->getShippingAddress()->getAddress(), $orderDb->getShippingAddress()->getAddress());
        static::assertEquals($order->getShippingAddress()->getZip(), $orderDb->getShippingAddress()->getZip());
        static::assertEquals($order->getShippingAddress()->getCity(), $orderDb->getShippingAddress()->getCity());

        static::assertEquals($order->getOrderItem()->current()->getProduct()->getUid(), $orderDb->getOrderItem()->current()->getProduct()->getUid());
        static::assertEquals($order->getOrderItem()->current()->getAmount(), $orderDb->getOrderItem()->current()->getAmount());

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
    public function persistOrderContainingProductCollectionResolvesOrderToContainedProducts()
    {

        /**
         * Scenario:
         *
         * Given I'm logged in
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given a product collection is ordered
         * When I make an order
         * Then the order is saved
         * Then the order contains
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check65.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setFrontendUser($frontendUser);
        $order->setRemark('Was ist hier los?');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setFrontendUser($frontendUser);
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

        $this->subject->persistOrder($order, $frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $orderDb */
        $orderDb = $this->orderRepository->findByUid(1);

        $orderDb->getOrderItem()->rewind();
        $order->getOrderItem()->rewind();

        static::assertInstanceOf('\RKW\RkwShop\Domain\Model\Order', $orderDb);
        static::assertEquals($order->getRemark(), $orderDb->getRemark());

        static::assertEquals($frontendUser->getUid(), $orderDb->getFrontendUser()->getUid());
        static::assertEquals($frontendUser->getUid(), $orderDb->getShippingAddress()->getFrontendUser()->getUid());

        /** ToDo: Check for title object!!!! */
        static::assertEquals($order->getShippingAddress()->getFirstName(), $orderDb->getShippingAddress()->getFirstName());
        static::assertEquals($order->getShippingAddress()->getLastName(), $orderDb->getShippingAddress()->getLastName());
        static::assertEquals($order->getShippingAddress()->getCompany(), $orderDb->getShippingAddress()->getCompany());
        static::assertEquals($order->getShippingAddress()->getAddress(), $orderDb->getShippingAddress()->getAddress());
        static::assertEquals($order->getShippingAddress()->getZip(), $orderDb->getShippingAddress()->getZip());
        static::assertEquals($order->getShippingAddress()->getCity(), $orderDb->getShippingAddress()->getCity());

        static::assertEquals($order->getOrderItem()->current()->getProduct()->getUid(), $orderDb->getOrderItem()->current()->getProduct()->getUid());
        static::assertEquals($order->getOrderItem()->current()->getAmount(), $orderDb->getOrderItem()->current()->getAmount());

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
    public function persistOrderCreatesUniqueIncrementedOrderNumber()
    {

        /**
         * Scenario:
         *
         * Given I'm logged in
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given an product is ordered which is out of stock
         * Given that ordered product can be pre-ordered
         * When I make an order
         * Then the order is saved
         * Then the order has a unique order number
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check66.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setFrontendUser($frontendUser);
        $order->setRemark('Was ist hier los?');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setFrontendUser($frontendUser);
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

        $this->subject->persistOrder($order, $frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $orderDb */
        $orderDb = $this->orderRepository->findByUid(2);

        /** @var \RKW\RkwShop\Domain\Model\Order $existingOrder */
        $existingOrder = $this->orderRepository->findByUid(1);

        static::assertInstanceOf('\RKW\RkwShop\Domain\Model\Order', $orderDb);
        static::assertEquals($order->getRemark(), $orderDb->getRemark());

        static::assertEquals('00002', $orderDb->getOrderNumber());

        static::assertTrue((int)$existingOrder->getOrderNumber() + 1 === (int)$orderDb->getOrderNumber());

        static::assertCount(1, $this->orderRepository->findByOrderNumber('00001'));
        static::assertEquals(1, $this->orderRepository->findByOrderNumber('00001')->getFirst()->getUid());

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
    public function createOrderRemovesCartAfterCreatingOrder() {

        /**
         * Scenario:
         *
         * Given I enter a valid shippingAddress
         * Given a product is ordered with amount greater than zero
         * When I confirm an order
         * Then the order is saved
         * Then the existing cart is deleted
         */

        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check190.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Cart  $cart */
        $cart = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash($frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setFrontendUser($frontendUser);
        $order->setRemark('Testen wir das mal');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setFrontendUser($frontendUser);
        $shippingAddress->setFirstName('Karl');
        $shippingAddress->setLastName('Dall');
        $shippingAddress->setCompany('Käse-Zentrum');
        $shippingAddress->setAddress('Emmenthaler Allee 15');
        $shippingAddress->setZip('12345');
        $shippingAddress->setCity('Gauda');
        $order->setShippingAddress($shippingAddress);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        foreach ($cart->getOrderItem() as $orderItem) {
            $order->addOrderItem($orderItem);
        }

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->objectManager->get(Request::class);

        $this->subject->createOrder($order, $request, $frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $orderDb */
        $orderDb = $this->orderRepository->findByUid(1);

        $order->getOrderItem()->rewind();
        $orderDb->getOrderItem()->rewind();

        static::assertEquals($order->getOrderItem()->current()->getProduct()->getUid(), $orderDb->getOrderItem()->current()->getProduct()->getUid());

        /** @var \RKW\RkwShop\Domain\Model\Cart $cartDb */
        $cartDb = $this->cartRepository->findAll();

        static::assertCount(0, $cartDb);

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
    public function createOrderSavesOrderIfProductOutOfStockIsSubscription ()
    {

        /**
         * Scenario:
         *
         * Given I'm not logged in
         * Given I accept the Terms & Conditions
         * Given I accept the Privacy-Terms
         * Given I enter a valid shippingAddress
         * Given an product is ordered which is out of stock
         * Given that ordered product is of type "subscription"
         * When I make an order
         * Then the order is saved as registration
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check70.xml');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);

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

        static::assertEquals(
            'orderService.message.createdOptIn',
            $this->subject->createOrder($order, $request, null, true, true)
        );

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
    public function createOrderPersistsOrderIfUserIsLoggedIn ()
    {

        /**
         * Scenario:
         *
         * Given I'm logged in
         * Given I enter a valid shippingAddress
         * Given an product is ordered with amount greater than zero
         * When I make an order
         * Then the order is saved
         * Then the remark of the order is saved
         * Then the order is linked to the given frontendUser
         * Then the shippingAddress is linked to the given frontendUser
         * Then the shippingAddress is saved correctly
         * Then the ordered product and the given amount is saved correctly
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check30.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setFrontendUser($frontendUser);
        $order->setRemark('Testen wir das mal');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setFrontendUser($frontendUser);
        $shippingAddress->setFirstName('Karl');
        $shippingAddress->setLastName('Dall');
        $shippingAddress->setCompany('Käse-Zentrum');
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

        static::assertEquals(
            'orderService.message.created',
            $this->subject->createOrder($order, $request, $frontendUser, false, true)
        );

        /** @var \RKW\RkwShop\Domain\Model\Order $orderDb */
        $orderDb = $this->orderRepository->findByUid(1);

        $orderDb->getOrderItem()->rewind();
        $order->getOrderItem()->rewind();

        static::assertInstanceOf('\RKW\RkwShop\Domain\Model\Order', $orderDb);
        static::assertEquals($order->getRemark(), $orderDb->getRemark());

        static::assertEquals($frontendUser->getUid(), $orderDb->getFrontendUser()->getUid());
        static::assertEquals($frontendUser->getUid(), $orderDb->getShippingAddress()->getFrontendUser()->getUid());

        /** ToDo: Check for title object!!!! */
        static::assertEquals($order->getShippingAddress()->getFirstName(), $orderDb->getShippingAddress()->getFirstName());
        static::assertEquals($order->getShippingAddress()->getLastName(), $orderDb->getShippingAddress()->getLastName());
        static::assertEquals($order->getShippingAddress()->getCompany(), $orderDb->getShippingAddress()->getCompany());
        static::assertEquals($order->getShippingAddress()->getAddress(), $orderDb->getShippingAddress()->getAddress());
        static::assertEquals($order->getShippingAddress()->getZip(), $orderDb->getShippingAddress()->getZip());
        static::assertEquals($order->getShippingAddress()->getCity(), $orderDb->getShippingAddress()->getCity());

        static::assertEquals($order->getOrderItem()->current()->getProduct()->getUid(), $orderDb->getOrderItem()->current()->getProduct()->getUid());
        static::assertEquals($order->getOrderItem()->current()->getAmount(), $orderDb->getOrderItem()->current()->getAmount());

    }

    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \Exception
     */
    public function removeAllOrdersOfFrontendUserSignalSlotRemovesAllOpenOrdersOfGivenUser ()
    {

        /**
         * Scenario:
         *
         * Given I'm not logged in
         * When I delete all my orders
         * Then all my orders are deleted
         * Then all the corresponding order items are deleted
         * Then all my shippingAddresses are kept
         * Then orders of other users are kept
         * Then shippingAddresses of other users are kept
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check90.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $this->subject->removeAllOrdersOfFrontendUserSignalSlot($frontendUser);

        $resultOrderUser = $this->orderRepository->findByFrontendUser($frontendUser);
        $resultShippingAddressUser = $this->shippingAddressRepository->findByFrontendUser($frontendUser);

        $resultOrderAll = $this->orderRepository->findAll();
        $resultOrderItemAll = $this->orderItemRepository->findAll();
        $resultShippingAddressAll = $this->shippingAddressRepository->findAll();

        self::assertCount(0, $resultOrderUser);
        self::assertCount(1, $resultShippingAddressUser);

        self::assertCount(1, $resultOrderAll);
        self::assertCount(1, $resultOrderItemAll);
        self::assertCount(2, $resultShippingAddressAll);

    }



    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function getRemainingStockSubstractsOrderedFromStock ()
    {

        /**
         * Scenario:
         *
         * Given the same product has been ordered via shop
         * Given the same product as a pre-order-stock
         * Given the same product has been pre-ordered via shop
         * When I fetch the remaining stock
         * Then orders are substracted from the available stock
         * Then pre-order-stocks are excluded from calculation
         * Then pre-orders are excluded from calculation
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check100.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(1);
        self::assertEquals(85, $this->subject->getRemainingStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function getRemainingStockReturnsZeroIfAmountOfOrdersIsGreaterThanStock ()
    {

        /**
         * Scenario:
         *
         * Given a product has been ordered
         * Given the same product has been pre-ordered
         * Given the amount of orders exceeds the available stock
         * When I fetch the remaining stock
         * Then pre-order-stocks are excluded from calculation
         * Then pre-orders are excluded from calculation
         * Then a value of zero is returned
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check110.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(1);

        self::assertEquals(0, $this->subject->getRemainingStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function getRemainingStockReturnsMaxDeliverableStockOfProductBundleBasedOnContainedSubItemsMaxDeliverableStock()
    {

        /**
         * Scenario:
         *
         * Given a product is of type "ProductBundle"
         * Given the product bundle contains a first product with a stock of 10
         * Given the product bundle contains a second product with a stock of 5
         * When I fetch the remaining stock
         * Then the stock of the product bundle is returned as 5
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check125.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(1);
        self::assertEquals(5, $this->subject->getRemainingStockOfProduct($product));
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function getRemainingStockReturnsStockOfProductIfSingleOrderIsAllowed()
    {

        //  @todo: rework dependency on product bundle

        /**
         * Scenario:
         *
         * Given a product is part of a product bundle
         * Given that product bundle allows single orders
         * Given the same product has been ordered via shop
         * Given the same product has been pre-ordered via shop*
         * When I fetch the remaining stock
         * Then the stock of the product itself is returned
         * Then pre-order-stocks are excluded from calculation
         * Then pre-orders are excluded from calculation
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check130.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(1);
        self::assertEquals(88, $this->subject->getRemainingStockOfProduct($product));
    }



    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function getPreOrderStockSubstractsOrderedFromStock ()
    {

        /**
         * Scenario:
         *
         * Given a product has been pre-ordered via shop
         * When I fetch the remaining stock
         * Then normal order-stocks are excluded from calculation
         * Then normal orders are excluded from calculation
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check100.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(1);
        self::assertEquals(45, $this->subject->getPreOrderStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     *
     */
    public function getPreOrderStockReturnsZeroIfAmountOfOrdersIsGreaterThanStock ()
    {
        /**
         * Scenario:
         *
         * Given a product has been ordered
         * Given the same product has been pre-ordered
         * Given the amount of pre-orders exceeds the available stock
         * When I fetch the remaining stock
         * Then normal order-stocks are excluded from calculation
         * Then normal orders are excluded from calculation
         * Then a value of zero is returned
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check110.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(1);

        self::assertEquals(0, $this->subject->getPreOrderStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function getPreOrderStockReturnsStockOfProductIfSingleOrderIsAllowed()
    {
        /**
         * Scenario:
         *
         * Given a product is part of a product bundle
         * Given the product bundle allows single orders
         * Given the same product has been ordered via shop
         * Given the same product has been pre-ordered via shop
         * When I fetch the remaining stock
         * Then the stock of the product itself is returned
         * Then normal order-stocks are excluded from calculation
         * Then normal orders are excluded from calculation
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check130.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(1);
        self::assertEquals(21, $this->subject->getPreOrderStockOfProduct($product));
    }


    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \Exception
     */
    public function cleanUpOrderItemListRemovesOrderItemObjectsWithAmountLowerThanOne ()
    {

        /**
         * Scenario:
         *
         * Given I order two products
         * Given I set the amount of one of the products to zero
         * When I place an order
         * Then the product with amount of zero is removed from my order
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check140.xml');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = GeneralUtility::makeInstance(ShippingAddress::class);
        $shippingAddress->setAddress('Emmenthaler Allee 15');
        $shippingAddress->setZip('12345');
        $shippingAddress->setCity('Gauda');
        $order->setShippingAddress($shippingAddress);

        /** @var \RKW\RkwShop\Domain\Model\Product $productOne */
        $productOne = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\Product $productTwo */
        $productTwo = $this->productRepository->findByUid(2);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = GeneralUtility::makeInstance(OrderItem::class);
        $orderItem->setProduct($productOne);
        $orderItem->setAmount(10);
        $order->addOrderItem($orderItem);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = GeneralUtility::makeInstance(OrderItem::class);
        $orderItem->setProduct($productTwo);
        $orderItem->setAmount(0);
        $order->addOrderItem($orderItem);

        $this->subject->cleanUpOrderItemList($order);

        $orderItemList = $order->getOrderItem()->toArray();
        self::assertCount(1, $orderItemList);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     *     public function getBackendUsersForAdminMails_GivenProductWithAdminsSetAndOneAdminWithInvalidEmail_ReturnsArrayWithBackendUsersWithValidEmailOnly ()

     */
    public function getBackendUsersForAdminMailsAddsAdminFromTextField ()
    {

        /**
         * Scenario:
         *
         * Given I order a product
         * Given that product has two admins for notification
         * Given that one more admins is set in free text field of the product
         * When I place an order
         * Then three admins are returned
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check150.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\BackendUser $result[] */
        $result = $this->subject->getBackendUsersForAdminMails($product);
        static::assertInternalType('array', $result);

        self::assertCount(3, $result);
        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[0]);
        self::assertEquals('test1@test.de', $result[0]->getEmail());

        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[1]);
        self::assertEquals('test2@test.de', $result[1]->getEmail());

        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[2]);
        self::assertEquals('test3@test.de', $result[2]->getEmail());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function getBackendUsersForAdminMailsChecksForValidEmails ()
    {
        /**
         * Scenario:
         *
         * Given I order a product
         * Given that product has two admins for notification
         * Given that one more admins is set in free text field of the product
         * Given two of the admins have invalid email-addresses
         * When I place an order
         * Then one admin is returned only
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check160.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\BackendUser $result[] */
        $result = $this->subject->getBackendUsersForAdminMails($product);
        static::assertInternalType('array', $result);

        self::assertCount(1, $result);
        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[0]);
        self::assertEquals('test1@test.de', $result[0]->getEmail());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getBackendUsersForAdminMailsReturnsFallbackAdmin ()
    {

        /**
         * Scenario:
         *
         * Given I order a product
         * Given that product has two admins for notification
         * Given that the two  admins have invalid email-addresses
         * Given a fallback-admin is set
         * When I place an order
         * Then one fallback-admin is returned only
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check170.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\BackendUser $result[] */
        $result = $this->subject->getBackendUsersForAdminMails($product);
        static::assertInternalType('array', $result);

        self::assertCount(1, $result);

        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[0]);
        self::assertEquals('fallback@test.de', $result[0]->getEmail());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getBackendUsersForAdminMailsReturnsAdminsOfProductBundle ()
    {
        /**
         * Scenario:
         *
         * Given I order a product
         * Given that product belongs to a product bundle
         * Given that product-bundle has two admins for notification
         * Given that product-bundle has an additional admin in the text field
         * When I place an order
         * Then the three admins of the product-bundle are returned
         */
        $this->importDataSet(__DIR__ . '/OrderServiceTest/Fixtures/Database/Check180.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\BackendUser $result[] */
        $result = $this->subject->getBackendUsersForAdminMails($product);
        static::assertInternalType('array', $result);

        self::assertCount(3, $result);
        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[0]);
        self::assertEquals('test1@test.de', $result[0]->getEmail());

        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[1]);
        self::assertEquals('test2@test.de', $result[1]->getEmail());

        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[2]);
        self::assertEquals('test3@test.de', $result[2]->getEmail());
    }



    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}