<?php

/**
 * 菜单配置
 */


return [

    'admin' => [

        'app' => [

            'left' => [


                'app-httpapi' => [
                    'name' => 'API接口',
                    'icon' => 'fa fa-plug',
                    'link' => [
                        [
                            'name' => 'API接口密钥',
                            'icon' => 'fa fa-key',
                            'uri' => 'httpapi/auth/index',
                        ],
                        [
                            'name' => 'API接口数据',
                            'icon' => 'fa fa-plug',
                            'uri' => 'httpapi/http/index',
                        ],
                        [
                            'name' => '网站栏目接口',
                            'icon' => 'fa fa-reorder',
                            'uri' => 'httpapi/category/index',
                        ],
                        [
                            'name' => '模块内容接口',
                            'icon' => 'fa fa-th-large',
                            'uri' => 'httpapi/module/index',
                        ],
                        [
                            'name' => '项目信息接口',
                            'icon' => 'fa fa-cog',
                            'uri' => 'httpapi/site/index',
                        ],

                    ]
                ],
            ],



        ],

    ],
];