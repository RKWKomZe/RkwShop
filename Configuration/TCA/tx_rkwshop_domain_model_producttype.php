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
        'model' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_producttype.model',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'is_collection' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:rkw_shop/Resources/Private/Language/locallang_db.xlf:tx_rkwshop_domain_model_producttype.isCollection',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
    ],
];