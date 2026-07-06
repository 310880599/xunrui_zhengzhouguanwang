<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;  

$ids = \Phpcmf\Service::M()->table('1_chanpinzhongxin_flag')->where('catid', $selectedId)->select('id')->limit(7)->getAll();

// 初始化一个数组来存储数据
$data = [];

foreach ($ids as $row) {
    
    $result = \Phpcmf\Service::M()->table('1_chanpinzhongxin')
    
        ->where('id', $row)
        
        ->select('id,url,title')
        
        ->getAll();
        
    // 将结果合并到 data 数组中
    $data = array_merge($data, $result);
    
}






    
    
foreach ($data as $row) {

    echo '<li>
            <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                <h6>
                      '.mb_substr($row["title"],0,14).'
                </h6>
            </a>
          </li>';        

}
    
    






exit();


