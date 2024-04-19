<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product',
		'label' => 'title',
		'label_alt' => 'subtitle',
		'label_alt_force' => 1,
		'default_sortby' => 'ORDER BY title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => true,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
		],
		'searchFields' => 'uid, title, subtitle, stock, ordered_external, page, product_bundle, backend_user, admin_email,',
		'iconfile' => 'EXT:rkw_shop/Resources/Public/Icons/tx_rkwshop_domain_model_product.gif',
        'type' => 'record_type',
	],
	'types' => [
		'0' => ['showitem' => '--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.basics,record_type, title, subtitle, publishing_date, download, image, page, product_bundle,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.stock, stock, ordered_external,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.access, hidden,--palette--;;1, starttime, endtime'],
        '\RKW\RkwShop\Domain\Model\ProductBundle' => ['showitem' => '--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.basics,record_type, title, subtitle, image, page,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.stock, stock, ordered_external,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email,allow_single_order,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.access, hidden,--palette--;;1, starttime, endtime'],
        '\RKW\RkwShop\Domain\Model\ProductSubscription' => ['showitem' => '--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.basics,record_type, title, subtitle, image, page,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email,allow_single_order,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.access, hidden,--palette--;;1, starttime, endtime'],
        '\RKW\RkwShop\Domain\Model\ProductDownload' => ['showitem' => '--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.basics,record_type, title, subtitle, image, page,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,--div--;LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.tab.access, hidden,--palette--;;1, starttime, endtime'],
    ],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [

        'record_type' => [
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.recordType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.recordType.default', '0'],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.recordType.bundle', '\RKW\RkwShop\Domain\Model\ProductBundle'],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.recordType.subscription', '\RKW\RkwShop\Domain\Model\ProductSubscription'],
                    ['LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.recordType.download', '\RKW\RkwShop\Domain\Model\ProductDownload'],
                ],
                'default' => '0'
            ],
        ],
		'sys_language_uid' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => [
					['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
					['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0]
				],
                'default' => 0
            ],
        ],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['', 0],
				],
				'foreign_table' => 'tx_rkwshop_domain_model_product',
				'foreign_table_where' => ' AND tx_rkwshop_domain_model_product.pid=###CURRENT_PID### AND tx_rkwshop_domain_model_product.sys_language_uid IN (-1,0)',
			],
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
			],
		],
		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'title' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			],
		],
		'subtitle' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.subtitle',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
        'publishing_date' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.publishing_date',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 30,
                'eval' => 'datetime'
            ],
        ],
		'stock' => [
		    /* not very user friendly
            'displayCond' => [
                'OR' => [
                    'EXT:rkw_soap:LOADED:FALSE',
                    'REC:NEW:TRUE'
                ]
            ],*/
            'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.stock',
            'config' => [
                'type' => 'inline',
                'size' => 5,
                'minitems' => 1,
                'maxitems' => 99,
                'foreign_table' => 'tx_rkwshop_domain_model_stock',
                'foreign_table_where' => 'AND tx_rkwshop_domain_model_stock.deleted = 0 AND tx_rkwshop_domain_model_stock.hidden = 0 ORDER BY tx_rkwshop_domain_model_stock.crdate ASC',
                'foreign_field' => 'product',
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                ],
            ]
		],
        'ordered_external' => [
            //'displayCond' => 'EXT:rkw_soap:LOADED:FALSE',
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.orderedExternal',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true
            ],
        ],
        'product_bundle' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.productBundle',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_rkwshop_domain_model_product',
                'foreign_table_where' => ' AND ((\'###PAGE_TSCONFIG_IDLIST###\' <> \'0\' AND FIND_IN_SET(tx_rkwshop_domain_model_product.pid,\'###PAGE_TSCONFIG_IDLIST###\')) OR (\'###PAGE_TSCONFIG_IDLIST###\' = \'0\')) AND tx_rkwshop_domain_model_product.deleted = 0 AND tx_rkwshop_domain_model_product.hidden = 0 AND (tx_rkwshop_domain_model_product.record_type = \'\\\\RKW\\\\RkwShop\\\\Domain\\\\Model\\\\ProductBundle\' OR tx_rkwshop_domain_model_product.record_type = \'\\\\RKW\\\\RkwShop\\\\Domain\\\\Model\\\\ProductSubscription\') ORDER BY tx_rkwshop_domain_model_product.title ASC',
            ],
            'onChange' => 'reload'
        ],
        'page' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.page',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'maxitems' => 1,
                'minitems' => 0,
                'size' => 1,
                'default' => 0,
                'suggestOptions' => [
                    'default' => [
                        'additionalSearchFields' => 'nav_title, alias, url',
                        'addWhere' => 'AND pages.doktype = 1'
                    ]
                ]
            ],
        ],
        'download' => [
            'exclude' => false,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.download',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'download',
                [
                    'maxitems' => 1
                ],
                'doc,docx,docm,xls,xlsx,pdf'
            ),
        ],
        'image' => [
            'exclude' => 0,
            'displayCond' => 'FIELD:download:REQ:FALSE',
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                ['maxitems' => 1],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],
        'backend_user' => [
            'displayCond' => 'FIELD:product_bundle:REQ:FALSE',
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.backendUser',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 99,
                'foreign_table' => 'be_users',
                'foreign_table_where' => 'AND be_users.deleted = 0 AND be_users.disable = 0 ORDER BY be_users.username ASC',
            ]
        ],
        'admin_email' => [
            'displayCond' => 'FIELD:product_bundle:REQ:FALSE',
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.adminEmail',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email'
            ],
        ],
        'allow_single_order' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_product.allowSingleOrder',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
    ],
];
