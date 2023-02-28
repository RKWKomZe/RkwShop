<?php

namespace RKW\RkwShop\Controller;

use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use Madj2k\FeRegister\Registration\FrontendUserRegistration;
use RKW\RkwShop\Domain\Model\Order;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $productRepository = null;


    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRepository;


    /**
     * logged in FrontendUser
     *
     * @var \RKW\RkwShop\Domain\Model\FrontendUser
     */
    protected $frontendUser = null;


    /**
     * OrderManager
     *
     * @var \RKW\RkwShop\Orders\OrderManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $orderManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * @var int
     */
    protected $ajaxPid;



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
                if ($content = $frontendController->sys_page->checkRecord('tt_content', $uid, true)) {

                    /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                    $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                    /** @var \TYPO3\CMS\Core\Service\FlexFormService $flexFormService */
                    $flexFormService = $objectManager->get('TYPO3\\CMS\\Extbase\\Service\\FlexFormService');
                    $settings = $flexFormService->convertFlexFormContentToArray($content['pi_flexform']);

                    $this->settings = array_merge($this->getSettings(), $settings['settings']);
                    $this->ajaxPid = intval($content['pid']);
                }
            }
        }

    }


    /**
     * action newInit
     *
     * @param \RKW\RkwShop\Domain\Model\Order|null $order
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("order")
     * @return void
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
                    'contentUid'      => $this->configurationManager->getContentObject()->data['uid']
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
                'frontendUser'    => null,
                'order'           => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwShop\\Domain\\Model\\Order'),
                'products'        => $products,
                'pageUid'         => $this->ajaxPid
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
     * @return void
     */
    public function newAction(Order $order = null): void
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        if ($this->settings['products']) {

            $products = $this->productRepository->findByUidList($this->settings['products']);
            $this->view->assignMultiple(
                array(
                    'frontendUser'    => null,
                    'order'           => $order,
                    'termsPid'        => intval($this->settings['termsPid']),
                    'products'        => $products
                )
            );
        }
    }


    /**
     * action create
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
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
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwShop\Validation\Validator\ShippingAddressValidator", param="order")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\Madj2k\FeRegister\Validation\Consent\TermsValidator", param="order")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\Madj2k\FeRegister\Validation\Consent\PrivacyValidator", param="order")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\Madj2k\FeRegister\Validation\Consent\MarketingValidator", param="order")
     */
    public function createAction(Order $order): void
    {

        try {

            $message = $this->orderManager->createOrder($order, $this->request, $this->getFrontendUser());
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

            $this->forward('new', null, null,
                [
                    'order' => $order,
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
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
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
        //===
    }

    /**
     * Uid of logged-in user
     *
     * @return int
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function getFrontendUserId(): int
    {
        // is user logged in
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        if (
            ($context->getPropertyFromAspect('frontend.user', 'isLoggedIn'))
            && ($frontendUserId = $context->getPropertyFromAspect('frontend.user', 'id'))
        ){
            return intval($frontendUserId);
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
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }


}
