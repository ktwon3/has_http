
<div class = "shopping_bag choose_class">
<form id="form_id" action="enrollment_process.php" method="post">
    <table border="2" width="100%" height="230" bordercolor="#000">
    <thead>
        <tr align="center" bgcolor="#298168">
            <th colspan="6"> 
            장바구니
            </th>
        </tr>
        <tr align="center" bgcolor="#30977a">
            <th class="subj">과목</th>
            <th class="class_num">분반</th> 
            <th class="time">시간</th>
            <th class="last_num">남은 인원</th>
            <th class="condition">상태</th>
            <th class="summit">신청</th>
        </tr>
    </thead>
    <?php
        require("../lib/enroll_func.php");
        require_once("../lib/sub_list.php");
        $block_dic = get_blockDicionary();
        $sql = "SELECT c_no, t_no FROM demand WHERE s_id='{$id}'";
        $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $block = get_block($id);
        while($row = mysqli_fetch_array($result)) {
            $c_no = $row[0];
            $t_no = $row[1];

            $sql2 = "SELECT t_max, c_name, t_time FROM teach WHERE c_no='{$c_no}' AND t_no='{$t_no}'";
            $result2 = mysqli_query($conn, $sql2) or die(mysqli_error($conn));
            $row2 = mysqli_fetch_row($result2);
            $t_max = $row2[0];
            $c_name = $row2[1];
            $t_time = $row2[2];

            echo "<tr>";
            $msg_array = get_condition($id, $c_no, $t_no, $block);
            $content = array($c_name, $t_no, $block_dic[$t_time], $t_max, 
            join(' ', $msg_array),null);

            $content[5] = get_radio_shopping($msg_array, $t_no, $c_no, $c_name);
                        
            foreach ($content as $i)
            {
            echo "<td> ".$i." </td>";
            }
            echo "</tr>";
        }
    ?>
    </table>
    <input id="submit_input" name="submit_input"  type="submit" value='수강 신청'>
    <input id="submit_input" name="submit_input"  type="submit" value='신청 취소'>
</form>

</div>