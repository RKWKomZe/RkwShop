<?php

namespace RKW\RkwShop\Service;

use Madj2k\CoreExtended\Utility\GeneralUtility;
use Madj2k\Postmaster\Service\MailService;
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
 * RkwMailService
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwMailService implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Handles opt-in event
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \Madj2k\FeRegister\Domain\Model\OptIn $optIn
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function optInRequest
    (
        \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser,
        \Madj2k\FeRegister\Domain\Model\OptIn $optIn
    )
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($frontendUser->getEmail()) {
            if ($settings['view']['templateRootPaths'][0]) {

                /** @var \Madj2k\Postmaster\Service\MailService $mailService */
                $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

                // send new user an email with token
                $mailService->setTo($frontendUser, array(
                    'marker' => array(
                        'tokenYes'     => $optIn->getTokenYes(),
                        'tokenNo'      => $optIn->getTokenNo(),
                        'userSha1'     => $optIn->getTokenUser(),
                        'optIn'        => $optIn,
                        'pageUid'      => intval($GLOBALS['TSFE']->id),
                        'loginPid'     => intval($settingsDefault['loginPid']),
                    ),
                ));

                $mailService->getQueueMail()->setSubject(
                    \Madj2k\Postmaster\Utility\FrontendLocalizationUtility::translate(
                        'rkwMailService.optInRequestEvent.subject',
                        'rkw_shop',
                        null,
                        $frontendUser->getTxFeregisterLanguageKey()
                    )
                );


                $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
                $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

                $mailService->getQueueMail()->setPlaintextTemplate('Email/OptInRequest');
                $mailService->getQueueMail()->setHtmlTemplate('Email/OptInRequest');

                $mailService->send();
            }
        }
    }


    /**
     * Handles confirm order mail for user
     *
     * Works with FeRegister-FrontendUser -> this is correct! (data comes from TxFeRegister)
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function confirmationOrderUser
    (
        \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser,
        \RKW\RkwShop\Domain\Model\Order $order
    )
    {
        $this->userMail($frontendUser, $order, 'confirmation');
    }


    /**
     * Handles confirm order mail for admin
     *
     * @param \RKW\RkwShop\Domain\Model\BackendUser|array $backendUser
     * @param \RKW\RkwShop\Domain\Model\Order  $order
     * @param array $backendUserForProductMap
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function confirmationOrderAdmin
    (
        $backendUser,
        \RKW\RkwShop\Domain\Model\Order $order,
        array $backendUserForProductMap
    )
    {
        $this->adminMail($backendUser, $order, $backendUserForProductMap, 'confirmation');
    }


    /**
     * Handles delete order mail for user
     *
     * Works with FeRegister-FrontendUser -> this is correct! (data comes from TxFeRegister)
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function deleteOrderUser
    (
        \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser,
        \RKW\RkwShop\Domain\Model\Order $order
    )
    {
        $this->userMail($frontendUser, $order, 'delete', true);
    }


    /**
     * Handles delete order mail for admin
     *
     * @param \RKW\RkwShop\Domain\Model\BackendUser|array   $backendUser
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwShop\Domain\Model\Order  $order
     * @param array $backendUserForProductMap
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function deleteOrderAdmin
    (
        $backendUser,
        \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser,
        \RKW\RkwShop\Domain\Model\Order $order,
        array $backendUserForProductMap
    )
    {
        $this->adminMail($backendUser, $order, $backendUserForProductMap, 'delete', $frontendUser, true);
    }


    /**
     * Sends an E-Mail to a Frontend-User
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param string $action
     * @param bool $renderTemplates
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function userMail
    (
        \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser,
        \RKW\RkwShop\Domain\Model\Order $order,
        string $action = 'confirmation',
        bool $renderTemplates = false
    )
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($frontendUser->getEmail()) {
            if ($settings['view']['templateRootPaths'][0]) {

                /** @var \Madj2k\Postmaster\Service\MailService $mailService */
                $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

                // send new user an email with token
                $mailService->setTo($frontendUser, array(
                    'marker' => array(
                        'order'        => $order,
                        'frontendUser' => $frontendUser,
                        'pageUid'      => intval($GLOBALS['TSFE']->id),
                        'loginPid'     => intval($settingsDefault['loginPid']),
                    ),
                    $renderTemplates
                ));

                $mailService->getQueueMail()->setSubject(
                    \Madj2k\Postmaster\Utility\FrontendLocalizationUtility::translate(
                        'rkwMailService.' . strtolower($action) . 'User.subject',
                        'rkw_shop',
                        null,
                        $frontendUser->getTxFeregisterLanguageKey()
                    )
                );

                $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
                $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

                $mailService->getQueueMail()->setPlaintextTemplate('Email/' . ucFirst(strtolower($action)) . 'OrderUser');
                $mailService->getQueueMail()->setHtmlTemplate('Email/' . ucFirst(strtolower($action)) . 'OrderUser');

                $mailService->send();
            }
        }

    }


    /**
     * Sends an E-Mail to an Admin
     *
     * @param \RKW\RkwShop\Domain\Model\BackendUser|array $backendUser
     * @param \RKW\RkwShop\Domain\Model\Order $order
     * @param array $backendUserForProductMap
     * @param string $action
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param bool $renderTemplates
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function adminMail
    (
        $backendUser,
        \RKW\RkwShop\Domain\Model\Order $order,
        array $backendUserForProductMap,
        string $action = 'confirmation',
        \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser = null,
        bool $renderTemplates = false
    )
    {
        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        $recipients = array();
        if (is_array($backendUser)) {
            $recipients = $backendUser;
        } else {
            $recipients[] = $backendUser;
        }

        if ($settings['view']['templateRootPaths'][0]) {

            /** @var \Madj2k\Postmaster\Service\MailService $mailService */
            $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

            foreach ($recipients as $recipient) {

                if (
                    ($recipient instanceof \RKW\RkwShop\Domain\Model\BackendUser)
                    && ($recipient->getEmail())
                ) {
                    // send new user an email with token
                    $mailService->setTo($recipient, array(
                        'marker'  => array(
                            'order'        => $order,
                            'backendUser'  => $recipient,
                            'frontendUser' => $frontendUser,
                            'backendUserForProductMap' => $backendUserForProductMap,
                            'pageUid'      => intval($GLOBALS['TSFE']->id),
                            'loginPid'     => intval($settingsDefault['loginPid']),
                        ),
                        'subject' => \Madj2k\Postmaster\Utility\FrontendLocalizationUtility::translate(
                            'rkwMailService.' . strtolower($action) . 'Admin.subject',
                            'rkw_shop',
                            null,
                            $recipient->getLang()
                        ),
                        $renderTemplates
                    ));
                }
            }

            if (
                ($order->getFrontendUser())
                && ($order->getFrontendUser()->getEmail())
            ) {
                $mailService->getQueueMail()->setReplyAddress($order->getFrontendUser()->getEmail());
            }

            $mailService->getQueueMail()->setSubject(
                \Madj2k\Postmaster\Utility\FrontendLocalizationUtility::translate(
                    'rkwMailService.' . strtolower($action) . 'Admin.subject',
                    'rkw_shop',
                    null,
                    'de'
                )
            );

            $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

            $mailService->getQueueMail()->setPlaintextTemplate('Email/' . ucfirst(strtolower($action)) . 'OrderAdmin');
            $mailService->getQueueMail()->setHtmlTemplate('Email/' . ucfirst(strtolower($action)) . 'OrderAdmin');

            if (count($mailService->getTo())) {
                $mailService->send();
            }
        }

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
        return GeneralUtility::getTypoScriptConfiguration('Rkwshop', $which);
    }
}
