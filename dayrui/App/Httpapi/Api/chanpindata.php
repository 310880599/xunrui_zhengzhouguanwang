<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;  


$catIds = \Phpcmf\Service::M()->table('1_chanpinzhongxin')->where('id', $selectedId)->select('catid')->order_by('updatetime')->limit(1)->getAll();

$data = \Phpcmf\Service::M()->table('1_chanpinzhongxin')->where('catid', $catIds[0]['catid'])->select('id,title,url,thumb')->order_by('updatetime')->limit(5)->getAll();


foreach ($data as $key => $subArray) {
    if ($subArray['id'] == $selectedId) {
        unset($data[$key]);
        break;
    }
}


foreach ($data as &$row) {
    $attachmentData = \Phpcmf\Service::M()->table('attachment_data')
        ->where('id', $row['thumb'])
        ->getRow('attachment');

    $row['newurl'] = $attachmentData['attachment'] ?? '';
}
unset($row); // 取消引用传递


// 取出前4条记录
$data = array_slice($data, 0, 4);
    
    
foreach ($data as $row) {

    echo  '<li>
                <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                    <div class="part026_bottom_li_image">
                        <div class="part026_bottom_li_image_piece">
                             <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="">
                        </div>
                    </div>
                    <div class="part026_bottom_li_font">
                        <div class="part026_bottom_li_font_original">
                            <h3>
                                  '.mb_substr($row["title"],0,14).'
                            </h3>
                        </div>
                        <div class="part026_bottom_li_font_cover">
                            <div class="part026_bottom_li_font_cover_title">
                                <h3>
                                      '.mb_substr($row["title"],0,14).'
                                </h3>
                            </div>
                            <div class="part026_bottom_li_font_cover_link">
                                <div class="part026_bottom_li_font_cover_link_piece">
                                   <a href=https://www.zzyugong.cn'.$row["url"].'" style="display: block;text-decoration: none;" target="_blank">
                                        <button class="button--nina">
                                            <span>了</span>
                                            <span>解</span>
                                            <span>详</span>
                                            <span>情</span>
                                            <span><img class="animate__animated animate__pulse" src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/product_article/part026_bottom_li_font_cover_link_piece_icon.png" alt="" style="position: relative;"></span>
                                            <div class="button--nina-before">了解详情<img class="animate__animated animate__pulse" src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/product_article/part026_bottom_li_font_cover_link_piece_icon.png" alt=""></div>
                                        </button>
                                    </a> 
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </li>' ;

}
    
    






exit();


