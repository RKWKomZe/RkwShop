<?php
namespace RKW\RkwShop\Tests\Functional\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;

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
 * OrderRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderRepositoryTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    private $subject = null;

    /**
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderRepository/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderRepository/Order.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderRepository/FeUsers.xml');


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
        $this->subject = $this->objectManager->get(OrderRepository::class);

        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

    }


    /**
     * @test
     */
    public function findByFrontendUser_ReturnsOrdersWithoutRespectingStorageId()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $result = $this->subject->findByFrontendUser($frontendUser)->toArray();
        static::assertEquals(2, count($result));
        self::assertEquals('1', $result[0]->getUid());
        self::assertEquals('2', $result[1]->getUid());


    }

    /**
     * @test
     */
    public function findByFrontendUser_ReturnsOnlyNonDeletedOrders()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(2);

        $result = $this->subject->findByFrontendUser($frontendUser)->toArray();
        static::assertEquals(1, count($result));
        self::assertEquals('3', $result[0]->getUid());
    }


    /**
     * @test
     */
    public function findByFrontendUser_ReturnsOnlyOrdersOf_GivenUser()
    {

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(3);

        $result = $this->subject->findByFrontendUser($frontendUser)->toArray();
        static::assertEquals(1, count($result));
        self::assertEquals('5', $result[0]->getUid());
    }



    //===================================================================

    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTimestampSoap_GivenTimestampZero_ReturnsCompleteListOfOrdersIncludingDeletedAndDisabledAndIgnoresStoragePid()
    {
        $result = $this->subject->findByTimestampSoap(0);
        static::assertEquals(12, count($result));

    }


    /**
     * @test
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTimestampSoap_GivenTimestampValue_ReturnsListOfOrdersWithTimestampEqualOrGreaterThanGiven()
    {
        $result = $this->subject->findByTimestampSoap(5000);
        static::assertEquals(1, count($result));

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}