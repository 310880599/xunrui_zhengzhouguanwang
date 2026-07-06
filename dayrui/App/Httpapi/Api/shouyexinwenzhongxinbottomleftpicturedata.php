<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_xinwenzhongxin')->where('catid', $selectedId)->select('id,url,thumb')->order_by('inputtime DESC')->limit(2)->getAll();




foreach ($data as &$row) {
    $attachmentData = \Phpcmf\Service::M()->table('attachment_data')
        ->where('id', $row['thumb'])
        ->getRow('attachment');

    $row['newurl'] = $attachmentData['attachment'] ?? '';
}
unset($row); // 取消引用传递








    
    
foreach ($data as $row) {

                
              echo '<li>
                        <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                            <div class="part027_bottom_left_picture_li">
                                <div class="part027_bottom_left_picture_li_image">
                                    <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="" class="lazy">
                                </div>
                            </div>
                        </a>
                    </li>';    

}
    
    






exit();


