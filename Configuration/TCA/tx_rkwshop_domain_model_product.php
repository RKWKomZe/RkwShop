<?php

$_LLL = 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf';

return [
	'ctrl' => [
		'title'	=> $_LLL . ':tx_rkwshop_domain_model_product',
		'label' => 'title',
        'label_userFunc' => \RKW\RkwShop\Utilities\TCA::class . '->buildProductTitle',
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
		'searchFields' => 'uid, article_number, title, subtitle, description, stock, ordered_external, page, product_bundle, backend_user, admin_email, comment',
		'iconfile' => 'EXT:rkw_shop/Resources/Public/Icons/tx_rkwshop_domain_model_product.gif',
        'type' => 'record_type',
        'requestUpdate' => 'product_bundle',
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, article_number, title, subtitle, description, publishing_date, image, stock, ordered_external, page, product_bundle, backend_user, admin_email, comment, tags',
	],
	'types' => [
        '\RKW\RkwShop\Domain\Model\Product' => [
            'showitem' => '
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.basics, record_type, parent_products, article_number, title, subtitle, description, publishing_date, download, image, page, product_bundle,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.stock, stock, ordered_external,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email, comment, allow_single_order,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.tags, categories,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.access, hidden,
                --palette--;;1, starttime, endtime
            '
        ],
        '\RKW\RkwShop\Domain\Model\ProductCollection' => [
            'showitem' => '
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.basics, record_type, child_products, title, subtitle, description, image, page,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email, comment,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.tags, categories,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.access, hidden,
                --palette--;;1, starttime, endtime
            '
        ],
        '\RKW\RkwShop\Domain\Model\ProductBundle' => [
            'showitem' => '
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.basics, record_type, child_products, article_number, title, subtitle, description, image, page,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.stock, stock, ordered_external,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email, comment,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.tags, categories,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.access, hidden,
                --palette--;;1, starttime, endtime
            '
        ],
        '\RKW\RkwShop\Domain\Model\ProductSubscription' => [
            'showitem' => '
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.basics, record_type, child_products, article_number, title, subtitle, description, image, page,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email, comment,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.tags, categories,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.access, hidden,
                --palette--;;1, starttime, endtime
            '
        ],
        '\RKW\RkwShop\Domain\Model\ProductDownload' => [
            'showitem' => '
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.basics, record_type, parent_products, article_number, title, subtitle, description, image, page,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.order, backend_user, admin_email, comment, allow_single_order,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.language, sys_language_uid, l10n_parent, l10n_diffsource,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.tags, categories,
                --div--;' . $_LLL . ':tx_rkwshop_domain_model_product.tab.access, hidden,
                --palette--;;1, starttime, endtime
            '
        ],
    ],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [
        'record_type' => [
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.recordType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$_LLL . ':tx_rkwshop_domain_model_product.recordType.default', '\RKW\RkwShop\Domain\Model\Product'],
                    [$_LLL . ':tx_rkwshop_domain_model_product.recordType.collection', '\RKW\RkwShop\Domain\Model\ProductCollection'],
                    [$_LLL . ':tx_rkwshop_domain_model_product.recordType.bundle', '\RKW\RkwShop\Domain\Model\ProductBundle'],
                    [$_LLL . ':tx_rkwshop_domain_model_product.recordType.subscription', '\RKW\RkwShop\Domain\Model\ProductSubscription'],
                    [$_LLL . ':tx_rkwshop_domain_model_product.recordType.download', '\RKW\RkwShop\Domain\Model\ProductDownload'],
                ],
                'default' => '\RKW\RkwShop\Domain\Model\Product'
            ],
        ],
        'product_type' => [
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.productType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_rkwshop_domain_model_producttype',
                'foreign_table_where' => 'AND tx_rkwshop_domain_model_producttype.deleted = 0 ORDER BY uid ASC',
                'minitems' => 1,
                'maxitems' => 1,
                'default' => 1,
            ],
        ],
        'sys_language_uid' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => [
					['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
					['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
				],
                'default' => 0
            ],
        ],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
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
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
        'article_number' => [
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.articleNumber',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required,unique'
            ],
        ],
		'title' => [
			'exclude' => 0,
			'label' => $_LLL . ':tx_rkwshop_domain_model_product.title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			],
		],
		'subtitle' => [
			'exclude' => 0,
			'label' => $_LLL . ':tx_rkwshop_domain_model_product.subtitle',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
        'description' => [
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ],
        ],
        'publishing_date' => [
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.publishing_date',
            'config' => [
                'type' => 'input',
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
			'label' => $_LLL . ':tx_rkwshop_domain_model_product.stock',
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
            'displayCond' => 'EXT:rkw_soap:LOADED:FALSE',
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.orderedExternal',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true
            ],
        ],
        'product_bundle' => [
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.productBundle',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_rkwshop_domain_model_product',
                'foreign_table_where' => ' AND ((\'###PAGE_TSCONFIG_IDLIST###\' <> \'0\' AND FIND_IN_SET(tx_rkwshop_domain_model_product.pid,\'###PAGE_TSCONFIG_IDLIST###\')) OR (\'###PAGE_TSCONFIG_IDLIST###\' = \'0\')) AND tx_rkwshop_domain_model_product.deleted = 0 AND tx_rkwshop_domain_model_product.hidden = 0 AND tx_rkwshop_domain_model_product.record_type = \'\\\\RKW\\\\RkwShop\\\\Domain\\\\Model\\\\ProductBundle\' ORDER BY tx_rkwshop_domain_model_product.title ASC',
            ]
        ],
        'page' => [
            'exclude' => false,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.page',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'int',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                        'module' => [
                            'name' => 'wizard_link',
                        ],
                        'JSopenParams' => 'height=400,width=550,status=0,menubar=0,scrollbars=1',
                        'params' => [
                            // List of tabs to hide in link window. Allowed values are:
                            // file, mail, page, spec, folder, url
                            'blindLinkOptions' => 'mail,file,spec,folder,url',

                            // allowed extensions for file
                            //'allowedExtensions' => 'mp3,ogg',
                        ]
                    ]
                ],
                'softref' => 'typolink'
            ],
        ],
        'download' => [
            'exclude' => false,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.download',
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
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'maxitems' => 1,

                    // Use the imageoverlayPalette instead of the basicoverlayPalette
                    'foreign_types' => [
                        '0' => [
                            'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                            'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                            'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                            'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                            'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                        ]
                    ]
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],        
        'backend_user' => [
            'displayCond' => 'FIELD:product_bundle:REQ:FALSE',
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.backendUser',
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
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.adminEmail',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,email'
            ],
        ],
        'allow_single_order' => [
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.allowSingleOrder',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'comment' => [
            'exclude' => 0,
            'label' => $_LLL . ':tx_rkwshop_domain_model_product.comment',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ],
        ],
        'parent_products' => [
            'label'=>'' . $_LLL . ':tx_rkwshop_domain_model_product.parentProducts',
            'exclude' => 0,
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_rkwshop_domain_model_product',
                'foreign_table_where' => 'AND tx_rkwshop_domain_model_product.uid != ###THIS_UID### AND tx_rkwshop_domain_model_product.product_type IN (2,3) AND tx_rkwshop_domain_model_product.hidden = 0 AND tx_rkwshop_domain_model_product.deleted = 0 ORDER BY title ASC',
                'MM' => 'tx_rkwshop_domain_model_product_product_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'minitems' => 0,
                'multiple' => 0,
            ],
        ],
        'child_products' => [
            'exclude' => false,
            'label'=>'' . $_LLL . ':tx_rkwshop_domain_model_product.childProducts',
            'config'  => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_rkwshop_domain_model_product',
                'foreign_table_where' => 'AND tx_rkwshop_domain_model_product.uid != ###THIS_UID### AND tx_rkwshop_domain_model_product.product_type NOT IN (2,3) AND tx_rkwshop_domain_model_product.hidden = 0 AND tx_rkwshop_domain_model_product.deleted = 0 ORDER BY title ASC',
                'MM' => 'tx_rkwshop_domain_model_product_product_mm',
                'MM_opposite_field' => 'parent_products',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'minitems' => 0,
                'multiple' => 0,
            ],
        ],
    ],
];