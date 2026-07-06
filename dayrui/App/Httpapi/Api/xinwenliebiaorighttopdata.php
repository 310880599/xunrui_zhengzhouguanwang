<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;  






$data = \Phpcmf\Service::M()->table('1_xinwenzhongxin')->where('catid', $selectedId)->where('xinwenyoubian', 1)->select('id,url,title')->order_by('inputtime DESC')->limit(6)->getAll();



//var_dump($data);





 foreach ($data as $row) {

        echo '<li>
                <a href=https://www.zzyugong.cn'.$row["url"].' style="text-decoration: none;" target="_blank">
                    <div class="part011_right_top_content_li_font">
                        <div class="part011_right_top_content_li_font_icon">
                        <div class="part011_right_top_content_li_font_icon_circle">
        
                        </div>
                        </div>
                        <div class="part011_right_top_content_li_font_icon_h6">
                            <h6>
                              '.mb_substr($row["title"],0,18).'
                            </h6>
                        </div>
                    </div>
                    <div class="part011_right_top_content_li_line">
                        <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesNewsList/part011_right_top_content_li_line.png" alt="">
                    </div>
                </a>
            </li>';        

 }
    
    






exit();


