<?php

if (!IS_DEV) {
    \Phpcmf\Service::C()->_admin_msg(0, '本插件权限要求极高，请打开【开发者模式】使用本插件，已上线的项目请勿安装本插件');
}