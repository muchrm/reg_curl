<?php
require "simple_html_dom.php";
// Create DOM from URL or file
$dom = new simple_html_dom();
$url = 'http://reg.buu.ac.th/registrar/class_info_1.asp?coursestatus=O00&facultyid=003%A4%B3%D0%C7%D4%B7%C2%D2%C8%D2%CA%B5%C3%EC&maxrow=50&acadyear=2559&semester=3&CAMPUSID=1&LEVELID=&coursecode=*&coursename=&cmd=2';
$dom->load_file($url);


$contents = $dom->find('table[cellspacing=1]')[0]->find('tr');
for($i = 3;$i<count($contents)-1;$i++){
    $content = $contents[$i];
    
    $class = $content->find('td');
    if(count($class)==0){
        echo $content;
        $content = fixdom($content);
        $class = $content->find('td');
    }
    echo $class[1]."\n";
    $json = [];
    //teacher
    
    echo $class[2];
    /*$teachers = [];
    if(count($lis) > 0){
        echo $lis[0];
        $teachers = '<li>'.$lis->innerText;
        $teachers = removeNoise($teachers,['อาจารย์','MR.','ผู้ช่วยศาสตราจารย์ ดร.','รองศาสตราจารย์ ดร.','ผู้ช่วยศาสตราจารย์','ดร.','</li>']);
        $teachers = splitTeacher($teachers);
    }*/
    print_r($teachers);
    //end teacher
    echo "\n#######  END  #########\n\n";
}
function fixdom($content){
    $html = new simple_html_dom();
    $html->load(str_replace('<tr><td width=30></td><td colspan=3>[หน้าก่อน]  </td>  </tr>','',$content->outertext));
    return  $html;
}
function explode_new($text,$suflixs){
    $text = preg_replace($suflixs, "|", $text);
    $text = explode("|",$text);
    foreach($text as $key => $value){
        if($value =='')
        array_splice($text, $key, 1);
    }
    return $text;
}
function removeNoise($text,$noises){
    foreach ($noises as $noise) {
        $text = str_replace($noise,'',$text);
    }
    return $text;
}
function splitTeacher($teachers){
    return explode("<li>",$teachers);
}
function splitTime($times){
    $times = str_replace("</u>","|",$times);
    $times = str_replace([' <u>','</b>'],'##',$times);
    $times = substr($times,0,-1);
    $time  = explode("|",$times);
    if(count($time) > 0 && $time[0] != null)
    return $time;
    return [];
}
function splitUrlToArray($url){
    $remove_http = str_replace('http://', '', $url);
    $split_url = explode('?', $remove_http);
    $get_page_name = explode('/', $split_url[0]);
    $page_name = $get_page_name[1];

    $split_parameters = explode('&', $split_url[1]);

    for($i = 0; $i < count($split_parameters); $i++) {
        $final_split = explode('=', $split_parameters[$i]);
        $split_complete[$page_name][$final_split[0]] = $final_split[1];
    }
    return $split_complete;
}