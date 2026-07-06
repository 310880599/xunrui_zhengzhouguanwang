<?php

/**
 * 菜单配置
 */


return [

    'admin' => [

        'app' => [

            'left' => [


                'app-dever' => [
                    'name' => '开发者工具',
                    'icon' => 'fa fa-code',
                    'link' => [
                        [
                            'name' => '应用插件',
                            'icon' => 'fa fa-code',
                            'uri' => 'dever/home/index',
                        ],
                        [
                            'name' => '字段控件代码',
                            'icon' => 'fa fa-table',
                            'uri' => 'dever/field/index',
                        ],
                        [
                            'name' => '导出模块配置',
                            'icon' => 'fa fa-cogs',
                            'uri' => 'dever/module/index',
                        ],
                        [
                            'name' => '数据表字典',
                            'icon' => 'fa fa-database',
                            'uri' => 'dever/db/index',
                        ],
                        [
                            'name' => '执行SQL代码',
                            'icon' => 'bi bi-clipboard-data',
                            'uri' => 'dever/sql/index',
                        ],
                        [
                            'name' => '执行PHP代码',
                            'icon' => 'bi bi-code',
                            'uri' => 'dever/php/index',
                        ],
                        [
                            'name' => '调试变量记录',
                            'icon' => 'fa fa-bug',
                            'uri' => 'dever/debug/index',
                        ],
                        [
                            'name' => '后台HTML特效',
                            'icon' => 'fa fa-html5',
                            'uri' => 'dever/html/index',
                        ],

                    ]
                ],




            ],



        ],




    ],



];