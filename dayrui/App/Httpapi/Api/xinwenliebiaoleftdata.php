<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;  


$data = \Phpcmf\Service::M()->table('1_xinwenzhongxin')->where('catid', $selectedId)->where('xinwenzuobian', 1)->select('id,url,title,description,thumb,updatetime')->order_by('inputtime DESC')->limit(3)->getAll();


//var_dump($data);




foreach ($data as &$row) {

    $row['newurl'] = dr_thumb($row['thumb'], 1049, 512 ,0, 'crop');
    
}

unset($row); 




foreach ($data as $key => $item) {
    $timestamp = $item['updatetime'];
    $formattedDate = date('Y-m-d', $timestamp);
    $data[$key]['updatetime'] = $formattedDate;
}



//var_dump($data);





 foreach ($data as $row) {

     echo '<div class="swiper-slide">
                <a href=https://www.zzyugong.cn'.$row["url"].' style="text-decoration: none;" target="_blank">
                    <div class="part011_left_piece_li">
                        <div class="part011_left_piece_li_top">
                            <img src="'.$row["newurl"].'" alt="" class="lazy">
                        </div>
                        <div class="part011_left_piece_li_middle">
                            <div class="part011_left_piece_li_middle_title">
                                <h3>
                                  '.mb_substr($row["title"],0,20).'
                                </h3>
                            </div>
                            <div class="part011_left_piece_li_middle_content">   
                                <p>
                                    '.mb_substr($row["description"],0,100).'
                                </p>
                            </div>  
                        </div>
                        <div class="part011_left_piece_li_bottom">
                            <div class="part011_left_piece_li_bottom_date">
                                <h3>
                                    '.mb_substr($row["updatetime"],0,100).'
                                </h3>
                            </div>
                            <div class="part011_left_piece_li_bottom_link">
                                <a href=https://www.zzyugong.cn'.$row["url"].' style="text-decoration: none;" target="_blank">
                                    <div class="part011_left_piece_li_bottom_link_lump">
                                        <h6>
                                            查看详情
                                        </h6>
                                        <img src="https://www.zzyugong.cn/static/assets/zhengzhoudaguanwang/images/zzImagesNewsList/part011_left_piece_li_bottom_link_lump_icon.png" alt="">
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </a>
            </div>';        

 }
    
    






exit();


