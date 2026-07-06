<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_xinwenzhongxin')->where('catid', $selectedId)->select('id,title,description,updatetime,url')->order_by('inputtime DESC')->limit(6)->getAll();


foreach ($data as $key => $item) {
    $timestamp = $item['updatetime'];
    $formattedDate = date('Y-m-d', $timestamp);
    $data[$key]['updatetime'] = $formattedDate;
}





    
    
foreach ($data as $row) {

            
        echo  '<li>
                <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                    <div class="part027_bottom_right_li">
                        <div class="part027_bottom_right_li_time">
                            <div class="part027_bottom_right_li_time_day">
                                <h3>
                                    '.mb_substr($row["updatetime"],8,2).'
                                </h3>
                            </div>
                            <div class="part027_bottom_right_li_time_year">
                                <h6>
                                    '.mb_substr($row["updatetime"],0,7).'
                                </h6>
                            </div>
                        </div>
                        <div class="part027_bottom_right_li_font">
                            <h3>
                                  '.mb_substr($row["title"],0,20).'
                            </h3>
                            <h6>
                                  '.mb_substr($row["description"],0,30).'
                            </h6>
                        </div>
                    </div>
                </a>
            </li>';

}
    
    






exit();


