<?php
namespace RKW\RkwShop\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwBasics\Helper\Common;
use RKW\RkwRegistration\Tools\Authentication;
use RKW\RkwShop\Domain\Repository\CartRepository;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;
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
 * CartRepositoryTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartRepositoryTest extends FunctionalTestCase
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
        $this->importDataSet(__DIR__ . '/CartRepositoryTest/Fixtures/Database/Global.xml');

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
        $this->subject = $this->objectManager->get(CartRepository::class);

        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findByFrontendUserIncludesCartsWithDifferentStoragePid()
    {

        /**
         * Scenario:
         *
         * Given a user has placed two carts
         * Given one cart has a different storage pid
         * When I fetch the carts of the user
         * Then both carts are returned
         */
        $this->importDataSet(__DIR__ . '/CartRepositoryTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $result = $this->subject->findByFrontendUser($frontendUser)->toArray();
        static::assertEquals(2, count($result));
        self::assertEquals('1', $result[0]->getUid());
        self::assertEquals('2', $result[1]->getUid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByFrontendUserIgnoresDeletedCarts()
    {
        /**
         * Scenario:
         *
         * Given a user has placed two carts
         * Given one of the carts is deleted
         * When I fetch the carts of the user
         * Then only one cart is returned
         */
        $this->importDataSet(__DIR__ . '/CartRepositoryTest/Fixtures/Database/Check20.xml');


        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        $result = $this->subject->findByFrontendUser($frontendUser)->toArray();
        static::assertEquals(1, count($result));
        self::assertEquals('1', $result[0]->getUid());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByFrontendUserOrFrontendUserSessionHashGetsCurrentGuestUsersCart()
    {

        /**
         * Scenario:
         *
         * Given current guest user created a cart
         * Given another guest user created a cart
         * When I fetch the cart by frontend user cookie hash
         * Then only the cart of the current user is returned
         */

        $this->importDataSet(__DIR__ . '/CartRepositoryTest/Fixtures/Database/Check30.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';  //  get this from a fixture or set it in a fixture

        $result = $this->subject->findByFrontendUserOrFrontendUserSessionHash();

        static::assertEquals(1, count($result));
        static::assertEquals('1', $result->getUid());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findByFrontendUserOrFrontendUserSessionHashGetsCurrentLoggedInUsersCart()
    {

        /**
         * Scenario:
         *
         * Given a frontend user is logged in
         * Given he created a cart
         * Given the session hash is different
         * When I fetch the cart of the user
         * Then his cart is returned
         */

        $this->importDataSet(__DIR__ . '/CartRepositoryTest/Fixtures/Database/Check40.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '';

        $result = $this->subject->findByFrontendUserOrFrontendUserSessionHash($frontendUser);

        static::assertEquals('1', $result->getUid());

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}