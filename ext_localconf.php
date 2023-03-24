<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'ItemList',
            array(
                'Order' => 'newInit, newAjax, new, create, optIn',
            ),
            // non-cacheable actions
            array(
                'Order' => 'newAjax, new, create, optIn',
            )
        );

        //=================================================================
        // Register DataMapper
        //=================================================================
        /** @var \TYPO3\CMS\Extbase\Object\Container\Container $extbaseObjectContainer */
        $extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
        $extbaseObjectContainer->registerImplementation(
            \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class,
            \RKW\RkwShop\Persistence\Generic\Mapper\DataMapper::class
        );

        //=================================================================
        // Register SignalSlots
        //=================================================================
        /**
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $signalSlotDispatcher->connect(
            Madj2k\FeRegister\Registration\AbstractRegistration::class,
            \Madj2k\FeRegister\Registration\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN  . 'RkwShop',
            RKW\RkwShop\Service\RkwMailService::class,
            'optInRequest'
        );

        $signalSlotDispatcher->connect(
            Madj2k\FeRegister\Registration\AbstractRegistration::class,
            \Madj2k\FeRegister\Registration\AbstractRegistration::SIGNAL_AFTER_REGISTRATION_COMPLETED . 'RkwShop',
            RKW\RkwShop\Orders\OrderManager::class,
            'saveOrderSignalSlot'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwShop\Orders\OrderManager::class,
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_CREATED_USER,
            RKW\RkwShop\Service\RkwMailService::class,
            'confirmationOrderUser'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwShop\Orders\OrderManager::class,
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_CREATED_ADMIN,
            RKW\RkwShop\Service\RkwMailService::class,
            'confirmationOrderAdmin'
        );

        $signalSlotDispatcher->connect(
            Madj2k\FeRegister\Registration\AbstractRegistration::class,
            \Madj2k\FeRegister\Registration\AbstractRegistration::SIGNAL_AFTER_REGISTRATION_ENDED,
            RKW\RkwShop\Orders\OrderManager::class,
            'removeAllOrdersOfFrontendUserSignalSlot'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwShop\Orders\OrderManager::class,
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_DELETED_USER,
            RKW\RkwShop\Service\RkwMailService::class,
            'deleteOrderUser'
        );

        $signalSlotDispatcher->connect(
            RKW\RkwShop\Orders\OrderManager::class,
            \RKW\RkwShop\Orders\OrderManager::SIGNAL_AFTER_ORDER_DELETED_ADMIN,
            RKW\RkwShop\Service\RkwMailService::class,
            'deleteOrderAdmin'
        );

        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwShop']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath()  . '/log/tx_rkwshop.log'
                )
            ),
        );
    },
    $_EXTKEY
);

