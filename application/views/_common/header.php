<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title><?php echo $title; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo(base_url()); ?>public/stylesheets/default.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo(base_url()); ?>public/stylesheets/datepicker.css" media="screen" /> 
<script src="<?php echo(base_url()); ?>public/javascript/application.js" type="text/javascript"></script>

</head>
<body>
<div id="wrap">
<div id="header">
	<img src="<?php echo(base_url()); ?>public/images/logo.png" class="logo" alt="" />
  <?php if ($navigation): ?>
	<ul>
   	<li><? echo anchor('tick/select_project', 'Projects List'); ?> |</li> 
       <li><? echo anchor('settings/index', 'Settings'); ?> | </li>
       <li><? echo anchor('user/logout', 'Logout'); ?></li>
   </ul>
 <?php endif ?>
</div>
<!-- end div header -->	
<div id="sub-header">
	<img src="<?php echo(base_url()); ?>public/images/page_divider.png" alt="" />
</div>
