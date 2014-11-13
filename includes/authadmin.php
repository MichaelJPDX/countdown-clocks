<?php
/*****************************************************************
**  Simple security for Countdown Generators.
**
**  Copied from github site shown below
**
**	Revisions:
**		MH-20141017 Modify forms to fit site.
**					Added header & footer to most functions.
**      MH-20141020 Copied to create admin class
**
*****************************************************************/

/**
 * Class OneFileLoginApplication
 *
 * An entire php application with user registration, login and logout in one file.
 * Uses very modern password hashing via the PHP 5.5 password hashing functions.
 * This project includes a compatibility file to make these functions available in PHP 5.3.7+ and PHP 5.4+.
 *
 * @author Panique
 * @link https://github.com/panique/php-login-one-file/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class SecurityAdmin
{
    /**
     * @var string Type of used database (currently only SQLite, but feel free to expand this with mysql etc)
     */
    private $db_type = "sqlite"; //

    /**
     * @var string Path of the database file (create this with _install.php)
     */
    private $db_sqlite_path = "/var/www/includes/adminusr.db";
    private $userDB = "/var/www/includes/users.db";

    /**
     * @var object Database connection
     */
    private $db_connection = null;
    var $user_db_connection = null;

    /**
     * @var bool Login status of user
     */
    private $user_is_logged_in = false;

    /**
     * @var string System messages, likes errors, notices, etc.
     */
    public $feedback = "";


    /**
     * Does necessary checks for PHP version and PHP password compatibility library and runs the application
     */
    public function __construct()
    {
        if ($this->performMinimumRequirementsCheck()) {
            $this->runApplication();
        }
    }

    /**
     * Performs a check for minimum requirements to run this application.
     * Does not run the further application when PHP version is lower than 5.3.7
     * Does include the PHP password compatibility library when PHP version lower than 5.5.0
     * (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
     * @return bool Success status of minimum requirements check, default is false
     */
    private function performMinimumRequirementsCheck()
    {
        if (version_compare(PHP_VERSION, '5.3.7', '<')) {
            echo "Sorry, Simple PHP Login does not run on a PHP version older than 5.3.7 !";
        } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
            require_once("libraries/password_compatibility_library.php");
            return true;
        } elseif (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            return true;
        }
        // default return
        return false;
    }

    /**
     * This is basically the controller that handles the entire flow of the application.
     */
    public function runApplication()
    {
        // check is user wants to see register page (etc.)
        if (isset($_GET["action"])) {
        	switch ($_GET["action"]) {
        		case "register":
					$this->doRegistration();
					$this->showPageRegistration();
					break;
				case "delete":
					if (!isset($_GET['user'])) {
						$this->feedback = "No user name specified for deletion.";
					} else {
						$this->deleteUser();
					}
					include_once('template/header.php');
					if ($this->feedback) {
						echo "<p>" . $this->feedback . "<br/><br/></p>";
					}
					break;
			}
        } else {
            // start the session, always needed!
            $this->doStartSession();
            // check for possible user interactions (login with session/post data or logout)
            $this->performUserLoginAction();
            // show "page", according to user's login status
            if ($this->getUserLoginStatus()) {
                $this->showPageLoggedIn();
            } else {
                $this->showPageLoginForm();
            }
        }
    }

    /**
     * Creates a PDO database connection (in this case to a SQLite flat-file database)
     * @return bool Database creation success status, false by default
     */
    private function createDatabaseConnection()
    {
        try {
            $this->db_connection = new PDO($this->db_type . ':' . $this->db_sqlite_path);
            return true;
        } catch (PDOException $e) {
            $this->feedback = "PDO database connection problem: " . $e->getMessage();
        } catch (Exception $e) {
            $this->feedback = "General problem: " . $e->getMessage();
        }
        return false;
    }
    /**
     * Creates a PDO database connection to the USERS db (in this case to a SQLite flat-file database)
     * @return bool Database creation success status, false by default
     */
    function createUserDBConnection()
    {
        try {
            $this->user_db_connection = new PDO($this->db_type . ':' . $this->userDB);
            return true;
        } catch (PDOException $e) {
            $this->feedback = "PDO database connection problem: " . $e->getMessage();
        } catch (Exception $e) {
            $this->feedback = "General problem: " . $e->getMessage();
        }
        return false;
    }

    /**
     * Handles the flow of the login/logout process. According to the circumstances, a logout, a login with session
     * data or a login with post data will be performed
     */
    private function performUserLoginAction()
    {
        if (isset($_GET["action"]) && $_GET["action"] == "logout") {
            $this->doLogout();
        } elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) {
            $this->doLoginWithSessionData();
        } elseif (isset($_POST["login"])) {
            $this->doLoginWithPostData();
        }
    }

    /**
     * Simply starts the session.
     * It's cleaner to put this into a method than writing it directly into runApplication()
     */
    private function doStartSession()
    {
        session_start();
    }

    /**
     * Set a marker (NOTE: is this method necessary ?)
     */
    private function doLoginWithSessionData()
    {
        $this->user_is_logged_in = true; // ?
    }

    /**
     * Process flow of login with POST data
     */
    private function doLoginWithPostData()
    {
        if ($this->checkLoginFormDataNotEmpty()) {
            if ($this->createDatabaseConnection()) {
                $this->checkPasswordCorrectnessAndLogin();
            }
        }
    }

    /**
     * Logs the user out
     */
    private function doLogout()
    {
        $_SESSION = array();
        session_destroy();
        $this->user_is_logged_in = false;
        $this->feedback = "You were just logged out.";
    }

	/*
	**  DELETE a USER
	*/
	private function deleteUser() {
		if ($this->createUserDBConnection()) {
			$sql = "delete from users where user_name = '" . $_GET['user'] . "'";
			if ($this->user_db_connection->query($sql)) {
				$this->feedback = "User deleted.";
			} else {
				$this->feedback = "There was an error removing the user from the database.";
			}
		}
	}
    /**
     * The registration flow for regular users of the generator
     * @return bool
     */
    private function doRegistration()
    {
        if ($this->checkRegistrationData()) {
        	if (isset($_POST['type']) and $_POST['type'] == 'admin') {
				if ($this->createDatabaseConnection()) {
					$this->createNewUser($this->db_connection);
				}
			} else {
				if ($this->createUserDBConnection()) {
					$this->createNewUser($this->user_db_connection);
				}
			}
        }
        // default return
        return false;
    }

    /**
     * The registration flow
     * @return bool
     */
    private function doAdminRegistration()
    {
        if ($this->checkRegistrationData()) {
            if ($this->createDatabaseConnection()) {
                $this->createNewUser($this->db_connection);
            }
        }
        // default return
        return false;
    }

    /**
     * Validates the login form data, checks if username and password are provided
     * @return bool Login form data check success state
     */
    private function checkLoginFormDataNotEmpty()
    {
        if (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {
            return true;
        } elseif (empty($_POST['user_name'])) {
            $this->feedback = "Username field was empty.";
        } elseif (empty($_POST['user_password'])) {
            $this->feedback = "Password field was empty.";
        }
        // default return
        return false;
    }

    /**
     * Checks if user exits, if so: check if provided password matches the one in the database
     * @return bool User login success status
     */
    private function checkPasswordCorrectnessAndLogin()
    {
        // remember: the user can log in with username or email address
        $sql = 'SELECT user_name, user_email, user_password_hash
                FROM users
                WHERE user_name = :user_name OR user_email = :user_name
                LIMIT 1';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':user_name', $_POST['user_name']);
        $query->execute();

        // Btw that's the weird way to get num_rows in PDO with SQLite:
        // if (count($query->fetchAll(PDO::FETCH_NUM)) == 1) {
        // Holy! But that's how it is. $result->numRows() works with SQLite pure, but not with SQLite PDO.
        // This is so crappy, but that's how PDO works.
        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            // using PHP 5.5's password_verify() function to check password
            if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
                // write user data into PHP SESSION [a file on your server]
                $_SESSION['user_name'] = $result_row->user_name;
                $_SESSION['user_email'] = $result_row->user_email;
                $_SESSION['user_is_logged_in'] = true;
                $this->user_is_logged_in = true;
                return true;
            } else {
                $this->feedback = "Wrong password.";
            }
        } else {
            $this->feedback = "This user does not exist.";
        }
        // default return
        return false;
    }

    /**
     * Validates the user's registration input
     * @return bool Success status of user's registration data validation
     */
    private function checkRegistrationData()
    {
        // if no registration form submitted: exit the method
        if (!isset($_POST["register"])) {
            return false;
        }

        // validating the input
        if (!empty($_POST['user_name'])
            && strlen($_POST['user_name']) <= 64
            && strlen($_POST['user_name']) >= 2
            && preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])
            && !empty($_POST['user_email'])
            && strlen($_POST['user_email']) <= 64
            && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
            and (!empty($_POST['user_password_gen']) or (!empty($_POST['user_password_new']) && !empty($_POST['user_password_repeat'])))
            && ($_POST['user_password_new'] === $_POST['user_password_repeat'])
        ) {
            // only this case return true, only this case is valid
            return true;
        } elseif (empty($_POST['user_name'])) {
            $this->feedback = "Empty Username";
        } elseif (empty($_POST['user_password_gen']) and (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat']))) {
            $this->feedback = "Empty Password";
        } elseif (empty($_POST['user_password_gen']) and ($_POST['user_password_new'] !== $_POST['user_password_repeat'])) {
            $this->feedback = "Password and password repeat are not the same";
        } elseif (empty($_POST['user_password_gen']) and strlen($_POST['user_password_new']) < 6) {
            $this->feedback = "Password has a minimum length of 6 characters";
        } elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
            $this->feedback = "Username cannot be shorter than 2 or longer than 64 characters";
        } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
            $this->feedback = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
        } elseif (empty($_POST['user_email'])) {
            $this->feedback = "Email cannot be empty";
        } elseif (strlen($_POST['user_email']) > 64) {
            $this->feedback = "Email cannot be longer than 64 characters";
        } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            $this->feedback = "Your email address is not in a valid email format";
        } else {
            $this->feedback = "An unknown error occurred.";
        }

        // default return
        return false;
    }

    /**
     * Creates a new user.
     * @return bool Success status of user registration
     */
    private function createNewUser($dbConn)
    {
        // remove html code etc. from username and email
        $user_name = htmlentities($_POST['user_name'], ENT_QUOTES);
        $user_email = htmlentities($_POST['user_email'], ENT_QUOTES);
        if (isset($_POST['user_password_gen'])) $user_password = htmlentities($_POST['user_password_gen'], ENT_QUOTES);
        else $user_password = $_POST['user_password_new'];
        // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 char hash string.
        // the constant PASSWORD_DEFAULT comes from PHP 5.5 or the password_compatibility_library
        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

        $sql = 'SELECT * FROM users WHERE user_name = :user_name OR user_email = :user_email';
        $query = $dbConn->prepare($sql);
        $query->bindValue(':user_name', $user_name);
        $query->bindValue(':user_email', $user_email);
        $query->execute();

        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            $this->feedback = "Sorry, that username / email is already taken. Please choose another one.";
        } else {
            $sql = 'INSERT INTO users (user_name, user_password_hash, user_email)
                    VALUES(:user_name, :user_password_hash, :user_email)';
            $query = $dbConn->prepare($sql);
            $query->bindValue(':user_name', $user_name);
            $query->bindValue(':user_password_hash', $user_password_hash);
            $query->bindValue(':user_email', $user_email);
            // PDO's execute() gives back TRUE when successful, FALSE when not
            // @link http://stackoverflow.com/q/1661863/1114320
            $registration_success_state = $query->execute();

            if ($registration_success_state) {
                $this->feedback = "Your account has been created successfully. You can now log in.";
                sendLoginEmail($user_email, $user_name, $user_password);
                return true;
            } else {
                $this->feedback = "Sorry, your registration failed. Please go back and try again.";
            }
        }
        // default return
        return false;
    }

    /**
     * Simply returns the current status of the user's login
     * @return bool User's login status
     */
    public function getUserLoginStatus()
    {
        return $this->user_is_logged_in;
    }

    /**
     * Simple demo-"page" that will be shown when the user is logged in.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageLoggedIn()
    {
    	global $adminclass, $pagetitle;
		include_once('template/header.php');
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }

        //echo 'Hello ' . $_SESSION['user_name'] . ', you are logged in.<br/><br/>';
        //echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=logout">Log out</a>';
    }

    /**
     * Simple demo-"page" with the login form.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageLoginForm()
    {
    	$pagetitle = "Login Please";
		include_once('template/header.php');
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }
        echo '<h1>Admin Login</h1>';
		echo '<div id="inner">';
        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '" name="loginform" class="pure-form pure-form-aligned">';
        echo '	<div class="pure-control-group">';
        echo '		<label for="login_input_username">Username (or email)</label> ';
        echo '		<input id="login_input_username" type="text" name="user_name" required /> ';
        echo '	</div>';
        echo '	<div class="pure-control-group">';
        echo '		<label for="login_input_password">Password</label> ';
        echo '		<input id="login_input_password" type="password" name="user_password" required /> ';
        echo '	</div>';
        echo '	<div class="pure-controls">';
        echo '		<input type="submit"  name="login" value="Log in" class="button-go pure-button" />';
        echo '	</div>';
        echo '</form>';
        echo '</div>';
        //echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=register">Register new account</a>';
		include('template/footer.php');
		exit;
    }

    /**
     * Simple demo-"page" with the registration form.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageRegistration()
    {
    	$pagetitle = "New User Registration";
		include_once('template/header.php');
		?>
<script type="text/javascript">
	function suggestPassword(passwd_form) {
		// restrict the password to just letters and numbers to avoid problems:
		// "editors and viewers regard the password as multiple words and
		// things like double click no longer work"
		var pwchars = "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ0123456789!#$%^&";
		var passwordlength = 8;    // do we want that to be dynamic?  no, keep it simple :)
		var passwd = passwd_form.login_gen_password_new;
		passwd.value = '';

		for (var i = 0; i < passwordlength; i++ ) {
			passwd.value += pwchars.charAt( Math.floor( Math.random() * pwchars.length ) );
		}
		passwd_form.text_pma_pw.value = passwd.value;
		passwd_form.text_pma_pw2.value = passwd.value;
		return true;
	}
</script>
		<?php
		echo '<div id="inner">';
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }
        $addtype = "user";
        if (isset($_GET['type'])) $addtype = $_GET['type'];
        if (isset($_POST['type'])) $addtype = $_POST['type'];

        echo '<p class="homelink"><a href="' . $_SERVER['SCRIPT_NAME'] . '"><i class="fa fa-home"></i></a></p>';
        echo '<h1>Registration</h1>';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register" name="registerform" class="pure-form pure-form-aligned">';
        echo '	<input type="hidden" name="type" value="' . $addtype . '" />';
        echo '	<div class="pure-control-group">';
        echo '		<label for="login_input_username">Username (only letters and numbers, 2 to 64 characters)</label>';
        echo '		<input id="login_input_username" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required />';
        echo '	</div>';
        echo '	<div class="pure-control-group">';
        echo '		<label for="login_input_email">User\'s email</label>';
        echo '		<input id="login_input_email" type="email" name="user_email" required />';
        echo '	</div>';
        echo '	<div class="pure-control-group">';
        echo '		<label for="login_gen_password_new"><a href="javascript:suggestPassword(document.registerform)" class="button-go pure-button"><i class="fa fa-cogs"></i> Generate PW</a></label>';
        echo '		<input id="login_gen_password_new" class="login_input" type="text" name="user_password_gen" />';
        echo '	</div>';
        echo '	<div class="pure-control-group">';
        echo '		<label for="login_input_password_new">Password (min. 6 characters)</label>';
        echo '		<input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" autocomplete="off" />';
        echo '	</div>';
        echo '	<div class="pure-control-group">';
        echo '		<label for="login_input_password_repeat">Repeat password</label>';
        echo '		<input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" autocomplete="off" />';
        echo '	</div>';
        echo '	<div class="pure-controls">';
        echo '		<input type="submit" name="register" value="Register" class="button-go pure-button" />';
        echo '	</div>';
        echo '</form>';

        echo '</div>';
		include('template/footer.php');
		exit;
    }
}

function sendLoginEmail ($to, $uname, $pw) {
	$subject = 'Timer Generator Access';
	$crlf = "\r\n";
	$message = "Hi,\r\n\r\nYou have been granted access to the countdown timer generator.\r\nVisit http://dis.yesmail.com and log in with the following credentials:\r\n\r\n\tUser name: " . $uname . "\r\n\tPassword: " . $pw;
	$headers = 'From: no-reply@yesmail.com.com' . $crlf .
		'Bcc: michael.holland@yesmail.com' . $crlf;

	mail($to, $subject, $message, $headers);
}

// run the application
$adminclass = new SecurityAdmin();
?>