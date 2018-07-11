<?php
	$errors = array();
	$setupComplete = false;
	$message = "";
	$hiddenFields = "";
	function ifError($field) {
		global $errors;
		if(isset($errors[$field])&&$errors[$field]!=="") {
			echo "<div class=\"setup-error\">{$errors[$field]}</div>";
			return true;
		}
		return false;
	}
	function ifParam($field) {
		if(validParam($field))
			echo $_POST[$field];
	}
	function validParam($field) {
		return (isset($_POST[$field])&&$_POST[$field]!=="");
	}
	if(validParam('submit')) {
		if(!validParam('repo'))
			$errors['repo'] = "You must provide a repository name.";
		if(!validParam('username'))
			$errors['username'] = "You must provide an administrator username.";
		if(!validParam('password'))
			$errors['password'] = "You must provide an administrator password.";
		if(count($errors)==0) {
			$config = "../resources/config.php";
			$packages = "../Packages";
			if(validParam('bypassConfigCheck')) {
				$hiddenFields .= "<input type=\"hidden\" name=\"bypassConfigCheck\" value=\"true\">";
			}
			if(!validParam('bypassConfigCheck')&&file_exists($config)) {
				$hiddenFields .= "<input type=\"hidden\" name=\"bypassConfigCheck\" value=\"true\">";
				$message = "<div class=\"setup-error\">A configuration file already exists. Continuing will erase the previous configuration and may have undesired effects.<br/>Choose \"Create Repository\" again if you still wish to continue.</div>";
			}
			else if(!validParam('bypassPackagesCheck')&&file_exists($packages)) {
				$hiddenFields .= "<input type=\"hidden\" name=\"bypassPackagesCheck\" value=\"true\">";
				$message = "<div class=\"setup-error\">A Packages file already exists. Continuing will erase the previous Packages file and may have undesired effects.<br/>Choose \"Create Repository\" again if you still wish to continue.</div>";
			}
			else {
				$deleteFailed = false;
				if(file_exists($config)) {
					if(unlink($config)===false) {
						$deleteFailed = true;
						$message = "<div class=\"setup-error\">Unable to delete the previous config file at \"$config\". Please make sure this setup script as sufficient permissions to delete this file, or delete it manually to continue.</div>";
					}
				}
				else if(file_exists($packages)) {
					if(unlink($packages)===false) {
						$deleteFailed = true;
						$message = "<div class=\"setup-error\">Unable to delete the previous Packages file at \"$packages\". Please make sure this setup script as sufficient permissions to delete this file, or delete it manually to continue.</div>";
					}
				}
				if(!$deleteFailed) {
					if(file_put_contents($config,serialize(array('repo'=>$_POST['repo'],'username'=>$_POST['username'],'password'=>sha1($_POST['password']))))===false) {
						$message = "<div class=\"setup-error\">Unable to create a config file at \"$config\". Please make sure this setup script as sufficient permissions to create this file.</div>";
					}
					if(file_put_contents($packages,"")===false) {
						$message = "<div class=\"setup-error\">Unable to create the Packages file at \"$packages\". Please make sure this setup script as sufficient permissions to create this file.</div>";
					}
					else {
						exec("bzip2 -kf ".realpath("../Packages"));
						$setupComplete = true;
					}
				}
			}
		}
		else {
			$message = "<div class=\"setup-error\">You need to correct ".(count($errors)==1?"an error":"a few errors")." before continuing.</div>";
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Cydia™ Repository Setup</title>
		<link rel="stylesheet" type="text/css" href="../resources/styles/styles.css">
	</head>
	<body>
		<div class="content">
			<?php
				if(!is_writable("../")||!is_writable("../debs")||!is_writable("../manage")||!is_writable("../resources")||!is_writable("../setup")) {
					$basename = basename(dirname(dirname($_SERVER['PHP_SELF'])));
					echo "Before beginning setup, please ensure that the directories \"{$basename}\", \"{$basename}/debs\", \"{$basename}/manage\", \"{$basename}/resources\", and \"{$basename}/setup\" are all able to be written to by the scripts used in this program.<br/><br/>If you need help, the easiest way to do this is to set the permissions of each directory to 777.<br/><br/>Setup cannot continue until these directories are writable.";
				}
				else if(!$setupComplete) {
			?>
			<h1>Welcome</h1><br/>
			<div class="setup-description">
				You are looking at an experimental web interface designed to help you create and maintain a Cydia™ Repository.<br/><br/>
				This system is both entirely new and entirely open-source, so if you run into any issues, you can either contact one of the developers, or attempt to fix the issue yourself, then share your changes with others on the GitHub Page.<br/><br/>
			</div>
			<br/><hr/>
			<h1>Get Started</h1>
			<form action="" method="POST">
				<div class="setup-description">
					To create your repository, you will need to provide a bit of information. These settings can be changed later.<br/><br/>
					<?php if($message!==""){echo $message;} ?>
					<?php if($hiddenFields!==""){echo $hiddenFields;} ?>
					<h2>Repository Information:</h2>
					<div class="setup-field">
						<div class="setup-field-label"><label for="repoField">Repository Name:</label></div>
						<input type="text" name="repo" id="repoField" size="20" placeholder="My Fantastic Repo" value="<?php ifParam('repo'); ?>"><?php ifError('repo'); ?>
					</div>
					<br/>
					<h2>Administrator Information:</h2>
					<div class="setup-field">
						<div class="setup-field-label"><label for="usernameField">Username:</label></div>
						<input type="text" name="username" id="usernameField" size="20" value="<?php ifParam('username'); ?>"><?php ifError('username'); ?>
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="passwordField">Password:</label></div>
						<input type="password" name="password" id="passwordField" size="20" value="<?php ifParam('password'); ?>"><?php ifError('password'); ?>
					</div>
					<br/>
					<input type="submit" name="submit" value="Create Repository">
				</div>
			</form>
			<?php
				}
				else {
			?>
			<h1>Success</h1><br/>
			<div class="setup-description">
				Looks like <?php echo (validParam('repo')?('"'.$_POST['repo'].'"'):"your repository"); ?> is up and running!<br/><br/>
				You can head over to <?php $link = "http://".$_SERVER['HTTP_HOST'].(substr($_SERVER['REQUEST_URI'],strlen($_SERVER['REQUEST_URI'])-4,4)==".php"?dirname(dirname($_SERVER['REQUEST_URI'])):dirname($_SERVER['REQUEST_URI']))."/manage/"; echo "<a href=\"{$link}\">{$link}</a>"; ?> to get started!
			</div>
			<?php
				}
			?>
		</div>
	</body>
</html>