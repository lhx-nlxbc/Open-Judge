<?php
require_once( "./include/db_info.inc.php" );
require_once( './include/setlang.php' );
$vcode = "";
if ( isset( $_POST[ 'vcode' ] ) )$vcode = trim( $_POST[ 'vcode' ] );
if ( $OJ_VCODE && ( $vcode != $_SESSION[ $OJ_NAME . '_' . "vcode" ] || $vcode == "" || $vcode == null ) ) {
	echo "<script language='javascript'>\n";
	echo "alert('Verify Code Wrong!');\n";
	echo "history.go(-1);\n";
	echo "</script>";
	exit( 0 );
}
$view_errors = "";
require_once( "./include/login-" . $OJ_LOGIN_MOD . ".php" );
$user_id = $_POST[ 'user_id' ];
$password = $_POST[ 'password' ];
if ( get_magic_quotes_gpc() ) {
	$user_id = stripslashes( $user_id );
	$password = stripslashes( $password );
}
$sql = "SELECT `rightstr` FROM `privilege` WHERE `user_id`=?";
$login = check_login( $user_id, $password );
if ( $login ) {
	$_SESSION[ $OJ_NAME . '_' . 'user_id' ] = $login;
	$result = pdo_query( $sql, $login );

	foreach ( $result as $row )
		$_SESSION[ $OJ_NAME . '_' . $row[ 'rightstr' ] ] = true;
	$sql="update users set accesstime=now() where user_id=?";
        $result = pdo_query( $sql, $login );

	echo "<script language='javascript'>\n";
	if ( $OJ_NEED_LOGIN )
		echo "window.location.href='index.php';\n";
	else
		echo "history.go(-2);\n";
	echo "</script>";
	
	if($OJ_COOKIE_LOGIN=="true"){
		$C_info=pdo_query("SELECT`password`,`accesstime`FROM`users`WHERE`user_id`=? and defunct='N'",$login)[0];
		for
		setcookie($OJ_NAME,$C_res,time()+86400*$OJ_KEEP_TIME);
	}
} else {
	if ( $view_errors ) {
		require( "template/" . $OJ_TEMPLATE . "/error.php" );
	} else {
		echo "<script language='javascript'>\n";
		echo "alert('UserName or Password Wrong!');\n";
		echo "history.go(-1);\n";
		echo "</script>";
	}
}
?>
