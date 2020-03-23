<?php

namespace RKW\RkwShop\Tests\Integration\Service\Checkout;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwShop\Cart\Cart;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Service\Checkout\CartService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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

        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);

    }

    /**
     * @test
     */
    public function getCartCreatesANewOrderForSessionUserIfAnOrderDoesNotAlreadyExist ()
    {

        /**
         * Scenario:
         *
         * Given a guest adds a product to the cart
         * Given he has not created a cart yet
         * When I initialize a cart
         * Then a new cart is created
         */

        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Check10.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderBefore = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(0, count($orderBefore));

        $this->subject->getCart();

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderAfter = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderAfter));
        static::assertEquals('1', $orderAfter->getFirst()->getUid());

    }

    /**
     * @test
     */
    public function addAddsAProductToOrderForSessionUser()
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

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderBefore = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderBefore));
        static::assertEquals('1', $orderBefore->getFirst()->getUid());
        static::assertEquals(0, count($orderBefore->getFirst()->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $selectedProduct = $this->productRepository->findByUid(1);

        $this->subject->add($selectedProduct, $amount = 1);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderAfter = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderAfter));
        static::assertEquals('1', $orderAfter->getFirst()->getUid());
        static::assertEquals(1, count($orderAfter->getFirst()->getOrderItem()));

    }

    /**
     * @test
     */
    public function addSameProductUpdatesQuantity()
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

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderBefore = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderBefore));
        static::assertEquals('1', $orderBefore->getFirst()->getUid());
        static::assertEquals(1, count($orderBefore->getFirst()->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $selectedProduct = $this->productRepository->findByUid(1);

        $this->subject->add($selectedProduct, $amount = 1);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderAfter = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderAfter));
        static::assertEquals('1', $orderAfter->getFirst()->getUid());
        static::assertEquals(1, $orderAfter->getFirst()->getOrderItem()->count());

        $orderItems = $orderAfter->getFirst()->getOrderItem();
        $orderItems->rewind();

        static::assertEquals(2, $orderItems->current()->getAmount());

    }

    /**
     * @test
     */
    public function removeRemovesOrderItemFromOrderForSessionUser()
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

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderBefore = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderBefore));
        static::assertEquals('1', $orderBefore->getFirst()->getUid());
        static::assertEquals(2, count($orderBefore->getFirst()->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $removableItem = $this->orderItemRepository->findByUid(2);

        $this->subject->remove($removableItem);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderAfter = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderAfter));
        static::assertEquals('1', $orderAfter->getFirst()->getUid());
        static::assertEquals(1, count($orderAfter->getFirst()->getOrderItem()));

        $orderItems = $orderAfter->getFirst()->getOrderItem();
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

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderBefore = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderBefore));
        static::assertEquals('1', $orderBefore->getFirst()->getUid());
        static::assertEquals(1, count($orderBefore->getFirst()->getOrderItem()));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $selectedOrderItems = $orderBefore->getFirst()->getOrderItem();
        $selectedOrderItems->rewind();

        $selectedOrderItem = $selectedOrderItems->current();

        $this->subject->changeQuantity($selectedOrderItem, $amount = 5);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderAfter = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderAfter));
        static::assertEquals('1', $orderAfter->getFirst()->getUid());
        static::assertEquals(1, $orderAfter->getFirst()->getOrderItem()->count());

        $orderItems = $orderAfter->getFirst()->getOrderItem();
        $orderItems->rewind();

        static::assertEquals(5, $orderItems->current()->getAmount());

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