<?php

use Google\Service\Monitoring\Custom;

require_once('utill.php');
function get_teach_dict(){ 
    /*확률 계산 시 쿼리 중복을 피하기 위한 teach_dict 생성
    teach_dict[$c_no][$t_no] = (t_dem, t_max)
    */
    
    $result = custom_query("SELECT c_no, t_no, t_dem, t_max FROM teach");
    $teach_dict = array();
    while ($row = mysqli_fetch_row($result)){
        $c_no = $row[0];
        $t_no = $row[1];
        $t_dem = $row[2];
        $t_max = $row[3];

        if (!isset($teach_dict[$c_no])){
            $teach_dict[$c_no] = array();
        }
        $teach_dict[$c_no][$t_no] = array($t_dem, $t_max);
    }
    $result = custom_query("SELECT c_no, count(t_no) FROM teach group by c_no;");
    while($row = mysqli_fetch_row($result)){
        $teach_dict[$row[0]]['count'] = $row[1]; 
    }
    return $teach_dict;
}

function get_probability($teach_dict, $demand_arr){
    $proballity = 1;
    foreach ($demand_arr as $c_no => $t_no){
        if (!isset($t_no)){
            return -1;
        }
        $temp = $teach_dict[$c_no][$t_no];
        $t_dem = $temp[0];
        $t_max = $temp[1];
        if ($t_max < $t_dem){
            $proballity *= $t_max / $t_dem;
        }
    }
    return $proballity;
}

function get_probability_improved($teach_dict, $demand_arr){ //개선된 것

    $proballity = 1;
    foreach ($demand_arr as $c_no => $t_no){
        if (!isset($t_no)){
            return -1;
        }
        $temp = $teach_dict[$c_no][$t_no];
        $t_dem = $temp[0];
        $t_max = $temp[1];

        $sql = "SELECT count(s_id) FROM demand WHERE c_no = {$c_no} AND t_no = {$t_no}";
        $demand_count = mysqli_fetch_row(custom_query($sql))[0];

        $x = $teach_dict[$c_no]['count'];
        $temp2 = ($t_max * $x) - $demand_count;

        $virtual_num = ($temp2 - ($temp2 % $x)) / $x;
        
        $t_dem += $virtual_num;
        
        if ($t_max < $t_dem){
            $proballity *= $t_max / $t_dem;
        }
    }
    return $proballity;
}

function get_count_arr(){
    // count_arr[$c_no][$t_no] = count(s_id)
    $sql = "SELECT teach.c_no, teach.t_no, count(s_id)  FROM teach LEFT OUTER JOIN demand ON demand.c_no = teach.c_no AND demand.t_no = teach.t_no group by teach.c_no, teach.t_no";
    $result = custom_query($sql);
    $count_arr = array();
    
    
    while ($row = mysqli_fetch_row($result)){
        $c_no = $row[0];
        if (!isset($count_arr[$c_no])){
            $count_arr[$c_no] = array();
        }
        $count_arr[$c_no][$row[1]] = $row[2]; 
    }

    return $count_arr;
}

function get_probability_improved_fast($teach_dict, $demand_arr, $count_arr){ // 더 개선된 것
    

    $proballity = 1;
    foreach ($demand_arr as $c_no => $t_no){
        if (!isset($t_no)){
            return -1;
        }
        $temp = $teach_dict[$c_no][$t_no];
        $t_dem = $temp[0];
        $t_max = $temp[1];

        $demand_count = $count_arr[$c_no][$t_no];

        $x = $teach_dict[$c_no]['count'];
        $temp2 = ($t_max * $x) - $demand_count;

        $virtual_num = ($temp2 - ($temp2 % $x)) / $x;
        
        $t_dem += $virtual_num;
        
        if ($t_max < $t_dem){
            $proballity *= $t_max / $t_dem;
        }
    }
    return $proballity;
}

function get_all_probability($all_tno_list){
    $teach_dict = get_teach_dict();
    $arr = array();
    $count_arr = get_count_arr();

    foreach($all_tno_list as $tno_list){
        array_push($arr, get_probability_improved_fast($teach_dict, $tno_list, $count_arr));
    }
    return $arr;
}

function get_fixed_tno(){ //고정된 arr[c_no] = t_no 리턴
    $s_id = $_SESSION['user_id'];
    $sql = "SELECT c_no, t_no FROM fix_subj where s_id = '{$s_id}' and t_no is not null";

    $result = custom_query($sql);
    $fixed_tno = array();
    while ($row = mysqli_fetch_row($result)){
        $fixed_tno[$row[0]] = $row[1];
    }
    return $fixed_tno;

}

function get_fix_selected($c_no) { // 고정된 분반만 'selected' 아니면 ''을 가진 array
    $s_id = $_SESSION['user_id'];

    $sql = "SELECT t_no FROM fix_subj WHERE c_no = {$c_no} AND s_id = '{$s_id}' AND t_no is not null";
    $result = custom_query($sql);
    if (isset($result)){
        $t_no = mysqli_fetch_row($result)[0];
    }
    else{
        $t_no = 0;
    }

    $count = mysqli_fetch_row(custom_query("SELECT count(t_no) FROM teach WHERE c_no = {$c_no}"))[0];
    $count += 1;

    $arr = array();
    for ($i=0;$i<$count;$i++){
        if ($i == $t_no){
            array_push($arr, 'selected');
        }
        else{
            array_push($arr, '');
        }
    }
    return $arr;
}
?>