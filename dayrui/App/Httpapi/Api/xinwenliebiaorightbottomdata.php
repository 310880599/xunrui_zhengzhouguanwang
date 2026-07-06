<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;  

$data = \Phpcmf\Service::M()->table('1_xinwenzhongxin')->where('catid', $selectedId)->where('xinwenyoubian', 1)->select('id,url,thumb')->order_by('inputtime DESC')->limit(6)->getAll();



foreach ($data as &$row) {

    $row['newurl'] = dr_thumb($row['thumb'], 403, 237 ,0, 'crop');
    
}

unset($row); 







//var_dump($data);





 foreach ($data as $row) {

        echo '<li>
                <a href=https://www.zzyugong.cn'.$row["url"].' style="text-decoration: none;" target="_blank">
                    <img src="'.$row["newurl"].'" alt="" class="lazy">
                </a>
            </li>';        

 }
    
    






exit();


