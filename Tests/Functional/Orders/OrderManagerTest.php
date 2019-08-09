<?php
namespace RKW\RkwShop\Tests\Functional\Orders;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Model\OrderItem;
use RKW\RkwShop\Orders\OrderManager;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;
use RKW\RkwShop\Domain\Repository\ShippingAddressRepository;


use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;


use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
 * OrderManagerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderManagerTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Domain\Model\Order
     */
    private $fixtureDummy = null;

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Request
     */
    private $requestDummy = null;

    /**
     * @var \RKW\RkwShop\Orders\OrderManager
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
     * @var array
     */
    private $maxNumbers;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/Stock.xml');


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

        $this->subject = $this->objectManager->get(OrderManager::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->shippingAddressRepository = $this->objectManager->get(ShippingAddressRepository::class);
        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->privacyRepository = $this->objectManager->get(PrivacyRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);

        $this->requestDummy = $this->objectManager->get(Request::class);

        // Calculating max database uids
        $this->maxNumbers = [
            'frontendUser' => $this->frontendUserRepository->findAll()->count(),
            'order' => $this->orderRepository->findAll()->count(),
        ];

        /** We need an non-persisted order here! */
        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = GeneralUtility::makeInstance(OrderItem::class);
        $orderItem->setProduct($product);
        $orderItem->setAmount(5);

        $this->fixtureDummy = GeneralUtility::makeInstance(Order::class);
        $this->fixtureDummy->addOrderItem($orderItem);
        $this->fixtureDummy->setShippingAddress($shippingAddress);
        $this->fixtureDummy->setEmail('email@rkw.de');

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
     */
    public function createOrderGivenNoFrontendUserAndTermsFalseThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.acceptTerms');

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, null, false, false);

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
     */
    public function createOrderGivenNonPersistentFrontendUserAndTermsFalseThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.acceptTerms');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, $frontendUser, false, false);

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
     */
    public function createOrderGivenNoFrontendUserAndTermsTrueAndPrivacyFalseThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.acceptPrivacy');

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, null, true, false);

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
     */
    public function createOrderGivenFrontendUserAndTermsTrueAndPrivacyFalseThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.acceptPrivacy');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, $frontendUser, true, false);

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
     */
    public function createOrderGivenNonPersistentFrontendUserAndTermsFalseAndPrivacyTrueThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.acceptTerms');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, $frontendUser, false, true);

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
     */
    public function createOrderGivenFrontendUserWithInvalidEmailAndTermsTrueAndPrivacyTrueThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.invalidEmail');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $this->fixtureDummy->setEmail('invalid-email');

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, $frontendUser, true, true);

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
     */
    public function createOrderGivenFrontendUserWithInvalidEmailAndTermsTrueAndPrivacyTrueAndMissingShippingAddressModelThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.noShippingAddress');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setEmail('email@rkw.de');

        $this->subject->createOrder($order, $this->requestDummy, $frontendUser, true, true);

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
     */
    public function createOrderGivenPersistentFrontendUserAndTermsTrueAndPrivacyTrueAndMissingOrderItemThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.noOrderItem');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->setEmail('email@rkw.de');

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByUid(1);
        $order->setShippingAddress($shippingAddress);

        $this->subject->createOrder($order, $this->requestDummy, $frontendUser, true, true);

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
     */
    public function createOrderGivenPersistentFrontendUserAndTermsTrueAndPrivacyTrueAndOrderItemHavingAmountZeroThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.noOrderItem');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $this->fixtureDummy->getOrderItem()->current()->setAmount(0);

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, $frontendUser, true, true);

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
     */
    public function createOrderGivenPersistentFrontendUserAndTermsFalseAndPrivacyTrueReturnsCreateMessageAndAddsOrderAndPrivacyInformationToDatabase ()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertEquals('orderManager.message.created', $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, $frontendUser, false, true));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid($this->maxNumbers['order']+1);
        static::assertInstanceOf('\RKW\RkwShop\Domain\Model\Order', $order);
        static::assertEquals($this->fixtureDummy->getEmail(), $order->getEmail());

        /** @var \RKW\RkwRegistration\Domain\Model\Privacy $privacy */
        $privacy = $this->privacyRepository->findByUid(1);
        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Privacy', $privacy);
        static::assertEquals($frontendUser->getUid(), $privacy->getUid());
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
     */
    public function createOrderGivenNoFrontendUserAndTermsTrueAndPrivacyTrueReturnsOptInMessageAndAddsRegistrationToDatabase ()
    {

        static::assertEquals('orderManager.message.createdOptIn', $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, null, true, true));

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $registration */
        $registration = $this->registrationRepository->findByUid(1);
        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Registration', $registration);
        static::assertEquals(4, $registration->getUser());
        static::assertEquals('rkwShop', $registration->getCategory());

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
     */
    public function createOrderGivenNoFrontendUserAndTermsTrueAndPrivacyTrueAndProductOrderForProductOutOfStockThrowsException ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(5);

        // set amount to very high value
        $this->fixtureDummy->getOrderItem()->current()->setProduct($product);

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.outOfStock');

        $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, null, true, true);

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
     */
    public function createOrderGivenNoFrontendUserAndTermsTrueAndPrivacyTrueAndProductOrderForProductOutOfStockButNotOutOfPreOrderReturnsOptInMessageAndAddsRegistrationToDatabase ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(6);

        // set amount to very high value
        $this->fixtureDummy->getOrderItem()->current()->setProduct($product);

        static::assertEquals('orderManager.message.createdOptIn', $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, null, true, true));

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $registration */
        $registration = $this->registrationRepository->findByUid(1);
        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Registration', $registration);
        static::assertEquals(4, $registration->getUser());
        static::assertEquals('rkwShop', $registration->getCategory());

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
     */
    public function createOrderGivenNoFrontendUserAndTermsTrueAndPrivacyTrueAndProductOrderForSubscriptionProductOutOfStockReturnsOptInMessageAndAddsRegistrationToDatabase ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(7);

        // set amount to very high value
        $this->fixtureDummy->getOrderItem()->current()->setProduct($product);

        static::assertEquals('orderManager.message.createdOptIn', $this->subject->createOrder($this->fixtureDummy, $this->requestDummy, null, true, true));

        /** @var \RKW\RkwRegistration\Domain\Model\Registration $registration */
        $registration = $this->registrationRepository->findByUid(1);
        static::assertInstanceOf('RKW\RkwRegistration\Domain\Model\Registration', $registration);
        static::assertEquals(4, $registration->getUser());
        static::assertEquals('rkwShop', $registration->getCategory());

    }



    //=============================================

    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderGivenPersistedOrderAndPersistedFrontendUserThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.orderAlreadyPersisted');

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $this->subject->saveOrder($order, $frontendUser);

    }

    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderGivenOrderAndNonPersistedFrontendUserThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.frontendUserNotPersisted');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = GeneralUtility::makeInstance(FrontendUser::class);

        $this->subject->saveOrder($this->fixtureDummy, $frontendUser);

    }

    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderGivenOrderWithoutOrderItemThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.noOrderItem');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);

        /** @var \RKW\RkwShop\Domain\Model\ShippingAddress $shippingAddress */
        $shippingAddress = $this->shippingAddressRepository->findByUid(1);
        $order->setShippingAddress($shippingAddress);

        $this->subject->saveOrder($order, $frontendUser);

    }

    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderGivenOrderWithOrderItemHavingAmountZeroThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.noOrderItem');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $this->fixtureDummy->getOrderItem()->current()->setAmount(0);

        $this->subject->saveOrder($this->fixtureDummy, $frontendUser);

    }

    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderGivenOrderWithoutShippingAddressThrowsException ()
    {

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.noShippingAddress');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = GeneralUtility::makeInstance(OrderItem::class);
        $orderItem->setProduct($product);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = GeneralUtility::makeInstance(Order::class);
        $order->addOrderItem($orderItem);

        $this->subject->saveOrder($order, $frontendUser);

    }


    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderGivenOrderAndPersistedFrontendUserReturnsTrueAndAddsOrderAndShippingAddressToDatabaseWithFrontendUserSet ()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertTrue($this->subject->saveOrder($this->fixtureDummy, $frontendUser));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid($this->maxNumbers['order']+1);

        self::assertEquals($frontendUser->getUid(), $order->getFrontendUser()->getUid());
        self::assertEquals($frontendUser->getUid(), $order->getShippingAddress()->getFrontendUser()->getUid());


    }


    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function saveOrderGivenOrderAndPersistedFrontendUserReturnsTrueAndAddsOrderAndShippingAddressWithExpectedDataToDatabase ()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        static::assertTrue($this->subject->saveOrder($this->fixtureDummy, $frontendUser));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid($this->maxNumbers['order']+1);

        $order->getOrderItem()->rewind();
        $this->fixtureDummy->getOrderItem()->rewind();

        /** ToDo: Check for title object!!!! */
        self::assertEquals($this->fixtureDummy->getOrderItem()->current()->getProduct()->getUid(), $order->getOrderItem()->current()->getProduct()->getUid());
        self::assertEquals($this->fixtureDummy->getShippingAddress()->getFirstName(), $order->getShippingAddress()->getFirstName());
        self::assertEquals($this->fixtureDummy->getShippingAddress()->getLastName(), $order->getShippingAddress()->getLastName());
        self::assertEquals($this->fixtureDummy->getShippingAddress()->getCompany(), $order->getShippingAddress()->getCompany());
        self::assertEquals($this->fixtureDummy->getShippingAddress()->getAddress(), $order->getShippingAddress()->getAddress());
        self::assertEquals($this->fixtureDummy->getShippingAddress()->getZip(), $order->getShippingAddress()->getZip());
        self::assertEquals($this->fixtureDummy->getShippingAddress()->getCity(), $order->getShippingAddress()->getCity());

        self::assertEquals($this->fixtureDummy->getEmail(), $order->getEmail());
        self::assertEquals($this->fixtureDummy->getRemark(), $order->getRemark());
        self::assertEquals($this->fixtureDummy->getOrderItem()->current()->getAmount(), $order->getOrderItem()->current()->getAmount());

    }
    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function removeAllOrdersOfFrontendUserSignalSlotRemovesAllOpenOrdersOfGivenUser ()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(3);

        $this->subject->removeAllOrdersOfFrontendUserSignalSlot($frontendUser);
        $result = $this->orderRepository->findByFrontendUser($frontendUser);
        $resultAll = $this->orderRepository->findAll();

        self::assertCount(0, $result);
        self::assertCount($this->maxNumbers['order']-2, $resultAll);
    }



    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getRemainingStockReturnsStockAndSubstractsOrderedAndOrderedExternalFromStock ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(10);
        self::assertEquals(65, $this->subject->getRemainingStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     *
     */
    public function getRemainingStockReturnsZeroIfCalculatedValueIsBelowZero ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(10);
        $product->setOrderedExternal(200);

        self::assertEquals(0, $this->subject->getRemainingStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getRemainingStockGivenProductWithProductBundleWithAllowSingleOrderFalseReturnsStockOfProductBundle ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(11);
        self::assertEquals(21, $this->subject->getRemainingStockOfProduct($product));
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getRemainingStockGivenProductWithProductBundleWithAllowSingleOrderTrueReturnsStockOfProduct()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(13);
        self::assertEquals(45, $this->subject->getRemainingStockOfProduct($product));
    }



    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getPreOrderStockReturnsStockAndSubstractsOrderedAndNotOrderedExternalFromStock ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(20);
        self::assertEquals(245, $this->subject->getPreOrderStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     *
     */
    public function getPreOrderStockReturnsZeroIfCalculatedValueIsBelowZero ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(21);

        self::assertEquals(0, $this->subject->getPreOrderStockOfProduct($product));

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getPreOrderStockGivenProductWithProductBundleWithAllowSingleOrderFalseReturnsStockOfProductBundle ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(22);
        self::assertEquals(33, $this->subject->getPreOrderStockOfProduct($product));
    }


    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getPreOrderStockGivenProductWithProductBundleWithAllowSingleOrderTrueReturnsStockOfProduct()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product =$this->productRepository->findByUid(24);
        self::assertEquals(79, $this->subject->getPreOrderStockOfProduct($product));
    }


    //=============================================

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function cleanUpOrderItemListRemovesOrderItemObjectsWithAmountLowerThanOne ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order =$this->orderRepository->findByUid(6);

        $this->subject->cleanUpOrderItemList($order);

        $orderItemList = $order->getOrderItem()->toArray();
        self::assertCount(2, $orderItemList);
        self::assertEquals(30, $orderItemList[0]->getUid());
        self::assertEquals(32, $orderItemList[1]->getUid());


    }

    //=============================================

    /**
     * @test
     */
    public function getBackendUsersForAdminMailsGivenProductWithAdminsSetAndOneAdminWithInvalidEmailReturnsArrayWithBackendUsersWithValidEmailOnly ()
    {

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
     */
    public function getBackendUsersForAdminMailsGivenProductWithInvalidAdminMailInFieldReturnsArrayWithBackendUsersWithValidEmailOnly ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(3);

        /** @var \RKW\RkwShop\Domain\Model\BackendUser $result[] */
        $result = $this->subject->getBackendUsersForAdminMails($product);
        static::assertInternalType('array', $result);

        self::assertCount(2, $result);
        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[0]);
        self::assertEquals('test1@test.de', $result[0]->getEmail());

        self::assertInstanceOf('\RKW\RkwShop\Domain\Model\BackendUser', $result[1]);
        self::assertEquals('test2@test.de', $result[1]->getEmail());

    }


    /**
     * @test
     */
    public function getBackendUsersForAdminMailsGivenProductWithoutAdminsSetReturnsArrayWithFallbackBeUser ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(2);

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
     */
    public function getBackendUsersForAdminMailsGivenProductWithParentProductReturnsArrayWithBackendUsersWithValidEmailOnly ()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(4);

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