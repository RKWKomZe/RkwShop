<?php

namespace RKW\RkwShop\Controller;

use \RKW\RkwBasics\Helper\Common;
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
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     * @inject
     */
    protected $productRepository = null;


    /**
     * FrontendUserRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     * @inject
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
     * @inject
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

                    /** @var \TYPO3\CMS\Extbase\Service\FlexFormService $flexFormService */
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
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @ignorevalidation $order
     * @return void
     */
    public function newInitAction(\RKW\RkwShop\Domain\Model\Order $order = null)
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        if ($this->settings['products']) {

            $products = $this->productRepository->findByUidList($this->settings['products']);
            $this->view->assignMultiple(
                array(
                    'frontendUser'    => null,
                    'order'           => $order,
                    'termsPid'        => intval($this->settings['termsPid']),
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
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
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
                'termsPid'        => intval($this->settings['termsPid']),
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
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param integer $terms
     * @param integer $privacy
     * @return void
     */
    public function newAction(\RKW\RkwShop\Domain\Model\Order $order = null, $terms = null, $privacy = null)
    {

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        if ($this->settings['products']) {

            $products = $this->productRepository->findByUidList($this->settings['products']);
            $this->view->assignMultiple(
                array(
                    'frontendUser'    => null,
                    'order'           => $order,
                    'termsPid'        => intval($this->settings['termsPid']),
                    'products'        => $products,
                    'terms'           => $terms,
                    'privacy'         => $privacy
                )
            );
        }
    }



    /**
     * action create
     *
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param integer $terms
     * @param integer $privacy
     * @return void
     * @validate $order \RKW\RkwShop\Validation\Validator\ShippingAddressValidator
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function createAction(\RKW\RkwShop\Domain\Model\Order $order, $terms = null, $privacy = null)
    {

        try {

            $message = $this->orderManager->createOrder($order, $this->request, $this->getFrontendUser(), $terms, $privacy);
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
                    'terms' => $terms,
                    'privacy' => $privacy
                ]
            );
        }

        $this->redirect('new');
    }


    /**
     * Takes optIn parameters and checks them
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function optInAction()
    {
        $tokenYes = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_yes') ? $this->request->getArgument('token_yes') : ''));
        $tokenNo = preg_replace('/[^a-zA-Z0-9]/', '', ($this->request->hasArgument('token_no') ? $this->request->getArgument('token_no') : ''));
        $userSha1 = preg_replace('/[^a-zA-Z0-9]/', '', $this->request->getArgument('user'));

        /** @var \RKW\RkwRegistration\Tools\Registration $register */
        $register = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwRegistration\\Tools\\Registration');
        $check = $register->checkTokens($tokenYes, $tokenNo, $userSha1, $this->request);

        if ($check == 1) {

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'orderController.message.orderCreated', 'rkw_shop'
                )
            );

        } elseif ($check == 2) {

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
        //===
    }



    /**
     * Returns current logged in user object
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     */
    protected function getFrontendUser()
    {

        if (!$this->frontendUser) {

            $frontendUser = $this->frontendUserRepository->findByUidNoAnonymous($this->getFrontendUserId());
            if ($frontendUser instanceof \RKW\RkwRegistration\Domain\Model\FrontendUser) {
                $this->frontendUser = $frontendUser;
            }
        }

        return $this->frontendUser;
        //===
    }



    /**
     * Id of logged User
     *
     * @return integer|null
     */
    protected function getFrontendUserId()
    {
        // is $GLOBALS set?
        if (
            ($GLOBALS['TSFE'])
            && ($GLOBALS['TSFE']->loginUser)
            && ($GLOBALS['TSFE']->fe_user->user['uid'])
        ) {
            return intval($GLOBALS['TSFE']->fe_user->user['uid']);
            //===
        }

        return null;
        //===
    }


    /**
     * Remove ErrorFlashMessage
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::getErrorFlashMessage()
     */
    protected function getErrorFlashMessage()
    {
        return false;
        //===
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('Rkwshop', $which);
        //===
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