<?php
// Assuming you installed from Composer:
require "vendor/autoload.php";

require "simple_html_dom.php";
use PHPHtmlParser\Dom;

$dom = new Dom;

$url = 'http://reg.buu.ac.th/registrar/class_info_1.asp?coursestatus=O00&facultyid=003%A4%B3%D0%C7%D4%B7%C2%D2%C8%D2%CA%B5%C3%EC&maxrow=50&acadyear=2559&semester=3&CAMPUSID=1&LEVELID=&coursecode=*&coursename=&cmd=2';
$dom->loadFromUrl($url);

$contents = $dom->find('tr.normaldetail');
$courseArray = [];
foreach ($contents as $content)
{
    $courseObj = [];
    $tds = $content->find('td');

    $url2  = 'http://reg.buu.ac.th/registrar/'.$tds[1]->find('a')->getAttribute('href');
    $dom2 = new simple_html_dom();
    $dom2->load_file($url2);
    $courseObj['courseCode'] =  $tds[1]->find('a')->innerHtml;
    $courseObj['courseName']  = $dom2->find('table[class="normaldetail"]')[0]->find('tr')[1]->find('td')[1]->plaintext; //courseName
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
    $courseObj['courseNameEng']  = removeNoise($tds[2]->innerHtml,['<br />']); //courseNameEng
    $section = explode(' ',$tds[3]->innerHtml);//crepit(period)
    $periods = explode('-',$section[1]);
    $courseObj['credit'] = $section[0];
    $courseObj['period1'] = substr($periods[0],1);
    $courseObj['period2'] = $periods[1];
    $courseObj['period3'] = substr($periods[2],0,-1);
    $courseObj['section']  = removeNoise($tds[5]->innerHtml,['&nbsp;']); // group
    $courseObj['enrollseat']  = removeNoise($tds[7]->innerHtml,['&nbsp;']); //enrollseat

    
    $times = removeNoise($tds[4]->innerHtml,
                                         [
                                         '<font face="tahoma" size="1" color="#A00000">',
                                         '<font face="tahoma" size="1" color="#808080">',
                                         '<font face="tahoma" size="1" color="#5080E0">',
                                         '</font>','<br />','<b>'
                                         ]);
    $times = splitTime($times);
    foreach($times as $time){
        $time = explode("##",$time);
        $time[1] = explode('-',$time[1]);
        $courseObj['times'][] = ['day'=>$time[0],'startTime'=>$time[1][0],'finishTime'=>$time[1][1],'room'=>$time[2]];
    }
    $actual_group = "";
    $levelName='';
    foreach($dom2->find('table[class="normaldetail"]')[1]->find('tr[bgcolor=#F5F5F5],tr[bgcolor=#F5f5f5],tr[bgcolor=#FFFFF0]') as  $sec){
        if($sec->bgcolor == '#FFFFF0'){
            $levelName =  $sec->find('td')[1]->find('font')[0]->plaintext;
            continue;
        }
        $group = removeNoise($sec->find('td')[1]->plaintext,['<b>','</b>','&nbsp;']);
        if($group != ''){
            $actual_group = intval($group);
        }
        if($actual_group == removeNoise($tds[5]->innerHtml,['&nbsp;'])){
            if(count($sec->find('td[colspan=5]'))==0){
                foreach($courseObj['times'] as & $time){
                    $courseObj['levelName'] =$levelName;
                    if($time['startTime'].'-'.$time['finishTime'] == $sec->find('td')[4]->plaintext){
                        $time ['studyType'] = (($sec->find('td')[7]->plaintext == 'C')?'Lecture':($sec->find('td')[7]->plaintext == 'L')?'Lab':'');
                        break;
                    }
                }
                break;   
            }
        }
        
    }
    $courseObj['teachers'] = $teachers;
    $courseArray[]= $courseObj;
}
print_r($courseArray);
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
    $teachers = explode("<li>",$teachers);
    array_splice($teachers,0,1);
    return $teachers;
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