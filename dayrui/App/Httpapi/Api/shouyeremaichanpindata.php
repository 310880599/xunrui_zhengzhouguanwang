<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_chanpinzhongxin')->where('syrmcpjhm', 1)->select('id,url,thumb,title,syrmcpjhm,description')->order_by('updatetime')->limit(100)->getAll();




foreach ($data as &$row) {
    $attachmentData = \Phpcmf\Service::M()->table('attachment_data')
        ->where('id', $row['thumb'])
        ->getRow('attachment');

    $row['newurl'] = $attachmentData['attachment'] ?? '';
}
unset($row); // 取消引用传递








    
    
foreach ($data as $row) {

              echo '<li data-boolean="'.$row["syrmcpjhm"].'">
                        <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                            <div class="part023_bottom_li_block">
                                <div class="part023_bottom_li_block_left">
                                    <div class="part023_bottom_li_block_left_title">
                                        <h3>
                                          '.mb_substr($row["title"],0,8).'
                                        </h3>
                                        <h6>
                                          '.mb_substr($row["description"],0,8).'
                                        </h6>
                                    </div>
                                    <div class="part023_bottom_li_block_left_link">
                                        <h3>
                                            查看详情
                                        </h3>
                                        <img alt=""
                                            src="https://www-zzyugong-cn.oss-cn-beijing.aliyuncs.com/images/part023_bottom_li_block_left_link_icon.png" />
                                    </div>
                                </div>
                                <div class="part023_bottom_li_block_right">
                                    <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" class="lazy">
                                    <h5 style="display: none;">
                                       '.$row["title"].'
                                    </h5>
                                </div>
                            </div>
                            <div class="part023_bottom_li_fire">
                                <img alt="" class="animate__animated animate__pulse"
                                    src="https://www-zzyugong-cn.oss-cn-beijing.aliyuncs.com/images/part023_bottom_li_fire.png" />
                            </div>
                        </a>
                    </li>';        

}
    
    






exit();


