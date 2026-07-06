<?php
/**
 * api 示例文件
 */
 
$selectedId = $_POST["selectedId"];

$selectedId = (int)$selectedId;

$data = \Phpcmf\Service::M()->table('1_sylbtgl')->where('lbtzt', 1)->select('id,lbtzt,thumb,lbandt,description,title')->order_by('displayorder,updatetime')->limit(100)->getAll();




foreach ($data as &$row) {
    $attachmentData = \Phpcmf\Service::M()->table('attachment_data')
        ->where('id', $row['thumb'])
        ->getRow('attachment');

    $row['newurl'] = $attachmentData['attachment'] ?? '';
}
unset($row); // 取消引用传递





foreach ($data as &$row) {
    $attachmentData = \Phpcmf\Service::M()->table('attachment_data')
        ->where('id', $row['lbandt'])
        ->getRow('attachment');

    $row['newnewurl'] = $attachmentData['attachment'] ?? '';
}
unset($row); // 取消引用传递


    
    
foreach ($data as $row) {
                    
              echo '<div class="swiper-slide part02_block_piece_lump_big_img" data-boolean="'.$row["lbtzt"].'">
                        <div class="part02_block_piece_lump_image">
                            <a style="text-decoration: none;" target="_blank" href='.$row["description"].'>
                                <img src="https://www.zzyugong.cn/uploadfile/'.$row["newurl"].'" alt="'.$row["title"].'" class="lazy">
                            </a>
                        </div>
                    </div>';        

}
    
    






exit();


