<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_anlizhongxin')->where('anlixianshi', 1)->select('id,url,thumb,title')->order_by('updatetime')->limit(4)->getAll();




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
                            <div class="part025_list_li">
                                <div class="part025_list_li_top">
                                    <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" class="lazy">
                                    <h5 style="display: none;">
                                       '.$row["title"].'
                                    </h5>
                                </div>
                                <div class="part025_list_li_bottom">
                                    <div class="part025_list_li_bottom_font">
                                        <h3>
                                          '.mb_substr($row["title"],0,20).'
                                        </h3>
                                    </div>
                                    <div class="part025_list_li_bottom_link">
                                        <img alt="" class="part025_list_li_bottom_link_icon_circle"
                                            src="https://www-zzyugong-cn.oss-cn-beijing.aliyuncs.com/images/part025_list_li_bottom_link_icon_circle.png" />
                                        <img alt="" class="part025_list_li_bottom_link_icon_arrow"
                                            src="https://www-zzyugong-cn.oss-cn-beijing.aliyuncs.com/images/part025_list_li_bottom_link_icon_arrow.png" />
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>';        

}
    
    






exit();


