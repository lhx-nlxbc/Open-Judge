<?php require("admin-header.php");

if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']))){
	echo "<a href='../loginpage.php'>Please Login First!</a>";
	exit(1);
}?>
<?php if(isset($_POST['do'])){
	require_once("../include/check_post_key.php");
	if (isset($_POST['rjpid'])){
		$rjpid=intval($_POST['rjpid']);
		if($rjpid == 0) {
		    echo "Rejudge Problem ID should not equal to 0";
		    exit(1);
		}
		$sql="UPDATE `solution` SET `result`=1 WHERE `problem_id`=? and problem_id>0";
		pdo_query($sql,$rjpid) ;
		$sql="delete from `sim` WHERE `s_id` in (select solution_id from solution where `problem_id`=?)";
		pdo_query($sql,$rjpid) ;
		$url="../status.php?problem_id=".$rjpid;
		echo "Rejudged Problem ".$rjpid;
		echo "<script>location.href='$url';</script>";
	}else if (isset($_POST['rjsid'])){
		$rjsid=intval($_POST['rjsid']);
		$sql="delete from `sim` WHERE `s_id`=?";
		pdo_query($sql,$rjsid) ;
		$sql="UPDATE `solution` SET `result`=1 WHERE `solution_id`=? and problem_id>0" ;
		pdo_query($sql,$rjsid) ;
		$sql="select contest_id from `solution` WHERE `solution_id`=? " ;
		$data=pdo_query($sql,$rjsid);
		$row=$data[0];
		$cid=intval($row[0]);
		if ($cid>0)
			$url="../status.php?cid=".$cid."&top=".($rjsid+1);
		else
			$url="../status.php?top=".($rjsid+1);
		echo "Rejudged Runid ".$rjsid;
		echo "<script>location.href='$url';</script>";
	}else if (isset($_POST['result'])){
		$result=intval($_POST['result']);
		$sql="UPDATE `solution` SET `result`=1 WHERE `result`=? and problem_id>0" ;
		pdo_query($sql,$result) ;
		$url="../status.php?jresult=1";
		echo "<script>location.href='$url';</script>";
	}else if (isset($_POST['rjcid'])){
		$rjcid=intval($_POST['rjcid']);
		$sql="UPDATE `solution` SET `result`=1 WHERE `contest_id`=? and problem_id>0";
		pdo_query($sql,$rjcid) ;
		$url="../status.php?cid=".($rjcid);
		echo "Rejudged Contest id :".$rjcid;
		echo "<script>location.href='$url';</script>";
	}
	echo str_repeat(" ",4096);
	flush();
	if($OJ_REDIS){
           $redis = new Redis();
           $redis->connect($OJ_REDISSERVER, $OJ_REDISPORT);
	   if(isset($OJ_REDISAUTH)) $redis->auth($OJ_REDISAUTH);
                $sql="select solution_id from solution where result=1 and problem_id>0";
                 $result=pdo_query($sql);
                 foreach($result as $row){
                        echo $row['solution_id']."\n";
                        $redis->lpush($OJ_REDISQNAME,$row['solution_id']);
                }
           $redis->close();     
        }
	if (isset($OJ_UDP) && $OJ_UDP) {
	  $JUDGE_SERVERS = explode(",",$OJ_UDPSERVER);
	  $JUDGE_TOTAL = count($JUDGE_SERVERS);

	  $select = $insert_id%$JUDGE_TOTAL;
	  $JUDGE_HOST = $JUDGE_SERVERS[$select];

	  if (strstr($JUDGE_HOST,":")!==false) {
	    $JUDGE_SERVERS = explode(":",$JUDGE_HOST);
	    $JUDGE_HOST = $JUDGE_SERVERS[0];
	    $OJ_UDPPORT = $JUDGE_SERVERS[1];
	  }
	  if(isset($OJ_JUDGE_HUB_PATH))
		send_udp_message($JUDGE_HOST, $OJ_UDPPORT, $OJ_JUDGE_HUB_PATH);
	  else
		send_udp_message($JUDGE_HOST, $OJ_UDPPORT, 0 );
	}
}
?>
<div class="container">
<b>Rejudge</b>
	<ol>
	<li><?php echo $MSG_PROBLEM?>
	<form action='rejudge.php' method=post>
		<input type=input name='rjpid' placeholder="1001">	<input type='hidden' name='do' value='do'>
		<input type=submit value=submit>
		<?php require_once("../include/set_post_key.php");?>
	</form>
	<li><?php echo $MSG_SUBMIT?>
	<form action='rejudge.php' method=post>
		<input type=input name='rjsid' placeholder="1002">	<input type='hidden' name='do' value='do'>
		<input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
		<input type=submit value=submit>
	</form>
	<li><?php echo "Stuck on running "?>
	<form action='rejudge.php' method=post>
		<input type=input name='result' placeholder="3" value="3">	<input type='hidden' name='do' value='do'>
		<input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
		<input type=submit value=submit>
	</form>
	<li><?php echo $MSG_CONTEST?>
	<form action='rejudge.php' method=post>
		<input type=input name='rjcid' placeholder="1003">	<input type='hidden' name='do' value='do'>
		<input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
		<input type=submit value=submit>
	</form>
</div>
