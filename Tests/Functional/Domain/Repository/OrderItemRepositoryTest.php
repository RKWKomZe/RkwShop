<?php
namespace RKW\RkwShop\Tests\Functional\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Domain\Repository\OrderItemRepository;
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
 * OrderItemRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderItemRepositoryTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     */
    private $subject = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderItemRepository/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderItemRepository/Product.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderItemRepository/Order.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderItemRepository/OrderItem.xml');

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
        $this->subject = $this->objectManager->get(OrderItemRepository::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);

    }


    //=============================================
    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getOrderedSumByProductAndPreOrder_GivenNormalProduct_ReturnsSumOfOrderAmountsForGivenProductWithoutPreOrdersAndWithoutDeleted()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $result = $this->subject->getOrderedSumByProductAndPreOrder($product);
        self::assertEquals(9, $result);

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function getOrderedSumByProductAndPreOrder_GivenNormalProductAndPreOrderTrue_ReturnsSumOfPreOrderAmountsForGivenProductWithoutDeleted()
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $result = $this->subject->getOrderedSumByProductAndPreOrder($product, true);
        self::assertEquals(18, $result);

    }

    //=============================================

    /**
     * @test
     */
    public function findByOrderUidSoap_GivenOrderUid_ReturnsOrderItemsOfGivenOrderUidIncludingDeletedAndIgnoresStoragePid()
    {
        $result = $this->subject->findByOrderUidSoap(20);
        self::assertCount(2, $result);

    }


    /**
     * @test
     */
    public function findByOrderUidSoap_GivenOrderUid__ReturnsOrderItemsOfGivenOrderUidIncludingRelationToDeletedProduct()
    {
        $result = $this->subject->findByOrderUidSoap(30);
        self::assertCount(1, $result);
        self::assertEquals(3, $result[0]['product']);

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}