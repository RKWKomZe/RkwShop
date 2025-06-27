<?php

namespace RKW\RkwShop\Controller;

use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use Madj2k\FeRegister\Registration\FrontendUserRegistration;
use Madj2k\FeRegister\Utility\FrontendUserSessionUtility;
use Madj2k\FeRegister\Utility\FrontendUserUtility;
use RKW\RkwOutcome\Domain\Repository\SurveyConfigurationRepository;
use RKW\RkwShop\Domain\Model\FrontendUser;
use RKW\RkwShop\Domain\Model\Order;
use RKW\RkwShop\Domain\Repository\CategoryRepository;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Orders\OrderManager;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * OrderController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?ProductRepository $productRepository = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?FrontendUserRepository $frontendUserRepository = null;


    /**
     * @var \RKW\RkwShop\Domain\Repository\CategoryRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?CategoryRepository $categoryRepository = null;


    /**
     * @var \RKW\RkwOutcome\Domain\Repository\SurveyConfigurationRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?SurveyConfigurationRepository $surveyConfigurationRepository = null;


    /**
     * @var \RKW\RkwShop\Orders\OrderManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?OrderManager $orderManager = null;


    /**
     * @var \RKW\RkwShop\Domain\Model\FrontendUser
     */
    protected ?FrontendUser $frontendUser = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected ?Logger $logger = null;


    /**
     * @var int
     */
    protected int $ajaxPid = 0;


    /**
     * @param \RKW\RkwShop\Domain\Repository\ProductRepository $productRepository
     */
    public function injectProductRepository(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\FrontendUserRepository $frontendUserRepository
     */
    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    /**
     * @param \RKW\RkwShop\Domain\Repository\SurveyConfigurationRepository $surveyConfigurationRepository
     */
    public function injectSurveyConfigurationRepository (SurveyConfigurationRepository $surveyConfigurationRepository)
    {
        $this->surveyConfigurationRepository = $surveyConfigurationRepository;
    }


    /**
     * @param \RKW\RkwShop\Orders\OrderManager $orderManager
     */
    public function injectOrderManager(OrderManager $orderManager)
    {
        $this->orderManager = $orderManager;
    }


    /**
     * Initializes the controller before invoking an action method.
     *
     * !!! Relevant for AJAX-Context !!!
     *
     * @return void
     * @api
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException if such an argument does not exist
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function initializeAction()
    {

        // Get flexform data in AJAX context
        if ($this->request->hasArgument('uid')) {

            if (
                ($uid = $this->request->getArgument('uid'))
                && ($frontendController = $GLOBALS['TSFE'])
                && ($frontendController instanceof \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController)
            ) {

                // Get flexform data by content-uid
                /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontendController */
                // @extensionScannerIgnoreLine
                if ($content = $frontendController->sys_page->checkRecord('tt_content', $uid, true)) {

                    /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                    $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                    /** @var \TYPO3\CMS\Core\Service\FlexFormService $flexFormService */
                    $flexFormService = $objectManager->get('TYPO3\\CMS\\Core\\Service\\FlexFormService');
                    $settings = $flexFormService->convertFlexFormContentToArray($content['pi_flexform']);

                    $this->settings = array_merge($this->getSettings(), $settings['settings']);
                    $this->ajaxPid = intval($content['pid']);
                }
            }
        }

        $this->settings['digitalOnly'] = $this->containsOnlyDigitalProducts($this->settings['products']);
        $this->settings['showOutcomeModal'] = $this->containsOnlyOutcomeProductDownload($this->settings['products']);

    }


    /**
     * action newInit
     *
     * @param \RKW\RkwShop\Domain\Model\Order|null $order
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("order")
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function newInitAction(\RKW\RkwShop\Domain\Model\Order $order = null): void
    {
        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        if ($this->settings['products']) {

            $products = $this->productRepository->findByUidList($this->settings['products']);

            $this->view->assignMultiple(
                array(
                    'frontendUser'    => null,
                    'order'           => $order,
                    'products'        => $products,
                    // @extensionScannerIgnoreLine
                    'showOutcomeModal' => $this->settings['showOutcomeModal'],
                    'contentUid'      => $this->configurationManager->getContentObject()->data['uid'],
                    'targetGroupList' => $this->categoryRepository->findChildrenByParent($this->settings['targetGroupsPid']),
                )
            );
        }
    }


    /**
     * action newAjax
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException if the extension name is not valid
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidControllerNameException if the controller name is not valid
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidActionNameException if the action name is not valid
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function newAjaxAction()
    {
        /** @var \RKW\RkwBasics\Api\JsonApi $jsonHelper */
        $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Api\\JsonApi');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        if (
            ($this->ajaxPid)
            && ($this->settings['products'])
        ){

            $products = $this->productRepository->findByUidList($this->settings['products']);
            $replacements = [
                'frontendUser'    => $this->getFrontendUser(),
                'order'           => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwShop\\Domain\\Model\\Order'),
                'products'        => $products,
                'pageUid'         => $this->ajaxPid,
                'targetGroupList' => $this->categoryRepository->findChildrenByParent($this->settings['targetGroupsPid']),
                'settings'        => $this->settings, // should not be needed
            ];

            $jsonHelper->setRequest($this->request);
            $jsonHelper->setHtml(
                'rkw-order-container',
                $replacements,
                'replace',
                'Ajax/New.html'
            );
        }

        print (string)$jsonHelper;
        exit();
    }


    /**
     * action new
     *
     * @param \RKW\RkwShop\Domain\Model\Order|null $order
     * @param int $targetGroup
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("order")
     */
    public function newAction(Order $order = null, int $targetGroup = 0): void
    {
        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        if ($this->settings['products']) {

            $products = $this->productRepository->findByUidList($this->settings['products']);
            $this->view->assignMultiple(
                array(
                    'frontendUser'    => $this->getFrontendUser(),
                    'order'           => $order,
                    'products'        => $products,
                    'targetGroupList' => $this->categoryRepository->findChildrenByParent($this->settings['targetGroupsPid']),
                    'targetGroup'     => $targetGroup,
                    'settings'        => $this->settings, // should not be needed
                )
            );
        }
    }


    /**
     * action create
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param int $targetGroup
     * @return void
     * @throws \Madj2k\FeRegister\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @TYPO3\CMS\Extbase\Annotation\Validate("RKW\RkwShop\Validation\Validator\ShippingAddressValidator", param="order")
     * @TYPO3\CMS\Extbase\Annotation\Validate("Madj2k\FeRegister\Validation\Consent\TermsValidator", param="order")
     * @TYPO3\CMS\Extbase\Annotation\Validate("Madj2k\FeRegister\Validation\Consent\PrivacyValidator", param="order")
     * @TYPO3\CMS\Extbase\Annotation\Validate("Madj2k\FeRegister\Validation\Consent\MarketingValidator", param="order")
     */
    public function createAction(Order $order, int $targetGroup = 0): void
    {
        try {

            $message = $this->orderManager->createOrder($order, $this->request, $this->getFrontendUser(), $targetGroup, $this->settings['digitalOnly']);
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    $message, 'rkw_shop'
                )
            );

        } catch (\RKW\RkwShop\Exception $exception) {
            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    $exception->getMessage(), 'rkw_shop'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );

            /**
             * Fixing "Serialization of 'Closure' is not allowed" in /var/www/html/public/typo3/sysext/fluid/Classes/ViewHelpers/FormViewHelper.php line 291
             * We simply remove the non-persisted object here. Not ideal, but working so far
             * @see https://forge.typo3.org/issues/91364
             */
            $order->setTargetGroup(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());

            $this->forward('new', null, null,
                [
                    'order' => $order,
                    'targetGroup' => $targetGroup,
                ]
            );
        }

        $this->redirect('new');
    }


    /**
     * Takes optIn parameters and checks them
     *
     * @param string $tokenUser
     * @param string $token
     * @return void
     * @throws \Madj2k\FeRegister\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function optInAction(string $tokenUser, string $token): void
    {

        /** @var \Madj2k\FeRegister\Registration\FrontendUserRegistration $registration */
        $registration = $this->objectManager->get(FrontendUserRegistration::class);
        $result = $registration->setFrontendUserToken($tokenUser)
            ->setCategory('rkwShop')
            ->setRequest($this->request)
            ->validateOptIn($token);

        if ($result >= 200 && $result < 300){

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'orderController.message.orderCreated', 'rkw_shop'
                )
            );

        } elseif ($result >= 300 && $result < 400) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'orderController.message.orderCanceled', 'rkw_shop'
                )
            );

        } else {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'orderController.error.orderError', 'rkw_shop'
                ),
                '',
                \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
            );
        }

        $this->forward('new');
    }


    /**
     * Returns current logged in user object
     *
     * @return \Madj2k\FeRegister\Domain\Model\FrontendUser|null
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function getFrontendUser()
    {

        if (!$this->frontendUser) {

            $frontendUser = $this->frontendUserRepository->findByUid($this->getFrontendUserId());
            if ($frontendUser instanceof \Madj2k\FeRegister\Domain\Model\FrontendUser) {
                $this->frontendUser = $frontendUser;
            }
        }

        return $this->frontendUser;
    }

    /**
     * Uid of logged-in user
     *
     * @return int
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function getFrontendUserId(): int
    {
        if (
            ($frontendUser = FrontendUserSessionUtility::getLoggedInUser())
            && (! FrontendUserUtility::isGuestUser($frontendUser))
        ){
            return $frontendUser->getUid();
        }

        return 0;
    }


    /**
     * Remove ErrorFlashMessage
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::getErrorFlashMessage()
     */
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return Common::getTypoScriptConfiguration('Rkwshop', $which);
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    protected function containsOnlyDigitalProducts(string $productUids): bool
    {
        $containsOnlyDigitalProducts = true;

        $products = $this->productRepository->findByUidList($productUids);
        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        foreach ($products as $product) {
            if (
                !($product instanceof \RKW\RkwShop\Domain\Model\ProductDownload)
                && ! $product->isDigitalOnly()
            ) {
                $containsOnlyDigitalProducts = false;
            }
        }

        return $containsOnlyDigitalProducts;

    }

    protected function containsOnlyOutcomeProductDownload(string $productUids): bool
    {
        $containsOnlyOutcomeProductDownload = true;

        $products = $this->productRepository->findByUidList($productUids);
        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        foreach ($products as $product) {
            if (
                ! ($product instanceof \RKW\RkwShop\Domain\Model\ProductDownload)
                ||
                (
                    ! ($product instanceof \RKW\RkwShop\Domain\Model\ProductDownload)
                    && count($this->surveyConfigurationRepository->findByProduct($product)) === 0
                )
            ) {
                $containsOnlyOutcomeProductDownload = false;
            }
        }

        return $containsOnlyOutcomeProductDownload;

    }

}
