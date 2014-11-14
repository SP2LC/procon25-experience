<html>
  <head>
    <script src = "jquery-1.11.1.js"></script>
  </head>
  <body>
<?php
// MySQLに接続
require 'info.php';
global $hostname,$user,$pass,$db_name,$sougen_id;
$link = mysql_connect($hostname,$user,$pass);
if (!$link) {
  die('error');
}

$db_selected = mysql_select_db($db_name, $link);
if(!$db_selected){
  die('error');
}

$f = fopen("hand.txt", "r");
$id_str = fgets($f);
$answer_string = "";

while($temp = fgets($f)){
   $answer_string = $answer_string.$temp;
}
$answer_string=rtrim($answer_string);

#$answer_string = rtrim(fread($f, filesize("hand.txt")), "\r\n");
fclose($f);

//$_GET["score_id"] = "1228";
#$id_str = intval($_GET["prob_id"]);
$sql_splits = "SELECT columns,rows, selection_rate, exchange_rate FROM problem_info WHERE problem_id=%d";
$respon = mysql_query(sprintf($sql_splits,$id_str));
$res_arr = mysql_fetch_array($respon,MYSQL_NUM);
$id_columns = $res_arr[0];
$id_rows = $res_arr[1];
$sel_rate = $res_arr[2];
$exc_rate = $res_arr[3];
$path = sprintf("imgs/%d/",$id_str);
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
#echo $row["answer_string"];
$version = $row["version"];
#echo sprintf("<input style='position: absolute; top: 0; left: %d; 'type='button' name='reset' value='リセット' onClick='reset()'>",90 + $w * $x);
#echo sprintf("");
#echo sprintf("<pre style='position: absolute; top: 70; left: %d;'>", 10 + $w * $x);
#echo $answer_string;
#echo $version;
#echo "</pre>"
?>
<script style ="text/javascript">
  
  var spd = 200;
function reset() {

  window.location.reload();

}
  
  function parse(answer) {
  var lines = answer.split("\r\n"); // 行ごとに切り分ける
  var array = []; // 書き出し先の配列
  var i = 1; // 行番号 最初の行は飛ばす
  // すべての行を読み終えるまで
  while (i < lines.length) {
    // 選択位置を読む
    var sel = lines[i];
    var x = parseInt(sel.charAt(0), 16);  // 1文字目を16進数として読む
    var y = parseInt(sel.charAt(1), 16);  // 2文字目を16進数として読む
    i++;
    //array.push(["S", x, y]);  // 選択を追加
    array.push("S" + x+"_"+y);
    i++; // 交換の数は読み飛ばす
    // 交換操作を読む
    var exchanges = lines[i].split(""); // 1文字ごとに切り分ける
    // 交換操作を1つずつ配列に入れる
    for (var j = 0; j < exchanges.length; j++) {
      array.push(exchanges[j]);
    }
    i++;
  }
  return array;
}
var len = <?php echo $h; ?>;
var wid = <?php echo $w; ?>;

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

function wait(time) {
  var d = new $.Deferred();
  setTimeout(d.resolve, time);
  return d.promise();
}

function blink(elem, time) {
  var SINGLE_TIME = 400;
  var count = time / SINGLE_TIME;
  if (count == 0) {
    obj = elem.show();
  } else {
    var obj = elem.fadeOut(SINGLE_TIME);
    for (var i = 1; i < count; i++) {
      if (i % 2 == 0) {
        console.log("O");
        obj = obj.fadeOut(SINGLE_TIME);
      } else {
        console.log("I");
        obj = obj.fadeIn(SINGLE_TIME);
      }
    }
  }
  $.when(obj).then(function() {elem.show()});
  return $.when(obj, wait(time));
}

var i = 0;
var position;
var flag = 0;
var change_flg = 0;

var columns = <?php echo $id_columns; ?>;
var rows = <?php echo $id_rows; ?>;

function command_controll(){
  if (i >= ans_spl.length) {
    var size = Math.min(wid * columns, len * rows);
    var kansei = $("<img>", {src: "kansei.png", width: size, height: size});
    $("body").append(kansei);
    kansei.css("position", "absolute");
    kansei.css("top", 0);
    kansei.css("left", wid * columns/ 2.0 - size / 2.0);
    kansei.css("z-index", 300);
    kansei.css("display", "block");
    return;
  }
  console.log(i);
  console.log(ans_spl[i]);
  if(ans_spl[i] == "U"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0],pos_id[1]-1);
    $.when(up(position),down(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
    });
  }
  if(ans_spl[i] == "R"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0]+1,pos_id[1]);
    $.when(right(position),left(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
    });
  }
  if(ans_spl[i] == "L"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0]-1,pos_id[1]);
    $.when(left(position),right(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
      });
  }
  if(ans_spl[i] == "D"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0],pos_id[1]+1);
    $.when(down(position),up(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
        setTimeout(command_controll, 1);
      });
  }  

  if (ans_spl[i].charAt(0) == "S") {
    position = ans_spl[i].slice(1);
    flag-=1;
    change_flg+=1;
    //setTimeout(command_controll, 1);
    $.when(blink($("#" + position), sel_rate * 10)).then(function () {
      setTimeout(command_controll, 1);
    });
  }
  i++;
  //setTimeout(command_controll, spd);
}

window.onload = function(ans_str)
    {
      var blc_num = <?php echo $ans_str; ?>;
      ans_spl= parse(blc_num);
      //setInterval(command_controll,spd);
      sel_rate = <?php echo $sel_rate; ?>;
      exc_rate = <?php echo $exc_rate; ?>;
      spd = exc_rate * 10;
      command_controll();
    }
    </script>
  </body>
</html>
