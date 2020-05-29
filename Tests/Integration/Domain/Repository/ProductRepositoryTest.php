<?php
namespace RKW\RkwShop\Tests\Integration\Domain\Repository;


use RKW\RkwShop\Domain\Model\Product;
use RKW\RkwShop\Domain\Model\ProductBundle;
use RKW\RkwShop\Domain\Model\ProductSeries;
use RKW\RkwShop\Domain\Model\ProductDownload;
use RKW\RkwShop\Domain\Model\ProductCollection;
use RKW\RkwShop\Domain\Model\ProductSubscription;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Domain\Repository\ProductRepository;

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
 * ProductRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductRepositoryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_shop',

    ];
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $subject = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Functional/Domain/Repository/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(ProductRepository::class);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByUidListRespectsEnableFieldsAndDeleted()
    {
        /**
         * Scenario:
         *
         * Given a list four product ids
         * Given that one product is hidden
         * Given that one product is deleted
         * When I fetch the products
         * Then only two products are returned
         * Then the deleted product is excluded
         * Then the hidden product is excluded
         */
        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check10.xml');

        $result = $this->subject->findByUidList('1,2,3,4');
        static::assertEquals(2, count($result));
        self::assertEquals('1', $result[0]->getUid());
        self::assertEquals('2', $result[1]->getUid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByUidListRespectsStoragePid()
    {
        /**
         * Scenario:
         *
         * Given a list two product ids
         * Given that one product has a different storage pid
         * When I fetch the products
         * Then only one product is returned

         */
        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check20.xml');

        $result = $this->subject->findByUidList('1,2');
        static::assertEquals(1, count($result));
        self::assertEquals('1', $result[0]->getUid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByUidListIgnoresDuplicates()
    {
        /**
         * Scenario:
         *
         * Given a list of six product uids
         * Given that two of the product uids are duplicates
         * When I fetch the products
         * Then each product is returned only once
         */
        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check30.xml');

        $result = $this->subject->findByUidList('1,2,3,4,1,2');
        static::assertEquals(4, count($result));
        self::assertEquals('1', $result[0]->getUid());
        self::assertEquals('2', $result[1]->getUid());
        self::assertEquals('3', $result[2]->getUid());
        self::assertEquals('4', $result[3]->getUid());


    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByUidListKeepsGivenOrder()
    {
        /**
         * Scenario:
         *
         * Given a list of six product uids
         * Given that the uids are not ordered by uid
         * When I fetch the products
         * Then the returned products are in the same order as given
         */
        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check30.xml');

        $result = $this->subject->findByUidList('4,3,2,1');
        static::assertEquals(4, count($result));
        self::assertEquals('4', $result[0]->getUid());
        self::assertEquals('3', $result[1]->getUid());
        self::assertEquals('2', $result[2]->getUid());
        self::assertEquals('1', $result[3]->getUid());

    }


    //===================================================================

    /**
     * @test
     * @throws \Exception
     */
    public function findAllSoapIgnoresEnableFieldsAndDeleted()
    {
        /**
         * Scenario:
         *
         * Given there are four products
         * Given that one product is hidden
         * Given that one product is deleted
         * When I fetch the products
         * Then all four products are returned
         */
        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check10.xml');

        $result = $this->subject->findAllSoap();
        static::assertEquals(4, count($result));

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllSoapRespectsStoragePid()
    {
        /**
         * Scenario:
         *
         * Given there are two products
         * Given that one product has a different storage pid
         * When I fetch the products
         * Then only one product is returned
         */
        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check20.xml');

        $result = $this->subject->findAllSoap();
        static::assertEquals(1, count($result));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findAllReturnsInstanceOfProduct()
    {
        /**
         * Scenario:
         *
         * Given there is a single product
         * Given that product's recordType is 'Product'
         * When I fetch all products
         * Then the returned product is an instance of 'Product'
         */

        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check40.xml');

        $result = $this->subject->findAll();

        static::assertEquals(1, count($result));
        static::assertInstanceOf(Product::class, $result[0]);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findAllReturnsInstanceOfProductDownload()
    {
        /**
         * Scenario:
         *
         * Given there is a single product
         * Given that product's recordType is 'ProductDownload'
         * When I fetch all products
         * Then the returned product is an instance of 'ProductDownload'
         */

        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check80.xml');

        $result = $this->subject->findAll();

        static::assertEquals(1, count($result));
        static::assertInstanceOf(ProductDownload::class, $result[0]);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllReturnsInstanceOfProductCollection()
    {
        /**
         * Scenario:
         *
         * Given there is a single product
         * Given that product's recordType is 'ProductCollection'
         * When I fetch all products
         * Then the returned product is an instance of 'ProductCollection'
         */

        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check60.xml');

        $result = $this->subject->findAll();

        static::assertEquals(1, count($result));
        static::assertInstanceOf(ProductCollection::class, $result[0]);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllReturnsInstanceOfProductBundle()
    {
        /**
         * Scenario:
         *
         * Given there is a single product
         * Given that product's recordType is 'ProductBundle'
         * When I fetch all products
         * Then the returned product is an instance of 'ProductBundle'
         */

        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check50.xml');

        $result = $this->subject->findAll();

        static::assertEquals(1, count($result));
        static::assertInstanceOf(ProductBundle::class, $result[0]);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllReturnsInstanceOfProductSeries()
    {
        /**
         * Scenario:
         *
         * Given there is a single product
         * Given that product's recordType is 'ProductSeries'
         * When I fetch all products
         * Then the returned product is an instance of 'ProductSeries'
         */

        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check75.xml');

        $result = $this->subject->findAll();

        static::assertEquals(1, count($result));
        static::assertInstanceOf(ProductSeries::class, $result[0]);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllReturnsInstanceOfProductSubscription()
    {
        /**
         * Scenario:
         *
         * Given there is a single product
         * Given that product's recordType is 'ProductSubscription'
         * When I fetch all products
         * Then the returned product is an instance of 'ProductSubscription'
         */

        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check70.xml');

        $result = $this->subject->findAll();

        static::assertEquals(1, count($result));
        static::assertInstanceOf(ProductSubscription::class, $result[0]);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findProductBySku()
    {
        /**
         * Scenario:
         *
         * Given there is a single product
         * Given that product's sku is '9999'
         * When I want to find a product by this sku
         * Then the returned product is our single product
         */

        $this->importDataSet(__DIR__ . '/ProductRepositoryTest/Fixtures/Database/Check90.xml');

        $result = $this->subject->findBySku('9999');

        static::assertEquals('9999', $result->getSku());

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}