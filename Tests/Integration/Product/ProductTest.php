<?php

namespace RKW\RkwShop\Tests\Integration\Product;

use Doctrine\Common\Util\Debug;
use RKW\RkwShop\Domain\Model\Product;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwShop\Domain\Repository\ProductBundleRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use RKW\RkwShop\Domain\Model\ProductBundle;

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
 * ProductTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Cart\Cart
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
     * @var \RKW\RkwShop\Domain\Repository\ProductBundleRepository
     */
    private $productBundleRepository;

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

        /*$this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Stock.xml');
*/

        $this->importDataSet(__DIR__ . '/ProductTest/Fixtures/Database/Global.xml');
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

//        $this->subject = $this->objectManager->get(Cart::class);

        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->productBundleRepository = $this->objectManager->get(ProductBundleRepository::class);

    }

    /**
     * @test
     */
    public function getParentProductReturnsCorrectProductBundle()
    {

        /**
         * Scenario:
         *
         * Given a product has a parent product
         * Given the parent product exists
         * When I want to get the product's parents
         * Then I get the product's parent object
         * Then the product's parent object is a ProductBundle
         */

        $this->importDataSet(__DIR__ . '/ProductTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $childProduct */
        $childProduct = $this->productRepository->findByUid(2);

        static::assertEquals(1, $childProduct->getParentProducts()->count());

        /** @var \RKW\RkwShop\Domain\Model\Product $parentProduct */
        $parentProduct = $this->productRepository->findByUid(1);

        static::assertSame($parentProduct, $childProduct->getParentProducts()->current());

        //  Wie kann ich festlegen, dass das Objekt aus der richtigen Instanz kommt? In diesem Falle also nicht "Product", sondern "ProductBundle"???
        //  https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86270-ExtbaseXclassViaTypoScriptSettings.html

        static::assertInstanceOf(ProductBundle::class, $parentProduct);

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