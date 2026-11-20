<?php
if ($U->hasRights('system.login'))
{
	$userName = $_SESSION['userInfo']['us_name'] != '' ? $_SESSION['userInfo']['us_name'] : $_SESSION['userInfo']['us_username'];
?>
<a href="javascript:void(0);" class="user"><img id="face" src="<?php echo $U->getFace($_SESSION['userInfo']['us_face']); ?>" title="<?php echo $userName; ?>" /><span class="name"><?php echo $userName; ?></span></a>
<?php
}
else
{
?>
<img src="<?php echo $U->getFace(); ?>" /><span class="name">游客</span>
<?php
}
?>
