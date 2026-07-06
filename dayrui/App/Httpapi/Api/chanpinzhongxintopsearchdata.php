<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_chanpinzhongxin')->select('id,url,title')->order_by('updatetime DESC')->limit(5)->getAll();



//var_dump($data);



    
foreach ($data as $row) {

              echo '<li>
                        <a href=https://www.zzyugong.cn'.$row["url"].' style="text-decoration: none;" target="_blank">
                            <div class="part02_top_right_li_font">
                                <h6>
                                  '.mb_substr($row["title"],0,20).'
                                </h6>
                            </div>
                        </a>
                    </li>';        

}
    
    






exit();


