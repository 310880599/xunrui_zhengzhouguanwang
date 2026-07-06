<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_xinwenzhongxin')->where('catid', $selectedId)->select('id,title,description,url')->order_by('inputtime DESC')->limit(2)->getAll();








    
    
foreach ($data as $row) {

            echo '<li>
                    <a href=https://www.zzyugong.cn'.$row["url"].' style="display: block;text-decoration: none;" target="_blank">
                        <div class="part027_bottom_left_font_li">
                            <h3>
                                  '.mb_substr($row["title"],0,20).'
                            </h3>
                            <h6>
                                  '.mb_substr($row["description"],0,30).'
                            </h6>
                        </div>
                        <div class="part027_bottom_left_picture_li_line">
                            <img alt=""
                                src="https://www-zzyugong-cn.oss-cn-beijing.aliyuncs.com/images/part027_bottom_left_picture_li_line.png" />
                        </div>
                    </a>
                </li>';    

}
    
    






exit();


