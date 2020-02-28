<?php
return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_producttype',
		'label' => 'title',
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
		'searchFields' => 'uid, title, description',
		'iconfile' => 'EXT:rkw_shop/Resources/Public/Icons/tx_rkwshop_domain_model_producttype.gif',
        'adminOnly' => 1,
        'rootLevel' => 1,
        'is_static' => 1,
        'readOnly' => 1,
	],
	'interface' => [
		'showRecordFieldList' => 'title, description',
	],
	'columns' => [
        'deleted' => [
            'readonly' => 1,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:deleted',
            'config' => [
                'type' => 'check'
            ]
        ],
        'title' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_producttype.title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			],
		],
        'description' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_producttype.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ],
        ],
        'has_article_number' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_producttype.hasArticleNumber',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'allow_single_order' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_producttype.allowSingleOrder',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
    ],
];