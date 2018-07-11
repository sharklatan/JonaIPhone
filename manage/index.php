<?php
	$config = "../resources/config.php";
	$packages = "";
	$message = "";
	$fatal = false;
	$loggedIn = false;
	$repo = "Cydia™ Repository";
	$info;
	$errors = array();
	$showAddDiv = 0;
	function ifError($field,$add="") {
		global $errors;
		if(isset($errors[$field])&&$errors[$field]!=="") {
			echo "<div class=\"setup-error\">{$errors[$field]}</div>".$add;
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
	function sortPackages($a, $b) {
		if ($a['Name'] == $b['Name']) {
			return 0;
		}
		return ($a['Name'] < $b['Name']) ? -1 : 1;
	}
	if(!file_exists($config)) {
		$link = "http://".$_SERVER['HTTP_HOST'].(substr($_SERVER['REQUEST_URI'],strlen($_SERVER['REQUEST_URI'])-4,4)==".php"?dirname(dirname($_SERVER['REQUEST_URI'])):dirname($_SERVER['REQUEST_URI']))."/setup/";
		$message = "<div class=\"setup-description\">Unable to load the config file at \"$config\".<br/><br/>Please make sure you have already run the setup script at <a href=\"{$link}\">{$link}</a>.<br/>If you have, then make sure the file exists and is readable by this script.";
		$fatal = true;
	}
	else if(empty(shell_exec("which dpkg"))) {
		$message = "<div class=\"setup-error\">Could not find the dpkg command. Please make sure you are using a linux server with dpkg installed.</div>";
		$fatal = true;
	}
	else {
		if(file_exists("../setup/index.php")&&unlink("../setup/index.php")===false) {
			$message = "<div class=\"setup-error\">Unable to delete the setup script at \"../setup/index.php\".<br/><br/>For security reasons, this script must be deleted before continuing.<br/>Please delete the script yourself, or make sure this script has the correct permissions to do so.";
			$fatal = true;
		}
		else {
			$info = unserialize(file_get_contents($config));
			if($info===false) {
				$message = "<div class=\"setup-error\">Unable to read the config file at \"$config\". Please make sure that this script has permission to view this file and that the file is not corrupted.</div>";
				$fatal = true;
			}
			else {
				session_start();
				if(validParam('logout')) {
					session_unset();
					session_destroy();
					$loggedIn = false;
				}
				else if(validParam('login')) {
					if(!validParam('username'))
						$errors['username'] = "You must provide an administrator username.";
					if(!validParam('password'))
						$errors['password'] = "You must provide an administrator password.";
					if(count($errors)==0) {
						$_SESSION['username'] = $_POST['username'];
						$_SESSION['password'] = sha1($_POST['password']);
					}
					else {
						$message = "<div class=\"setup-error no-indent\">You need to correct ".(count($errors)==1?"an error":"a few errors")." before continuing.</div>";
					}
				}
				else if(validParam('add')) {
					$showAddDiv = 1;
					$allowedCompressedTypes = array("application/zip", "application/x-zip", "application/octet-stream", "application/x-zip-compressed");
					if(!isset($_FILES["upload"])||!in_array($_FILES["upload"]["type"], $allowedCompressedTypes)) {
						$errors['upload'] = "You must upload a .zip file to continue.";
					}
					else {
						$zip = new ZipArchive;
						if($zip->open($_FILES["upload"]["tmp_name"]) === TRUE) {
						    $zip->extractTo($_FILES["upload"]["tmp_name"].'extract/');
						    $validControl = false;
						    if(file_exists($_FILES["upload"]["tmp_name"].'extract/DEBIAN/control')) {
						    	$validControl = true;
						    }
						    else if(validParam('controlFields')) {
						    	if(!validParam('name'))
									$errors['name'] = "You must provide a package name.";
								if(!validParam('identifier'))
									$errors['identifier'] = "You must provide a bundle identifier.";
								if(!validParam('description'))
									$errors['description'] = "You must provide a description for your package.";
								if(!validParam('version'))
									$errors['version'] = "You must provide a version number.";
								if(count($errors)==0)
									$validControl = true;
						    }
						    if(!$validControl) {
						    	$showAddDiv = 2;
						    }
						    else {
						    	$success = true;
						    	$controlText = "";
						    	$packageName;
						    	$packageIdentifier;
						    	$packageDescription;
						    	$packageVersion;
							    if(!file_exists($_FILES["upload"]["tmp_name"].'extract/DEBIAN/control')) {
							    	$packageName = $_POST['name'];
						    		$packageIdentifier = $_POST['identifier'];
						    		$packageDescription = $_POST['description'];
						    		$packageVersion= $_POST['version'];
							    	$controlSections = array('identifier'=>'Package','name'=>'Name','version'=>'Version','description'=>'Description','author'=>'Author','maintainer'=>'Maintainer','predepends'=>'Pre-Depends','depends'=>'Depends','conflicts'=>'Conflicts','replaces'=>'Replaces');
							    	foreach ($controlSections as $key => $value) {
									    if(validParam($key))
									    	$controlText .= ($value.": ".$_POST[$key]."\n");
									}
									$controlText .= "Architecture: iphoneos-arm\n";
									if (!file_exists($_FILES["upload"]["tmp_name"].'extract/DEBIAN')) {
    									mkdir($_FILES["upload"]["tmp_name"].'extract/DEBIAN', 0777, true);
									}
									if(file_put_contents($_FILES["upload"]["tmp_name"].'extract/DEBIAN/control', $controlText)===false) {
										$errors['upload'] = "Unable to write control file. Please make sure this script has sufficient permissions.";
										$success = false;
									}
								}
								else {
									$controlText = file_get_contents($_FILES["upload"]["tmp_name"].'extract/DEBIAN/control');
									if($controlText === false) {
										$errors['upload'] = "Unable to read control file. Please make sure this script has sufficient permissions.";
										$success = false;
									}
									else {
										$missing = "";
										preg_match("#Name: ([^\n]*)\n#",$controlText,$packageNameResults);
										$packageName = $packageNameResults[1];
										preg_match("#Package: ([^\n]*)\n#",$controlText,$packageIdentifierResults);
										$packageIdentifier = $packageIdentifierResults[1];
										preg_match("#Description: ([^\n]*)\n#",$controlText,$packageDescriptionResults);
										$packageDescription = $packageDescriptionResults[1];
										preg_match("#Version: ([^\n]*)\n#",$controlText,$packageVersionResults);
										$packageVersion = $packageVersionResults[1];
										if(empty($packageName)||empty($packageIdentifier)||empty($packageDescription)||empty($packageVersion)||!preg_match("#Architecture: ([^\n]*)\n#",$controlText)) {
											$errors['upload'] = "The included control file did not contain the minimum necessary fields (Name, Package, Description, Version, and Architecture). Please make sure these fields are all present and try again.";
											$success = false;
										}
									}
								}
								$packages = "../Packages";
								if($success&&!is_writable($packages)) {
									$errors['upload'] = "Unable to read the Packages file at {$packages}. Please make sure this script has sufficient permissions.";
									$success = false;
								}
								if($success) {
									$packagesText = file_get_contents($packages);
									if(strpos($packagesText,"Name: {$packageName}\n")!==false||strpos($packagesText,"Package: {$packageIdentifier}\n")!==false) {
										$errors['upload'] = "A package with that name and/or identifier already exists. Please delete it before continuing.";
									}
									else {
										if(file_exists($_FILES["upload"]["tmp_name"].'extract/__MACOSX')) {
											exec('/bin/rm -rf ' . escapeshellarg($_FILES["upload"]["tmp_name"].'extract/__MACOSX'));
										}
										exec('cd '.sys_get_temp_dir().'; dpkg-deb -b '.$_FILES["upload"]["tmp_name"].'extract');
										$filename = '/debs/'.rawurldecode($_POST['identifier']).'.deb';
										rename($_FILES["upload"]["tmp_name"].'extract.deb','..'.$filename);
										if(file_put_contents($packages,preg_replace("#\n\n\n\n|\n\n\n|\n\n#","\n\n",trim($packagesText."\n\n{$controlText}Filename: {$filename}\n\n"))."\n")===false) {
											$errors['upload'] = "Unable to write to the Packages file at {$packages}. Please make sure this script has sufficient permissions.";
										}
							    		else {
							    			exec("bzip2 -kf ".realpath("../Packages"));
							    			$showAddDiv = 0;
							    		}
						    		}
						    	}
						    }
						}
						else {
						    $errors['upload'] = "Unable to read the .zip, make sure it is not corrupted.";
						}
						$zip->close();
					}
				}
				else if(validParam('delete')&&validParam('identifier')) {
					$packages = explode("\n\n",file_get_contents("../Packages"));
					foreach($packages as $i => $package) {
						if(strpos($package,"Package: {$_POST['identifier']}\n")!==false) {
							unset($packages[$i]);
						}
					}
					if(file_put_contents("../Packages", implode("\n\n",$packages))===false) {
						$errors['upload'] = "Unable to write to the Packages file at {$packages}. Please make sure this script has sufficient permissions.";
					}
					else {
						exec("bzip2 -kf ".realpath("../Packages"));
					}
				}
				if($message===""&&isset($_SESSION['username'])&&$_SESSION['username']!==""&&isset($_SESSION['password'])&&$_SESSION['password']!=="") {
					if($info['username']==$_SESSION['username']&&$info['password']==$_SESSION['password']) {
						$loggedIn = true;
						$repo = $info['repo'];
					}
					else {
						$_SESSION['username'] = "";
						$_SESSION['password'] = "";
						$message = "<div class=\"setup-error no-indent\">Your credentials do not appear to be valid. Please try logging in again.</div>";
					}
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $repo; ?> Management</title>
		<link rel="stylesheet" type="text/css" href="../resources/styles/styles.css">
	</head>
	<body>
		<div class="content">
			<?php
				if($message!="") {
					echo $message."<br/><br/>";
				}
				if(!$fatal&&!$loggedIn) {
			?>
			<form action="" method="POST">
				You must login to view this page.
				<br/><br/>
				<div class="setup-field">
					<div class="setup-field-label"><label for="usernameField">Username:</label></div>
					<input type="text" name="username" id="usernameField" size="20" value="<?php ifParam('username'); ?>"><?php ifError('username'); ?>
				</div>
				<div class="setup-field">
					<div class="setup-field-label"><label for="passwordField">Password:</label></div>
					<input type="password" name="password" id="passwordField" size="20" value="<?php ifParam('password'); ?>"><?php ifError('password'); ?>
				</div>
				<br/>
				<input type="submit" name="login" value="Login">
			</form>
			<?php
				}
				else if(!$fatal) {
			?>
			<form action="" method="POST"><div class="welcome-banner"><h1 class="welcome-banner">Welcome, <?php echo $_SESSION['username']; ?>.</h1><div class="logout-button-wrapper"><input class="logout-button" type="submit" name="logout" value="Logout"></div></div></form><br/><hr/><br/>
			<h2>Repository Packages:</h2>
			<?php
				$packagesRawText = file_get_contents("../Packages");
				if(empty($packagesRawText)) {
					echo "You haven't added any packeges yet. Click \"Add A Package\" below to get started!<br/><br/>";
				}
				else {
					$packagesRaw = explode("\n\n",$packagesRawText);
					$packages = array();
					foreach($packagesRaw as $i => $package) {
						preg_match_all("#([^:]*): ([^\n]*)\n#", $package, $matches);
						$requiredKeys = array("Name","Package","Description","Version","Architecture");
						foreach($requiredKeys as $requiredKey) {
							if(!in_array($requiredKey, $matches[1])) {
								unset($packages[$i]);
								break;
							}
						}
						$packages[] = array_combine($matches[1],$matches[2]);
					}
					usort($packages, "sortPackages");
					$i=0;
					foreach($packages as $package) {
						$i++;
						echo "<form action=\"\" method=\"POST\"><input type=\"hidden\" name=\"identifier\" value=\"{$package['Package']}\"><div class=\"package\" onmouseover=\"showPackageDelete('package{$i}delete');\" onmouseout=\"hidePackageDelete('package{$i}delete');\"><div class=\"package-icon\"></div><div class=\"package-text\">".$package['Name']."</div><button type=\"submit\" name=\"delete\" value=\"true\" class=\"package-delete\" style=\"display:none;\" id=\"package{$i}delete\"><img src=\"../resources/images/PackageDelete.png\" alt=\"delete\"/></button></div></form><br/>";
					}
				}
			?>
			<form action="" method="POST" onsubmit="return checkAddPackage();" enctype="multipart/form-data">
			<div id="addDiv"<?php if($showAddDiv==0){echo "style=\"display:none\"";} ?>><div class="hr-container"><hr/></div><br/>
				<div class="setup-field">
					<?php ifError('upload',"<br/><br/>");if($showAddDiv>1){echo "<div class=\"setup-error\">Your .zip appears to be fine, but make sure to reupload it before submitting the fields below!</div><br/><br/>";} ?>
					<div class="setup-field-label"><label for="uploadField">Package:</label></div>
					<input type="file" name="upload" id="uploadField" size="20" accept=".zip"><br/>
					<div class="setup-note">The package should be uploaded in a .zip format, without any extra folders included in the root.<br/>(e.g., the first folders visible in the .zip should be system folders like "Library", or "etc", not a folder such as "My Theme")</div>
				</div>
				<div id="addDiv2"<?php if($showAddDiv<2){echo "style=\"display:none\"";} ?>>
					<?php if($showAddDiv>=2){echo "<input type=\"hidden\" name=\"controlFields\" value=\"true\">";} ?>
					<br/>Your package does not seem to contain a control file. That's perfectly fine — just fill out the fields below.
					<div class="setup-field">
						<div class="setup-field-label"><label for="nameField">Package Name:</label></div>
						<input type="text" name="name" id="nameField" size="60" placeholder="My Cydia Package" value="<?php ifParam('name'); ?>"><?php ifError('name'); ?>
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="identifierField">Bundle Identifier:</label></div>
						<input type="text" name="identifier" id="identifierField" size="60" placeholder="com.yourname.name" value="<?php ifParam('identifier'); ?>"><?php ifError('identifier'); ?>
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="descriptionField">Description:</label></div>
						<input type="text" name="description" id="descriptionField" size="60" placeholder="A fun thing!" value="<?php ifParam('description'); ?>"><?php ifError('description'); ?>
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="versionField">Version Number:</label></div>
						<input type="text" name="version" id="versionField" size="60" placeholder="1.0" value="<?php ifParam('version'); ?>"><?php ifError('version'); ?>
					</div><br/>
					The following fields are optional. These should be left blank unless you understand what they are for.
					<div class="setup-field">
						<div class="setup-field-label"><label for="authorField">Author:</label></div>
						<input type="text" name="author" id="authorField" size="60" placeholder="Your Name <you@example.com>" value="<?php ifParam('author'); ?>">
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="maintainerField">Maintainer:</label></div>
						<input type="text" name="maintainer" id="maintainerField" size="60" placeholder="Your Name <you@example.com>" value="<?php ifParam('maintainer'); ?>">
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="predependsField">Pre-Depends:</label></div>
						<input type="text" name="predepends" id="predependsField" size="60" value="<?php ifParam('predepends'); ?>">
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="dependsField">Depends:</label></div>
						<input type="text" name="depends" id="dependsField" size="60" value="<?php ifParam('depends'); ?>">
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="conflictsField">Conflicts:</label></div>
						<input type="text" name="conflicts" id="conflictsField" size="60" value="<?php ifParam('conflicts'); ?>">
					</div>
					<div class="setup-field">
						<div class="setup-field-label"><label for="replacesField">Replaces:</label></div>
						<input type="text" name="replaces" id="replacesField" size="60" value="<?php ifParam('replaces'); ?>">
					</div>
				</div>
			</div><br/><input type="submit" name="add" value="Add A Package"></form>
			<br/><hr/><br/>
			<h2>Repository Settings:</h2>
			Coming Soon!<br/>
			<br/><hr/><br/>
			<h2>Administrator Account Settings:</h2>
			Coming Soon!<br/><br/>
			<script type="text/javascript">
				function checkAddPackage() {
					if(document.getElementById('addDiv').style.display=='none') {
						document.getElementById('addDiv').style.display = 'block';
						return false;
					}
					return true;
				}
				function showPackageDelete(id) {
					document.getElementById(id).style.display = 'block';
				}
				function hidePackageDelete(id) {
					document.getElementById(id).style.display = 'none';
				}
			</script>
			<?php
				}
			?>
		</div>
	</body>
</html>