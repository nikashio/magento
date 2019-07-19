<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'section' => 'Dev',
        'groups' => [
            'log' => [
                'fields' => [
                    'active' => ['value' => '1'],
                    'file' => ['value' => 'fileName.log'],
                    'exception_file' => ['value' => 'exceptionFileName.log'],
                ],
            ],
            'debug' => [
                'fields' => [
                    'template_hints_storefront' => ['value' => '1'],
                    'template_hints_blocks' => ['value' => '0'],
                ],
            ],
        ],
        'expected' => [
            'Dev/log' => [
                'Dev/log/active' => '1',
                'Dev/log/file' => 'fileName.log',
                'Dev/log/exception_file' => 'exceptionFileName.log',
            ],
            'Dev/debug' => [
                'Dev/debug/template_hints_storefront' => '1',
                'Dev/debug/template_hints_blocks' => '0',
            ],
        ],
    ]
];
