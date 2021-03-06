<?php
  include("inc/class.debug.php");
  $debug = new Debug;
  include("inc/class.template.php");
  $html = new Template("default","wrapper");
  $html->set("flinks", "");
	error_reporting(E_ALL);
	ini_set("log_errors", true);
	ini_set("error_log", "./php-error.log");
	ob_start();
	session_start();
	$pageTitle = "REforum";
	$metaTags = "<meta HTTP-EQUIV=\"Pragma\" content=\"no-cache\">
				<meta HTTP-EQUIV=\"Expires\" content=\"-1\">";
	include_once './header.php';
?>
<center>
<?php
	include_once 'functions.php';
	include_once 'database.php';
	include_once 'navmenu.php';
	if(isSet($_GET['action']))
	{
		switch(strToLower($_GET['action']))
		{
			case "post":
				reauthuser();

				if(!isSet($_POST['postcontent']))
				{
					error("Form error.");
				}

				else if(!isSet($_SESSION['loggedin']))
				{
					error("You don't have permission to do this action.");
				}

				else if($_SESSION['banned'] == true)
				{
					error("You are banned.");
				}

				else if(isSet($_POST['preview']))
				{
					print("Here is a preview of your post.<br>\n<table border=1 class=forumTable><tr><td class=\"postcontent\">\n");
					$postStuff = htmlentities($_POST['postcontent']);
					$preview = bb_parse(str_replace("\n", "<br>", htmlentities(html_entity_decode($postStuff))));

					print($preview);
					print("</td></tr></table><br>\n<form action=\"./?action=post&topic=${_GET['topic']}&page=${_GET['page']}\" method=POST \">
						<textarea name=\"postcontent\" class=\"postbox\">${postStuff}</textarea>
						<br>
						<input type=submit name=post value=\"Post\">
						<input type=submit name=preview value=Preview>
						</form><br>");
				}

				else if($_SESSION['lastpostingtime'] > time() - 20)
				{
					error("Please wait a minute before posting.");
				}

				else if(!isSet($_GET['topic']))
				{
					error("You need to be in a topic to post.");
				}
				else if(strLen(trim($_POST['postcontent'])) < 3)
				{
					error("Please make your post longer.");
				}
				else if(strLen(trim($_POST['postcontent'])) > 10000)
				{
					error("Your post is over the 10000 character limit.");
				}
				else if($_SESSION['lastpostdata'] == $_POST['postcontent'])
				{
					error("Oops! Looks like you already tried to post that message.");
				}
				else if(isSet($_POST['postcontent']))
				{
					$postID = createPost($_SESSION['userid'], intVal($_GET['topic']), $_POST['postcontent']);
					print("Post successful!<script> window.location = \"./?topic=${_GET['topic']}&page=${_GET['page']}#${postID}\"; </script>");
					$_SESSION['lastpostdata'] = $_POST['postcontent'];
					$_SESSION['lastpostingtime'] = time();
				}

				break;

			case "edit":
				reauthuser();

				if(!isSet($_SESSION['loggedin']))
				{
					error("You don't have permission to do this action.");
					break;
				}
				if(!isSet($_GET['post']))
				{
					error("No post specified.");
					break;
				}
				$post = fetchSinglePost(intVal($_GET['post']));
				if($post['userID'] !== $_SESSION['userid'] && !$_SESSION['admin'] == true)
				{
					error("You do not have permission to edit this post!");
					break;
				}
				else if(!isSet($_POST['editpost']))
				{
					print("Editing post<br>\n<form method=post action=\"./?action=edit&post={$_GET['post']}&topic={$_GET['topic']}&page={$_GET['page']}\" accept-charset=\"ISO-8859-1\"><textarea name=editpost class=postbox>{$post['postData']}</textarea><br>\n<input type=submit value=Edit></form>\n");
				}
				else if(strLen(trim($_POST['editpost'])) < 3)
				{
					error("Please make your post longer.");
				}
				else if(strLen(trim($_POST['editpost'])) > 10000)
				{
					error("Your post is over the 10000 character limit.");
				}
				else
				{
					editPost($post['userID'], $post['postID'], $_POST['editpost']);
					print("Post edited.<script> window.location = \"./?topic={$_GET['topic']}&page={$_GET['page']}#{$post['postID']}\"; </script>");
				}

				break;

			case "recentposts":
				getRecentPosts(0, 40);
				break;

			case "newtopic":
				reauthuser();

				if(!isSet($_SESSION['loggedin']))
				{
					error("You don't have permission to do this action.");
				}

				else if($_SESSION['banned'] == true)
				{
					error("You are banned.");
					break;
				}

				else if($_SESSION['lastpostingtime'] > time() - 20)
				{
					error("Please wait a minute before posting.");
					break;
				}

				else if(isSet($_POST['newtopicsubject']) && isSet($_POST['newtopicpost']))
				{
					if(strLen(trim($_POST['newtopicsubject'])) < 3)
					{
						error("Please make your topic title longer.");
						break;
					}
					else if(strLen(trim($_POST['newtopicsubject'])) > 130)
					{
						error("Topic title is longer than the 130 character maximum.");
						break;
					}
					else if(strLen(trim($_POST['newtopicpost'])) < 3)
					{
						error("Please make your post longer.");
						break;
					}
					else if(strLen(trim($_POST['newtopicpost'])) > 10000)
					{
						if(!$_SESSION['admin'])
						{
							error("Your post is over the 10000 character limit. Size: " . strLen(trim($_POST['newtopicpost'])));
							break;
						}
						else if(strLen(trim($_POST['newtopicpost'])) > 100000)
						{
							error("Your post is over the 100000 character hard limit. Size: " . strLen(trim($_POST['newtopicpost'])));
							break;
						}
						else
						{
							$threadID = createThread($_SESSION['userid'], $_POST['newtopicsubject'], $_POST['newtopicpost']);
							print("<script> window.location = \"./?topic={$threadID}\"; </script>");
							$_SESSION['lastpostdata'] = $_POST['newtopicsubject'];
						}
					}
					else if($_SESSION['lastpostdata'] == $_POST['newtopicsubject'])
					{
						error("Oops! Looks like you already tried to post that message.");
						break;
					}
					else
					{
						$threadID = createThread($_SESSION['userid'], $_POST['newtopicsubject'], $_POST['newtopicpost']);
						print("<script> window.location = \"./?topic={$threadID}\"; </script>");
						$_SESSION['lastpostdata'] = $_POST['newtopicsubject'];
					}
				}

				else
				{
					print("<form action=\"./?action=newtopic\" method=POST accept-charset=\"ISO-8859-1\">
							Subject: <input type=text name=newtopicsubject><br>
							Original post:<br>
							<textarea class=postbox name=newtopicpost accept-charset=\"ISO-8859-1\"></textarea><br>
							<input type=submit value=\"Create thread\">
						</form>");
				}
				break;

			case "viewprofile":
				if(!isSet($_GET['user']))
				{
					error("No profile was specified.");
					break;
				}

				displayUserProfile(intVal($_GET['user']));
				break;

			case "viewedits":
				if(!isSet($_SESSION['loggedin']))
				{
					error("You don't have permission to do this action.");
					break;
				}
				else if(!isSet($_GET['post']))
				{
					error("No post specified.");
					break;
				}
				else
				{
					displayPostEdits(intVal($_GET['post']));
				}
				break;

			case "updateprofile":
				if(!isSet($_SESSION['loggedin']))
				{
					error("You do not have permission to do this action.");
					break;
				}
				else if(!isSet($_POST['updateProfileText']))
				{
					error("Did you forget to put something here?");
					break;
				}

				updateUserProfileText($_SESSION['userid'], $_POST['updateProfileText'], $_POST['tagline'], $_POST['website']);
				displayUserProfile(intVal($_SESSION['userid']));
				break;

			case "avatarchange":
				if(isSet($_FILES['avatar']))
				{
					if($_FILES['avatar']['error'] !== UPLOAD_ERR_OK)
						error("An error occurred while uploading your avatar. Please try again.<br /><a href=\"./?action=avatarchange\">Continue</a><script> window.setTimeout(function(){window.location.href = \"./?action=avatarchange\";}, 3000);</script>");
					else if($_FILES['avatar']['size'] > 2024000)
						error("Your avatar file is too large. Try to keep it under 2MB.<br /><a href=\"./?action=avatarchange\">Continue</a><script> window.setTimeout(function(){window.location.href = \"./?action=avatarchange\";}, 3000);</script>");
					else
					{
						$location = "./data/avatartemp_${_SESSION['userid']}.dat";

						updateAvatarByID($_SESSION['userid'], $location);

						print("Avatar uploaded successfully.<br /><a href=\"./?action=viewprofile&user=${_SESSION['userid']}\">Continue</a><script> window.setTimeout(function(){window.location.href = \"./?action=viewprofile&user=${_SESSION['userid']}\";}, 3000);</script>");
					}
				}
				else
				{
					?>
					<form enctype="multipart/form-data" method="POST">
						Avatar upload: <input type="file" accept=".jpg,.png,.gif,.bmp" name="avatar" />
						<input type="submit" value="Upload" />
					</form><br />
					png, jpg, bmp, and gif files supported<br />
					Non-PNG images will be converted to PNG.<br />
					For best results, make your avatar a PNG of 100x100px or smaller.
					<?php
				}
				break;

			case "passwordchange":
				if(!isSet($_SESSION['loggedin']))
				{
					error("You have to be logged in to do this action.");
					break;
				}

				if(isSet($_POST['oldpassword']) && isSet($_POST['newpassword']) && isSet($_POST['confirmnewpassword']))
				{
					if(password_verify($_POST['oldpassword'], getPasswordHashByID($_SESSION['userid'])))
					{
						if($_POST['newpassword'] == $_POST['confirmnewpassword'])
						{
							updatePasswordByID($_SESSION['userid'], password_hash($_POST['newpassword'], PASSWORD_BCRYPT));
							print("Your password has been updated.<br /><a href=\"./?action=viewprofile&user=${_SESSION['userid']}\">Continue</a><script> window.setTimeout(function(){window.location.href = \"./?action=viewprofile&user=${_SESSION['userid']}\";}, 3000);</script>");
						}
						else
							error("The new passwords you entered didn't match.<br /><a href=\"./?action=passwordchange\">Try again</a> <script> window.setTimeout(function(){window.location.href = \"./?action=passwordchange\";}, 3000);</script>");
					}
					else
						error("Incorrect password.<br /><a href=\"./?action=passwordchange\">Try again</a> <script> window.setTimeout(function(){window.location.href = \"./?action=passwordchange\";}, 3000);</script>");
				}
				else
				{
					?>
					<form action="./?action=passwordchange" method="POST">
						Old password: <input type="password" name="oldpassword" /><br />
						New password: <input type="password" name="newpassword" /><br />
						Confirm new password: <input type="password" name="confirmnewpassword" /><br />
						<input type="submit" value="Update password" />
					</form>
					<?php
				}
				break;

			case "emailchange":
				if(isSet($_GET['code']) && isSet($_GET['id']))
				{
					if(verifyEmailChange($_GET['id'], $_GET['code']))
					{
						print("Your new email was successfully verified!");
						break;
					}
					else
					{
						error("Email verification failed.");
						break;
					}
				}
				else if(!isSet($_SESSION['loggedin']))
				{
					error("You have to be logged in to do this action.");
					break;
				}

				if(isSet($_POST['newemail']))
				{
					if(updateEmailByID($_SESSION['userid'], $_POST['newemail']))
					{
						if($require_email_verification)
							print("A confirmation email has been sent to the new email address. Please click the link in the email to confirm this change.");
						else
							print("Your email has been updated.<br /><a href=\"./?action=viewprofile&user=${_SESSION['userid']}\">Continue</a> <script> window.setTimeout(function(){window.location.href = \"./?action=viewprofile&user=${_SESSION['userid']}\";}, 3000);</script>");
					}
					else
						error("That is not a valid email address.<br /><a href=\"./?action=emailchange\">Try again</a> <script> window.setTimeout(function(){window.location.href = \"./?action=emailchange\";}, 3000);</script>");
				}
				else
				{
					?>
					<form action="./?action=emailchange" method="POST">
						Enter new email address: <input class="validate" type="email" name="newemail" />
						<input type="submit" value="Update email" />
					</form>
					<?php
				}
				break;

			case "verify":
				$error = verifyAccount($_GET['code']);

				if($error === false)
				{
					error("Unable to verify account.");
					break;
				}

				print("Account verified!");
				break;

			case "resetpassword":
				if(isSet($_GET['code']) && isSet($_GET['id']))
				{
					if(getVerificationByID($_GET['id']) !== $_GET['code'])
					{
						error("This verifcation code is invalid.");
						break;
					}
					if(!isSet($_POST['newpassword']))
					{
						?>
						<h1>Complete Password Reset</h1>
						<table border=1 style="align: center; padding: 3px;">
							<form method="POST">
								New password: <input type="password" name="newpassword" /><br />
								Confirm password: <input type="password" name="confirmpassword" /><br />
								<input type="submit" value="Change password">
							</form>
						</table>
						<?php
						break;
					}
					else
					{
						if($_POST['newpassword'] !== $_POST['confirmpassword'])
						{
							error("The passwords you entered did not match.");
							break;
						}
						else if(strlen($_POST['newpassword']) < $min_password_length)
						{
							error("Error: Password is too short. Use at least ${min_password_length} characters. This is the only requirement aside from your password not being 'password'. <br><button onclick=\"goBack()\">Try again</button>");
							break;
						}
						else if(stripos($_POST['newpassword'], "password") !== false && strlen($_POST['password']) < 16)
						{
							error("You've got to be kidding me. <br><button onclick=\"goBack()\">Try again</button>");
							break;
						}

						$newPassword = password_hash($_POST['newpassword'], PASSWORD_BCRYPT);
						updatePasswordByID($_GET['id'], $newPassword);
						clearVerificationByID($_GET['id']);

						print("Password reset completed successfully!");
						break;
					}
				}
				if(!isSet($_POST['email']))
				{
					?>
					<h1>Reset Password</h1>
					<table border=1 style="align: center; padding: 3px;">
						<form method="POST">
							Email address: <input type="text" name="email" class="validate" /><br />
							<input type="submit" value="Send reset email">
						</form>
					</table>
					<?php
					break;
				}
				$error = sendResetEmail($_POST['email']);

				if($error === false)
				{
					error("Couldn't send reset email. Contact the system administrator.");
					break;
				}

				print("Reset email sent! Please follow the link in the email to reset your password.");

				break;

			case "lockthread":
				if(!isSet($_GET['thread']))
				{
					error("No topic specified.");
					break;
				}

				$result = lockThread($_GET['thread']);
				if($result === -1)
					break;

				print(($result ? "Locked" : "Unlocked") . " thread! <script> window.setTimeout(function(){window.location.href = \"./?topic=${_GET['thread']}\";}, 1500);</script>");
				break;

			case "stickythread":
				if(!isSet($_GET['thread']))
				{
					error("No topic specified.");
					break;
				}

				$result = stickyThread($_GET['thread']);
				if($result === -1)
					break;

				print(($result ? "Sticky'd" : "Unsticky'd") . " thread! <script> window.setTimeout(function(){window.location.href = \"./?topic=${_GET['thread']}\";}, 1500);</script>");
				break;

			case "deletepost":
				if(!$_SESSION['admin'])
				{
					error("You do not have permission to do this action.");
					break;
				}

				if(!isSet($_GET['post']))
				{
					error("No post specified.");
					break;
				}

				$result = deletePost($_GET['post']);

				if(!$result)
				{
					error("Failed to delete post.");
					break;
				}

				error("Post deleted successfully.");
				break;

			case "ban":
				if(!$_SESSION['admin'])
				{
					error("You do not have permission to do this action.");
					break;
				}

				if(!isSet($_GET['id']))
				{
					error("No user id specified.");
					break;
				}

				$result = toggleBanUserByID($_GET['id']);
				print(($result ? "Banned" : "Unbanned") . " user! <script> window.setTimeout(function(){window.location.href = \"./?action=viewProfile&user=${_GET['id']}\";}, 1500);</script>");
				break;

			case "promote":
				if(!$_SESSION['admin'])
				{
					error("You do not have permission to do this action.");
					break;
				}

				if(!isSet($_GET['id']))
				{
					error("No user id specified.");
					break;
				}

				$result = togglePromoteUserByID($_GET['id']);
				print(($result ? "Promoted" : "Demoted") . " user! <script> window.setTimeout(function(){window.location.href = \"./?action=viewProfile&user=${_GET['id']}\";}, 1500);</script>");
				break;

			case "search":
				print("");
				break;

			default:
				error("Unknown action.");
				break;
		}
	}
	if(isSet($_GET['topic']))
	{
		if(!isSet($_GET['page']))
		{
			$page = 0;
		}
		else
		$page = intVal($_GET['page']);

		displayThread(intVal($_GET['topic']), $page);
	}
	else if(!isSet($_GET['action']))
	{
    include("inc/page.index.php");
		if(!isSet($_GET['page']))
			$page = 0;
		else
			$page = intVal($_GET['page']);

		$sql = "SELECT COUNT(*) FROM topics;";
		$result = querySQL($sql) -> fetch_assoc();
		$totalPages = (int)$result["COUNT(*)"] / 20;

		showRecentThreads(20 * $page, 20 * ($page + 1));

		if($page > 2)
			print('<a href="./">0</a> ... <a href="./?page=' . $page - 2 . '">' . $page - 2 . '</a> <a href="./?page=' . $page - 1 . '">' . $page - 1 . '</a>');
		else if($page == 2)
			print(' <a href="./?page=' . $page - 2 . '">' . $page - 2 . '</a> <a href="./?page=' . $page - 1 . '">' . $page - 1 . '</a>');
		else if($page == 1)
			print(' <a href="./?page=' . $page - 1 . '">' . $page - 1 . '</a> ');

		if($totalPages > 1)
			print("[${page}]");

		if($page < $totalPages - 3)
			print('<a href="./?page=' . $page + 1 . '">' . $page + 1 . '</a> <a href="./?page=' . $page + 2 . '">' . $page + 2 . '</a> ... <a href="./?page=' . $totalPages - 1 . '">' . $totalPages - 1 . '</a>');
		else if($page == $totalPages - 3)
			print('<a href="./?page=' . $page + 1 . '">' . $page + 1 . '</a> <a href="./?page=' . $page + 2 . '">' . $page + 2 . '</a>');
		else if($page == $totalPages - 2)
			print('<a href="./?page=' . $page + 1 . '">' . $page + 1 . '</a>');

		if(isSet($_SESSION['loggedin']))
			print("<br><br><a href=\"./?action=newtopic\">Post a new topic</a>\n");

		print("<br><br><a href=\"./?action=recentPosts\">Show all recent posts</a>\n");

		if(isSet($_SESSION['admin']))
			if($_SESSION['admin'])
				print("<br><a href=\"./admin.php\">Admin</a>");
	}
  disconnectSQL();
  $debug->end();
  $dbstring = "Page created in ". round($debug->tFlag * 1000) ." milliseconds with ". $_mysqli_numQueries ." " . ($_mysqli_numQueries == 1 ? "query" : "queries");
  $html->set("copyright", "REforum is &copy;2017 pecon.us <a href='./about.html'>About</a>");
  $html->set("debug.string", $dbstring);
  echo $html->output();
?>