<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;  

//$result = '你好,这是我想返回的字符串';

$data = \Phpcmf\Service::M()->table('1_anlizhongxin')->where('baldycp', $selectedId)->select('title,url,thumb,description,alxqydbzbzt')->order_by('updatetime DESC')->limit(6)->getAll();

//echo $result."---".$selectedId."---";

$num = count($data);

foreach ($data as &$row) {
    $attachmentData = \Phpcmf\Service::M()->table('attachment_data')
        ->where('id', $row['thumb'])
        ->getRow('attachment');

    $row['newurl'] = $attachmentData['attachment'] ?? '';
}
unset($row); // 取消引用传递




foreach ($data as &$row) {
    $attachmentData = \Phpcmf\Service::M()->table('attachment_data')
        ->where('id', $row['alxqydbzbzt'])
        ->getRow('attachment');

    $row['newnewurl'] = $attachmentData['attachment'] ?? '';
}
unset($row); // 取消引用传递




//var_dump($data);

//echo $num;

if($num == 1){
    
    
    foreach ($data as $row) {
   
        echo  '<li>
            <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                <div class="part011_block_li_bottom_left_li_top">
                    <div class="part011_block_li_bottom_left_li_top_title">
                         <h3>
                              '.$row["title"].'
                         </h3>
                    </div>
                    <div class="part011_block_li_bottom_left_li_top_content">
                        <div class="part011_block_li_bottom_left_li_top_content_author">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_li_top_content_li_icon_01.png" alt="">
                            <h6>
                                编辑：豫工机械
                            </h6>
                        </div>
                        <div class="part011_block_li_bottom_left_li_top_content_tel">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_li_top_content_li_icon_02.png" alt="">
                            <h6>
                                工厂热线：189-3713-5091
                            </h6>
                        </div>
                        <div class="part011_block_li_bottom_left_li_top_content_source">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_li_top_content_li_icon_03.png" alt="">
                            <h6>
                                来源：https://www.zzyugong.cn/
                            </h6>
                        </div>
                    </div>
                </div>
                <div class="part011_block_li_bottom_left_li_bottom">
                    <div class="part011_block_li_bottom_left_li_bottom_image">
                        <div class="part011_block_li_bottom_left_li_bottom_image_piece">
                            <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" title="'.$row["title"].'">
                            <h5 style="display: none;">
                                '.$row["title"].'
                            </h5>
                        </div>
                    </div>
                    <div class="part011_block_li_bottom_left_li_bottom_content">
                        <div class="part011_block_li_bottom_left_li_bottom_content_image">
                            <div class="part011_block_li_bottom_left_li_bottom_content_image_piece">
                                <img src="https://www.zzyugong.cn/uploadfile/'.$row["newnewurl"].'" alt="'.$row["title"].'" title="'.$row["title"].'">
                                <h5 style="display: none;">
                                   '.$row["title"].'
                                </h5>
                            </div>
                        </div>
                        <div class="part011_block_li_bottom_left_li_bottom_content_paragraph">
                            <div class="part011_block_li_bottom_left_li_bottom_content_paragraph_piece">
                                <p>
                                    '.mb_substr($row["description"],0,82).'...
                                </p>
                            </div>
                            <div class="part011_block_li_bottom_left_li_bottom_content_contact">
                                <div class="part011_block_li_bottom_left_li_bottom_content_contact_piece">
                                    <h6>
                                        查看更多&gt;
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </li>' ;
    
    }
    
    
}

if($num == 2){
    
    
    foreach ($data as $row) {
   
        echo  '<li>
            <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                <div class="part011_block_li_bottom_left_image">
                    <div class="part011_block_li_bottom_left_image_piece">
                        <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" title="'.$row["title"].'">
                        <h5 style="display: none;">
                           '.$row["title"].'
                        </h5>
                    </div>
                </div>
                <div class="part011_block_li_bottom_left_font">
                    <div class="part011_block_li_bottom_left_font_h3">
                        <h3>
                              '.$row["title"].'
                        </h3>
                    </div>
                    <div class="part011_block_li_bottom_left_font_contact">
                        <div class="part011_block_li_bottom_left_font_contact_author">
                            <h6>
                                编辑：豫工机械      
                            </h6>
                        </div>
                        <div class="part011_block_li_bottom_left_font_contact_tel">
                            <h6>
                                工厂热线：189-3713-5091
                            </h6>
                        </div>
                    </div>
                    <div class="part011_block_li_bottom_left_font_p">
                        <p>
                            '.mb_substr($row["description"],0,82).'...
                        </p>
                    </div>
                    <div class="part011_block_li_bottom_left_font_arrow_green">
                        <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_font_arrow_green_image.png" alt="">
                        <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_font_arrow_green_image.png" alt="">
                    </div>
                </div>
            </a>
        </li>';
    
    }
    
    
}


if($num == 3){
    
    
    foreach ($data as $row) {
   
        echo  '<li>
            <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                <div class="part011_block_li_bottom_left_image">
                    <div class="part011_block_li_bottom_left_image_piece">
                        <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" title="'.$row["title"].'">
                        <h5 style="display: none;">
                           '.$row["title"].'
                        </h5>
                    </div>
                </div>
                <div class="part011_block_li_bottom_left_font">
                    <div class="part011_block_li_bottom_left_font_h3">
                        <h3>
                              '.mb_substr($row["title"],0,1000).'
                        </h3>
                    </div>
                    <div class="part011_block_li_bottom_left_font_content">
                        <div class="part011_block_li_bottom_left_font_content_author">
                            <h6>
                                编辑：豫工机械      
                            </h6>
                        </div>
                        <div class="part011_block_li_bottom_left_font_content_tel">
                            <h6>
                                工厂热线：189-3713-5091
                            </h6>
                        </div>
                    </div>
                    <div class="part011_block_li_bottom_left_font_p">
                        <p>
                            '.mb_substr($row["description"],0,82).'...
                        </p>
                    </div>
                </div>
                <div class="part011_block_li_bottom_left_contact">
                    <div class="part011_block_li_bottom_left_contact_piece">
                        <div class="part011_block_li_bottom_left_contact_piece_font">
                            <h3>
                                查看更多
                            </h3>
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_font_circle_image.png" alt="">
                        </div>
                        <div class="part011_block_li_bottom_left_contact_piece_arrow">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_contact_piece_arrow_image.png" alt="">
                        </div>
                    </div>
                </div>
            </a>
        </li>';
    
    }
    
    
}


if($num == 4){
    
    
    foreach ($data as $row) {
   
        echo  '<li>
            <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                
                <div class="part011_block_li_bottom_left_li_top">
                     <div class="part011_block_li_bottom_left_li_top_h3">
                        <h3>
                              '.mb_substr($row["title"],0,1000).'
                        </h3>
                    </div>
                    <div class="part011_block_li_bottom_left_li_top_arrow">
                        <div class="part011_block_li_bottom_left_li_top_arrow_piece_green">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_li_top_arrow_piece_green_image.png" alt="">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_li_top_arrow_piece_green_image.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="part011_block_li_bottom_left_li_bottom">
                    <div class="part011_block_li_bottom_left_li_bottom_piece">
                        <div class="part011_block_li_bottom_left_li_bottom_piece_image">
                            <div class="part011_block_li_bottom_left_li_bottom_piece_image_lump">
                                <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" title="'.$row["title"].'">
                                <h5 style="display: none;">
                                    '.$row["title"].'
                                </h5>
                            </div>
                        </div>
                        <div class="part011_block_li_bottom_left_li_bottom_piece_content">
                            <div class="part011_block_li_bottom_left_li_bottom_piece_content_p">
                                <p>
                                    '.mb_substr($row["description"],0,52).'...
                                </p>
                            </div>
                            <div class="part011_block_li_bottom_left_li_bottom_piece_content_line">
                                <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_li_bottom_piece_content_line_image.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                

            </a>
        </li>';
    
    }
    
    
}


if($num == 5){
    
    
    foreach ($data as $row) {
   
        echo  '<li>
            <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                <div class="part011_block_li_bottom_left_image">
                    <div class="part011_block_li_bottom_left_image_piece">
                        <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" title="'.$row["title"].'">
                        <h5 style="display: none;">
                            '.$row["title"].'
                        </h5>
                    </div>
                </div>
                <div class="part011_block_li_bottom_left_font">
                    <div class="part011_block_li_bottom_left_font_h3">
                        <h3>
                              '.mb_substr($row["title"],0,1000).'
                        </h3>
                    </div>
                    <div class="part011_block_li_bottom_left_font_arrow">
                        <div class="part011_block_li_bottom_left_font_arrow_image">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_font_arrow_image.png" alt="">
                            <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_font_arrow_image.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="part011_block_li_bottom_left_content">
                    <div class="part011_block_li_bottom_left_content_mix">
                        <div class="part011_block_li_bottom_left_content_mix_h3">
                            <h3>
                                  '.mb_substr($row["title"],0,1000).'
                            </h3>
                        </div>
                        <div class="part011_block_li_bottom_left_content_mix_contact">
                            <div class="part011_block_li_bottom_left_content_mix_contact_author">
                                <h6>
                                    编辑：豫工机械      
                                </h6>
                            </div>
                            <div class="part011_block_li_bottom_left_content_mix_contact_tel">
                                <h6>
                                    工厂热线：189-3713-5091
                                </h6>
                            </div>
                        </div>
                        <div class="part011_block_li_bottom_left_content_mix_p">
                            <p>
                                '.mb_substr($row["description"],0,100).'...
                            </p>
                        </div>
                    </div>
                    <div class="part011_block_li_bottom_left_content_contact">
                        <div class="part011_block_li_bottom_left_content_contact_piece">
                            <div class="part011_block_li_bottom_left_content_contact_piece_font">
                                <h3>
                                    查看更多
                                </h3>
                                <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_content_contact_piece_font_circle_image.png" alt="">
                            </div>
                            <div class="part011_block_li_bottom_left_content_contact_piece_arrow">
                                <div class="part011_block_li_bottom_left_content_contact_piece_arrow_white">
                                    <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_content_contact_piece_arrow_white_image.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                <div>
            </a>
        </li>';
    
    }
    
    
}


if($num == 6){
    
    
    foreach ($data as $row) {
   
        echo  '<li>
                    <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                        <div class="part011_block_li_bottom_left_image">
                            <div class="part011_block_li_bottom_left_image_piece">
                                <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" title="'.$row["title"].'">
                                <h5 style="display: none;">
                                    '.$row["title"].'
                                </h5>
                            </div>
                        </div>
                        <div class="part011_block_li_bottom_left_font">
                        <div class="part011_block_li_bottom_left_font_h3">
                            <h3>
                                  '.mb_substr($row["title"],0,1000).'
                            </h3>
                        </div>
                        <div class="part011_block_li_bottom_left_font_arrow">
                            <div class="part011_block_li_bottom_left_font_arrow_image">
                                <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_font_arrow_image.png" alt="">
                                <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesCasesList/part011_block_li_bottom_left_font_arrow_image.png" alt="">
                            </div>
                        </div>
                    </div>
                    </a>
              </li>' ;
    
    }
    
    
}








                                                  

//\Phpcmf\Service::C()->_json(1, 'ok', ['data' => $result]);

//\Phpcmf\Service::C()->_json(1, 'ok', $data);





exit();


