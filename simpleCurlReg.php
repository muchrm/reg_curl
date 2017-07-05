<?php
require "simple_html_dom.php";
// Create DOM from URL or file
$dom = new simple_html_dom();
$url = 'http://reg.buu.ac.th/registrar/class_info_1.asp?coursestatus=O00&facultyid=003%A4%B3%D0%C7%D4%B7%C2%D2%C8%D2%CA%B5%C3%EC&maxrow=5000&acadyear=2559&semester=3&CAMPUSID=1&LEVELID=&coursecode=*&coursename=&cmd=2';
$dom->load_file($url);


$contents = $dom->find('tr[class=normaldetail]');
foreach ($contents as $content)
{
    
    echo $content->find('td[bgcolor=#F0F0F5]',1)->innerHtml;
    /*
    echo removeNoise($content->find('td',2)->innerHtml,['<br />'])." | "; //courseNameEng
    echo $content->find('td',3)->innerHtml." | ";//crepit(period)
    echo removeNoise($content->find('td',5)->innerHtml,['&nbsp;'])." | "; // group
    echo removeNoise($content->find('td',7)->innerHtml,['&nbsp;'])." | "; //enrollseat
    echo "\n\n";
    $times = removeNoise($content->find('td',4)->innerHtml,
                                         [
                                         '<font face="tahoma" size="1" color="#A00000">',
                                         '<font face="tahoma" size="1" color="#808080">',
                                         '<font face="tahoma" size="1" color="#5080E0">',
                                         '</font>','<br />','<b>'
                                         ]);
    $times = splitTime($times);
    foreach($times as $time){
        echo $time."\n";
    }*/
    echo "\n";
    echo "#####################################################################################################################################################################################\n\n";
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