<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/html1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>REforum Setup</title>
		<link rel="stylesheet" type="text/css" href="./style/default.css"/>
		<link rel="icon" type="image/png" href="./style/favicon.png"/>
	</head>
	<body>
		<center>

<?php

	if(!isSet($_POST['setup']))
	{
		?>
			<h1>REforum Setup</h1><br />
			<hr /><br />
			<h2>Welcome to REforum!</h2><br />
			<br />
			This setup script will help you configure REforum for the first time so you can begin using it.<br />
			However, first we need to make sure you understand that this software is licensed under the GNU Affero General Public License.<br />
			<br />
			<textarea class="postbox" spellcheck="false" readonly>
				<?php	readfile("./LICENSE");	?>
			</textarea>
			<br /><br />
			<form method="POST">
				I have read and understand the license terms: <input type="checkbox" value="true" name="acceptLicense" required />
				<input type="hidden" name="setup" value="1" />
				<br />
				<input type="submit" value="Continue" />
			</form>
		</center>
	</body>
</html>
		<?php
		exit();
	}
	
	$step = $_POST['setup'];
	
	if($step == 1)
	{
		if(!isSet($_POST['acceptLicense']))
			$acceptLicense = false;
		else
			$acceptLicense = boolval($_POST['acceptLicense']);
		
		if(!$acceptLicense)
		{
			?>
			<h1>You must accept the license terms.</h1>
		</center>
	</body>
</html>
			<?php
			exit();
		}
		
		?>
			<h1>REforum Setup</h1><br />
			<hr /><br />
			<h2>Checking server requirements...</h2>
			<br />
		<?php
		
		$issue = false;
		
		print("Webserver: " . $_SERVER['SERVER_SOFTWARE'] . " ... OK.");
		// if(version_compare(PHP_VERSION, '5.5.0') >= 0)
			// print("good.<br /><br />");
		// else
		// {
			// $issue = true;
			// print("bad. Minimum requirement is 5.5.0 <br /><br />");
		// }
		
		print("PHP version: " . PHP_VERSION . " ... ");
		if(version_compare(PHP_VERSION, '5.5.0') >= 0)
			print("good.<br /><br />");
		else
		{
			$issue = true;
			print("bad. Minimum requirement is 5.5.0 <br /><br />");
		}
		
		print("Checking mysqli is installed: ... ");
		if(extension_loaded('mysqli'))
			print("Yes.<br /><br />");
		else
		{
			$issue = true;
			print("No. mysqli extension must be installed for REforum to work.<br /><br />");
		}
		
		print("Checking json is installed: ... ");
		if(extension_loaded('json'))
			print("Yes.<br /><br />");
		else
		{
			$issue = true;
			print("No. json extension must be installed for REforum to work.<br /><br />");
		}
		
		if(!$issue)
		{
			?>
			REforum is compatible with this server.
			<br />
			<form method="POST">
				<input type="hidden" name="setup" value="2"/>
				<input type="submit" value="Continue"/>
			</form>
		</center>
	</body>
</html>
			<?php
			exit();
		}
		else
		{
			print("Your server has one more issues that makes it imcompatible with REforum.");
			exit();
		}
	}
	
	else if($step == 2)
	{
		function error()
		{
			$numArgs = func_num_args();
			
			if($numArgs < 1)
				return;
			
			$text = func_get_arg(0);
			
			if($numArgs > 1)
				if(func_get_arg(1))
					return "<div class=errorText>" . $text . "</div>";
				
			print("<div class=errorText>" . $text . "</div>");
		}
		
		if(!isSet($_POST['server_addr']))
		{
			?>
			<h1>REforum Setup</h1><br />
			<hr /><br />
			<h2>General configuration</h2><br />
			<div style="text-align: left; width: 75%">
				<form method="POST">
					<input type="hidden" name="setup" value="2"/>
					MySQL server: <input type="text" name="server_addr" value="localhost"/> <span class="finetext">This value should be localhost in most configurations.</span><br />
					MySQL server port: <input type="text" name="port" value="3306"/> <span class="finetext">3306 is the default port value for most configurations.</span><br />
					MySQL server username: <input type="text" name="username" /> <span class="finetext"></span><br />
					MySQL server password: <input type="password" name="password" /> <span class="finetext"></span><br />
					MySQL database name: <input type="password" name="database" /> <span class="finetext">Make sure this name isn't the same as a database being used by other software.</span><br />
					<input type="submit" value="Save" />
				</form>
			</div>
		</center>
	</body>
</html>
			<?php
		}
		else
		{
			$server = $_POST['server_addr'];
			$port = intval($_POST['port']);
			$user = $_POST['username'];
			$pass = $_POST['password'];
			$database = $_POST['database'];
			
			?>
		<h1>REforum Setup</h1><br />
		<hr /><br />
		<h2>Testing MySQL configuration</h2><br />
			<?php
			$timer = microtime();
			$mysqli = new mysqli($server, $user, $pass, "", $port);
			
			if($mysqli -> connect_error)
			{
				print(error("MySQL connection failed. Please double check the connection settings.<br />", true));
				
				?>
			<form method="POST">
				<input type="hidden" name="setup" value="2"/>
				MySQL server: <input type="text" name="server_addr" value="localhost"/> <span class="finetext">This value should be localhost in most configurations.</span><br />
				MySQL server port: <input type="range" name="port" min="1" max="65000" value="8500"/> <span class="finetext">8500 is the default port value for most configurations.</span><br />
				MySQL server username: <input type="text" name="username" /> <span class="finetext"></span><br />
				MySQL server password: <input type="password" name="password" /> <span class="finetext"></span><br />
				MySQL database name: <input type="password" name="database" /> <span class="finetext">Make sure this name isn't the same as a database being used by other software.</span><br />
				<input type="submit" value="Save" />
			</form>
		</center>
	</body>
</html>
				<?php
				exit();
			}
			
			if(($connectTime = (microtime() - $timer) * 1000) > 50)
			{
				print(error("Warning: The MySQL server took a significant amount of time to respond (${connectTime} ms). Forum performance may be sub-optimal.<br />", true));
				
				if(strtolower($server) != "localhost")
					print(error("Using a remote MySQL server will probably degrade performance. Consider using a local one.", true));
			}
			ob_flush();
			flush();
			
			$sql = "CREATE DATABASE IF NOT EXISTS ${database};";
			$result = $mysqli -> query($sql);
			if($result === false)
				exit(error("Failed to create database. " . $mysqli -> error, true));
			else
				print("Database is created...<br />\n");
			ob_flush();
			flush();
			
			$sql = "USE ${database};";
			$result = $mysqli -> query($sql);
			if($result === false)
				exit(error("Failed to select database. " . $mysqli -> error, true));
			else
				print("Selected database...<br />\n");
			ob_flush();
			flush();
			
			$sql = "CREATE TABLE IF NOT EXISTS `changes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `lastChange` int(10) DEFAULT NULL,
  `postData` mediumtext NOT NULL,
  `changeTime` varchar(30) NOT NULL,
  `postID` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";
			$result = $mysqli -> query($sql);
			if($result === false)
				exit(error("Failed to create changes table. " . $mysqli -> error, true));
			else
				print("Created changes table...<br />\n");
			ob_flush();
			flush();
			
			$sql = "CREATE TABLE IF NOT EXISTS `posts` (
  `postID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(6) DEFAULT NULL,
  `threadID` int(6) DEFAULT NULL,
  `postDate` varchar(50) DEFAULT NULL,
  `postData` mediumtext,
  `postPreparsed` mediumtext NOT NULL,
  `changeID` int(10) DEFAULT NULL,
  PRIMARY KEY (`postID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";
			$result = $mysqli -> query($sql);
			if($result === false)
				exit(error("Failed to create posts table. " . $mysqli -> error, true));
			else
				print("Created posts table...<br />\n");
			ob_flush();
			flush();
			
			$sql = "CREATE TABLE IF NOT EXISTS `topics` (
  `topicID` int(6) NOT NULL AUTO_INCREMENT,
  `creatorUserID` int(6) DEFAULT NULL,
  `topicName` varchar(130) DEFAULT NULL,
  `posts` mediumtext,
  `lastposttime` bigint(20) unsigned DEFAULT NULL,
  `lastpostid` int(11) NOT NULL,
  `numposts` int(10) NOT NULL,
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`topicID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";
			$result = $mysqli -> query($sql);
			if($result === false)
				exit(error("Failed to create topics table. " . $mysqli -> error, true));
			else
				print("Created topics table...<br />\n");
			ob_flush();
			flush();
			
			$sql = "CREATE TABLE IF NOT EXISTS `users` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `passkey` varchar(128) NOT NULL,
  `reg_date` bigint(20) unsigned NOT NULL DEFAULT '0',
  `lastActive` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(200) NOT NULL DEFAULT '',
  `verification` varchar(128) NOT NULL,
  `verified` tinyint(1) NOT NULL,
  `banned` tinyint(1) DEFAULT NULL,
  `administrator` tinyint(1) DEFAULT NULL,
  `postCount` int(6) unsigned NOT NULL,
  `profiletext` varchar(300) DEFAULT NULL,
  `profiletextPreparsed` varchar(1000) NOT NULL,
  `tagline` varchar(40) NOT NULL DEFAULT '',
  `website` varchar(200) NOT NULL DEFAULT '',
  `avatar` mediumblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=186 ;";
			$result = $mysqli -> query($sql);
			if($result === false)
				exit(error("Failed to create users table. " . $mysqli -> error, true));
			else
				print("Created users table...<br />\n");
			ob_flush();
			flush();
			
			// Make configuration file
			$json = array();
			$json['sql_server_address'] = $server;
			$json['sql_username'] = $user;
			$json['sql_password'] = $pass;
			$json['sql_database_name'] = $database;
			$json['min_password_length'] = 8; // Make this part of the setup at some point plz me
			$json['require_email_verification'] = false;
			
			$jsonText = json_encode($json, JSON_PRETTY_PRINT);
			
			if($jsonText === false)
				exit(error("Fatal error: Unable to encode json file."));
			
			if(!file_exists("./data"))
				if(mkdir("./data") === false)
					exit(error("Fatal error: Unable to create data directory. Make sure the directory REforum is installed in is writable."));
			
			if(file_put_contents("./data/settings.json", $jsonText) === false)
				exit(error("Fatal error: Unable to save settings file. Make sure the ./data directory is writable."));
			
			?>
			<br /><br />
			Server configuration completed successfully.<br />
			
			<table style="border: hidden;">
				<tr>
					<td style="border:hidden;">
						<form method="POST">
							<input type="hidden" name="setup" value="2" />
							<input type="submit" value="Redo configuration" />
						</form>
					</td>
					<td style="border:hidden;">
						<form method="POST">
							<input type="hidden" name="setup" value="3" />
							<input type="submit" value="Continue" />
						</form>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	else if($step == 3)
	{
		require_once './functions.php';
		
		if(findUserByID(0) !== false)
			exit("This step cannot be done again.");
		
		if(!isSet($_POST['username']))
		{
			?>
			<h1>REforum Setup</h1><br />
			<hr /><br />
			<h2>Create first administrator account</h2><br />
			<form method="POST">
				<input type="hidden" name="setup" value="3"/>
				Username: <input type="text" name="username" /><br />
				Password: <input type="password" name="password"/><br />
				Confirm Password: <input type="password" name="confirmpassword" /></span><br />
				Email: <input type="email" name="email" /><br />
				<input type="submit" value="Create" />
			</form>
		</center>
	</body>
</html>
			<?php
			exit();
		}
		
		
		// Verify username is OK
		$username = normalize_special_characters(strip_tags($_POST['username']));
		if(strLen($username) > 20)
		{
			error("Username is too long. Pick something under 20 characters.");
			exit();
		}
		
		// Verify email is OK
		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
			exit(error("Email address is invalid.", true));
		
		// Verify password is OK
		if($_POST['password'] !== $_POST['confirmpassword'])
		{
			error("Passwords do not match.");
			exit();
		}
		
		if(strlen($_POST['password']) < $min_password_length)
		{
			error("Error: Password is too short. Use at least ${min_password_length} characters. This is the only requirement aside from your password not being 'password'.");
			exit();
		}
		else if(stripos($_POST['password'], "password") !== false && strlen($_POST['password']) < 16)
		{
			error("You've got to be kidding me.");
			exit();
		}
		
		
		$password = password_hash(normalize_special_characters($_POST['password']), PASSWORD_BCRYPT);
		
		$mysqli = new mysqli($servername, $dbusername, $dbpassword, $dbname);
		if($mysqli -> connect_error) 
			exit(error("Connection failed: " . $mysqli -> connect_error, true));
		
		$realUsername = $username;
		$username = mysqli_real_escape_string($mysqli, $username);
		$password = mysqli_real_escape_string($mysqli, $password);
		$email = mysqli_real_escape_string($mysqli, $_POST['email']);
		$regDate = time();

		$sql = "INSERT INTO users (username, passkey, reg_date, email) VALUES ('${username}', '${password}', ${regDate}, '${email}')";

		if($mysqli -> query($sql) === TRUE) 
		{
			print("Registration completed successfully. Your username is {$realUsername}.<br><a href=\"./login.php\">Log in</a><br /><br /><br />Oh right. Yes, the setup is actually over now. Good job.");
			unlink("./setup.php"); // Delete the setup file afterwards for security reasons.
		}
		else 
			exit(error($mysqli -> error, true));
	}
	
?>