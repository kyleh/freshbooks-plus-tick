<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title><?php echo $title; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo(base_url()); ?>public/stylesheets/default.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo(base_url()); ?>public/stylesheets/datepicker.css" media="screen" /> 

<link rel="stylesheet" type="text/css" media="all" href="<?php echo(base_url()); ?>public/stylesheets/grid.css" />
<link rel="stylesheet" type="text/css" media="all" href="<?php echo(base_url()); ?>public/stylesheets/system.css" />

<script src="<?php echo(base_url()); ?>public/javascript/application.js" type="text/javascript"></script>
<script language=”JavaScript” type=”text/javascript”>
function dis(a){
a.disabled = “disabled”;
}
</script>
</head>

<body>
<div class="header">
	<div class="container">
		<div class="logo-image prepend-1">
			<a class="nohover" href="/"><img src="<?php echo(base_url()); ?>public/images/freshbooks.gif" height="115" width="225" alt="FreshBooks"></a>
		</div>
		<div class="span-23">
			<ul class="tabs">
				<li><? echo anchor('tick/select_project', 'Tick Projects', $projectsActive); ?></li> 
				<li><? echo anchor('settings/index', 'Settings', $settingsActive); ?></li>
				<li class="logout"><? echo anchor('user/logout', 'Log out'); ?></li>
			</ul>
		</div>
	</div>
</div>

<div class="container" style="margin-top: 25px;">

