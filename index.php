<?php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$url = 'http://reg.buu.ac.th/registrar/class_info_1.asp?coursestatus=O00&facultyid=003%A4%B3%D0%C7%D4%B7%C2%D2%C8%D2%CA%B5%C3%EC&maxrow=5000&acadyear=2559&semester=3&CAMPUSID=1&LEVELID=&coursecode=*&coursename=&cmd=2';
$dom->loadFromUrl($url);

$contents = $dom->find('tr.normaldetail');
foreach ($contents as $content)
{
    
    $tds = $content->find('td');
    $url2  = 'http://reg.buu.ac.th/registrar/'.$tds[1]->find('a')->getAttribute('href');
    $dom->loadFromUrl($url2);
    
    echo $tds[1]->find('a')->innerHtml." | ";
    echo $dom->find('tr.headerDetail')[1]->find('td')[1]->innerHtml." | "; //courseName
    echo "\n";

    //teacher
    $lis  = $tds[2]->find('li');
    $teachers = [];
    if(count($lis) > 0){
        $teachers = '<li>'.$lis->innerHtml;
        $teachers = removeNoise($teachers,['อาจารย์','MR.','ผู้ช่วยศาสตราจารย์ ดร.','รองศาสตราจารย์ ดร.','ผู้ช่วยศาสตราจารย์','ดร.','</li>']);
        $teachers = splitTeacher($teachers);
    }
    $tds[2]->find('font')->delete();
    //endteacher

    echo removeNoise($tds[2]->innerHtml,['<br />'])." | "; //courseNameEng
    echo $tds[3]->innerHtml." | ";//crepit(period)
    echo removeNoise($tds[5]->innerHtml,['&nbsp;'])." | "; // group
    echo removeNoise($tds[7]->innerHtml,['&nbsp;'])." | "; //enrollseat

    $studyType = null;
    $urlCourse = '';
    echo "\n*****************************\n";
    echo $dom;
    echo "\n*****************************\n";
    foreach($dom->find('tr[bgcolor=#F5F5F5]') as $index => $sec){
        
        $group = removeNoise($sec->find('td')[1]->innerHtml,['<b>','</b>','&nbsp;']);
        /*if($group == ''){
            $group = removeNoise($dom->find('tr[bgcolor=#F5F5F5]')[$index+1]->find('td')[1]->innerHtml,['<b>','</b>','&nbsp;']);
        }*/
        echo "group = ".$group;
        /*
        if($group == removeNoise($tds[5]->innerHtml,['&nbsp;']) ){
            echo ($index+1).":".$group." time:".$sec->find('td')[3]->innerHtml." ".$sec->find('td')[4]->innerHtml." ".$sec->find('td')[5]->innerHtml."\n";
        }*/
        
        /*if(removeNoise($sec->find('td')[1]->innerHtml,['<b>','</b>','&nbsp;']) == removeNoise($tds[5]->innerHtml,['&nbsp;']) ){
            $studyType = $sec->find('td')[7]->innerHtml;
            echo $sec->find('td')[3];
           /* if($sec->find('td')[3]->getAttribute('colspan')=='5') $urlCourse = $sec->find('td')[9];
            else $urlCourse = $sec->find('td')[12];
        }*/
    }
    echo (($studyType == 'C')?'Lecture':($studyType == 'L')?'Lab':'').'|';
    echo "\n url:".$urlCourse."\n";
    
    foreach($teachers as $teacher){
        echo $teacher."\n";
    }
    echo "\n\n";
    $times = removeNoise($tds[4]->innerHtml,
                                         [
                                         '<font face="tahoma" size="1" color="#A00000">',
                                         '<font face="tahoma" size="1" color="#808080">',
                                         '<font face="tahoma" size="1" color="#5080E0">',
                                         '</font>','<br />','<b>'
                                         ]);
    $times = splitTime($times);
    foreach($times as $time){
        echo $time."\n";
    }
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