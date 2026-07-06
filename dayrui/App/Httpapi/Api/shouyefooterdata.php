<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_chanpinzhongxin')->select('id,url,title')->order_by('updatetime DESC')->limit(4)->getAll();



foreach ($data as $row) {
                    
              echo '<li>
                        <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                            <h6>
                              '.mb_substr($row["title"],0,8).'
                            </h6>
                        </a>
                    </li>';        

}
    
    






exit();


