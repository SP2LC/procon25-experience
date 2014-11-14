<html>
  <head>
<script src ="jquery-1.11.1.js"></script>
    <title>procon25 program test page </title>
<link rel="stylesheet" href="resources/index.css">
  </head>
  <body>    
    <?php
// MySQLに接続
require 'info.php';
global $hostname,$user,$pass,$db_name;
$link = mysql_connect($hostname,$user,$pass);
if (!$link) {
  die('error');
}

$db_selected = mysql_select_db($db_name, $link);
if(!$db_selected){
  die('error');
}
//$_GET["score_id"] = "1228";
if (!isset($_GET["score_id"])) {
  die('error');
}
$sql = <<<EOS
SELECT answer_string FROM answers WHERE score_id=%d
EOS;
$res = mysql_query(sprintf($sql, intval($_GET["score_id"])));
while ($row = mysql_fetch_assoc($res)) {
  echo "<pre>";
  //echo $row["answer_string"];
  echo "</pre>";;
  $answer_string = $row["answer_string"];
}

$sql_str = "SELECT problem_id FROM score WHERE score_id=%d";
$respo = mysql_query(sprintf($sql_str,$_GET["score_id"]));
$id_str = mysql_result($respo,0);
$sql_splits = "SELECT columns,rows, selection_rate, exchange_rate FROM problem_info WHERE problem_id=%d";
$respon = mysql_query(sprintf($sql_splits,$id_str));
$res_arr = mysql_fetch_array($respon,MYSQL_NUM);
$id_columns = $res_arr[0];
$id_rows = $res_arr[1];
$sel_rate = $res_arr[2];
$exc_rate = $res_arr[3];
$path = sprintf("imgs/%d/",$id_str);

$correct = "";
function csv_read($ID){
  $x = 0;
  $y = 0;
  $ans_csv = array();
  $num = 0;
  $file = mysql_fetch_array(mysql_query(sprintf('SELECT csv_path FROM problems WHERE problem_id = %s;',$ID)));
  if($file[0] == FALSE){
    die('error');
  }
  $fp = fopen("../procon25-test/" . $file[0],"r");
  if($fp == FALSE){
    die('error');
  }
  $date = fgetcsv($fp,",");
  $x_len = $date[0];
  $y_len = $date[1];
  $sel_rate = $date[2];
  $cha_rate = $date[3];
  while(($date = fgetcsv($fp,",")) !== FALSE){
    for($y = 0;$y < $y_len;$y++ ){
      $ans_csv[$y][$x] = $date[$y];
    }
    $x++;
  }
  /*
  for($i = 0;$i < count($ans_csv);$i++){
    print_r($ans_csv[$i]);
  }
  */
  return array($ans_csv,$x_len,$y_len,$sel_rate,$cha_rate);
}
list($ans_csv, $_, $_, $_, $_) = csv_read($id_str);
function callback($row) {
  return join(",", $row);
}
$correct = join(",", array_map("callback", $ans_csv));

list($w,$h) = getimagesize($path."00.png");
for($x=0;$x<$id_columns;$x++)
  {
    for($y=0;$y<$id_rows;$y++)
      {
        $position = sprintf("%X%X.png",$x,$y);
        $img_path=sprintf("%s%s",$path,$position);
        $img_id = sprintf("%d_%d",$x,$y);
        echo sprintf("<Img Src='%s' id ='%s'  style ='position: absolute; top: %d; left: %d;'>",$img_path,$img_id,$h*$y,$w*$x);
        $blc_num1 = substr($answer_string, 2, 3);
      }
  }
$ans_str = json_encode($answer_string);
echo $row["answer_string"];
$version = $row["version"];
echo sprintf("<input style='position: absolute; top: 0; left: %d; 'type='button' name='reset' value='リセット' onClick='reset()'>",90 + $w * $x);
echo sprintf("");
echo sprintf("<pre style='position: absolute; top: 70; left: %d;'>", 10 + $w * $x);
echo $answer_string;
echo $version;
echo "</pre>"
?>
<script style ="text/javascript">
  
  var spd = 200;
function reset() {

  window.location.reload();

}
  
var len = <?php echo $h; ?>;
var wid = <?php echo $w; ?>;

var columns = <?php echo $id_columns; ?>;
var rows = <?php echo $id_rows; ?>;

function read_id(str){
  var array = str.split("_");
  var x_str = array[0];
  var y_str = array[1];
  var x = parseInt(x_str, 10);
  var y = parseInt(y_str, 10);
  return [x,y];
}

function write_id(x,y){
  return "" + x + "_" + y;
}

function up(position){
  return $("#"+position).animate({top:"-="+len},spd);
}

function right(position){
  return $("#"+position).animate({left:"+="+wid},spd);
}

function left(position){
  return $("#"+position).animate({left:"-="+wid},spd);
}
function down(position){
  return $("#"+position).animate({top:"+="+len},spd);
}

//var img_id = "<?php echo $img_id; ?>";

function exchange_id(position1,position2){
  $("#"+position1).attr('id',position2 + "_");
  $("#"+position2).attr('id',position1);
  $("#"+position2 + "_").attr('id', position2);
}

function vAdd(a, b) {
  return [a[0] + b[0], a[1] + b[1]];
}

function vNeg(a) {
  return [-a[0], -a[1]];
}

function vSub(a, b) {
  return vAdd(a, vNeg(b));
}

function vLen(a) {
  return Math.sqrt(a[0] * a[0] + a[1] * a[1]);
}

var opQueue = [];

function setEvents() {
  $("img").bind("touchstart", function (e) {
    console.log("pressed");
    e.preventDefault();
    $(e.target).css("zIndex", 100);
    var start = [$(e.target).offset().left, $(e.target).offset().top];
    var startPos = read_id($(e.target).attr("id"));
    opQueue.push("S" + $(e.target).attr("id"));
    $("img").bind("touchmove", {start: start, startMouse: [e.originalEvent.changedTouches[0].pageX, e.originalEvent.changedTouches[0].pageY]}, function(e) {
      //console.log("maojn");
      e.preventDefault();
      var mouse = [e.originalEvent.changedTouches[0].pageX, e.originalEvent.changedTouches[0].pageY];
      var diff = vSub(mouse, e.data.startMouse);
      var pos = vAdd(e.data.start, diff)
      var origin = [$(e.target).offset().left, $(e.target).offset().top];
      var start = e.data.start;
      console.log(mouse[0]);
      var pos = read_id($(e.target).attr("id"));
      var x = startPos[0];
      var y = startPos[1];
      if (mouse[0] > start[0] + wid && startPos[0] + 1 < columns) {
        console.log("R");
        start[0] += wid;
        startPos[0] += 1
        opQueue.push("R");
      } else if (mouse[0] < start[0] & startPos[0] > 0) {
        console.log("L");
        start[0] -= wid;
        startPos[0] -= 1
        opQueue.push("L");
      } else if (mouse[1] > start[1] + len & startPos[1] + 1 < rows) {
        console.log("D");
        start[1] += len;
        startPos[1] += 1
        opQueue.push("D");
      } else if (mouse[1] < start[1] & startPos[1] > 0) {
        console.log("U");
        start[1] -= len;
        startPos[1] -= 1
        opQueue.push("U");
      }
    });
    $("body").bind("touchend", function(e) {
      //console.log("uP");
      $("img").unbind("touchmove").css("zIndex", 0);
    });
  });
}

var position = [];

function command_controll() {
  if (opQueue.length == 0) {
    setTimeout(command_controll, 10);
    return;
  }
  var op = opQueue.pop();
  if(op == "U"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0],pos_id[1]-1);
    $.when(up(position),down(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
    });
  }
  if(op == "R"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0]+1,pos_id[1]);
    $.when(right(position),left(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
    });
  }
  if(op == "L"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0]-1,pos_id[1]);
    $.when(left(position),right(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
      });
  }
  if(op == "D"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0],pos_id[1]+1);
    $.when(down(position),up(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
      });
  }  

  if (op.charAt(0) == "S") {
    position = op.slice(1);
    //$.when(blink($("#" + position), exc_rate * 10)).then(function () {
      setTimeout(command_controll, 1);
    //});
  }
}

var correct = <?php echo json_encode($correct) ?>;
function check() {
}

window.onload = function(ans_str)
    {
      alert("画像に合わせて拡大または縮小してください");
      var blc_num = <?php echo $ans_str; ?>;
      //setInterval(command_controll,spd);
      sel_rate = <?php echo $sel_rate; ?>;
      spd = 75;//sel_rate * 10;
      exc_rate = <?php echo $exc_rate; ?>;
      setEvents();
      command_controll();
    }
</script>
  </body>
</html>

