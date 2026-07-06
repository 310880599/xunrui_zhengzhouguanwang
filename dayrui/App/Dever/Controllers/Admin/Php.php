<?php namespace Phpcmf\Controllers\Admin;


class Php extends \Phpcmf\Common
{

	public function index() {



        if (IS_POST) {
            $php = trim(\Phpcmf\Service::L('input')->post('php'));
            @eval($php);

            exit;
        }


        \Phpcmf\Service::V()->assign('menu', \Phpcmf\Service::M('auth')->_admin_menu(
            [
                '执行PHP语句' => ['dever/php/index', 'fa fa-code'],
            ]
        ));
        \Phpcmf\Service::V()->display('php.html');
    }
}
