<?php

class User 
{
    private $db;

    public function update($username, $current_password, $email, $phone, $new_password)
    {
        $DB = new Database();
        $_SESSION['error'] = "";

        // Validate inputs as needed

        // Example validation for username and email
        if (!$this->validateUsername($username)) {
            $_SESSION['error'] = "Please enter a valid username.";
            return;
        }

        if (!$this->validateEmail($email)) {
            $_SESSION['error'] = "Please enter a valid email address.";
            return;
        }

        // Example validation for password (current and new)
        if (!$this->validatePassword($current_password)) {
            $_SESSION['error'] = "Please enter your current password.";
            return;
        }

        if (!$this->validatePassword($new_password)) {
            $_SESSION['error'] = "Please enter a valid new password.";
            return;
        }

        // Update user data in the database
        // Example query
        $query = "UPDATE users_ SET email = :email, phone = :phone, password = :new_password WHERE username = :username";
        $params = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'new_password' => password_hash($new_password, PASSWORD_DEFAULT) // Hash the new password
        ];

        $result = $DB->write($query, $params);

        if ($result) {
            $_SESSION['success'] = "Profile updated successfully.";
            header("Location: " . ROOT . "home");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update profile. Please try again.";
            header("Location: " . ROOT . "update");
            exit;
        }
    }
    function login($POST)
    {
        $DB = new Database();
        $_SESSION['error'] = "";

        // Validate username and password
        $username = trim($POST['username'] ?? '');
        $password = trim($POST['password'] ?? '');

        if(!$this->validateUsername($username)) {
            $_SESSION['error'] = "Please enter a valid username.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['phone'] = isset($POST['phone']) ? $POST['phone'] : ''; // Preserve phone
            return;
        }

        if(!$this->validatePassword($password)) {
            $_SESSION['error'] = "Please enter a valid password.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['phone'] = isset($POST['phone']) ? $POST['phone'] : ''; // Preserve phone
            return;
        }

        $arr['username'] = $username;

        $query = "SELECT * FROM users_ WHERE username = :username LIMIT 1";
        $data = $DB->read($query, $arr);

        if(is_array($data))
        {
            $user = $data[0];
            // Verify the password
            if(password_verify($password, $user->password))
            {
                // Logged in
                $_SESSION['user_name'] = $user->username;
                $_SESSION['user_url'] = $user->url_address;

                unset($_SESSION['username']); // Clear preserved username
                unset($_SESSION['phone']);    // Clear preserved phone
                header("Location:". ROOT . "home");
                die;
            } else {
                $_SESSION['error'] = "Wrong username or password.";
                $_SESSION['username'] = $username; // Preserve username
                $_SESSION['phone'] = isset($POST['phone']) ? $POST['phone'] : ''; // Preserve phone
            }
        } else {
            $_SESSION['error'] = "Wrong username or password.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['phone'] = isset($POST['phone']) ? $POST['phone'] : ''; // Preserve phone
        }
    }

    function signup($POST)
    {
        $DB = new Database();
        $_SESSION['error'] = "";

        // Validate username, email, password, and phone
        $username = trim($POST['username'] ?? '');
        $email = trim($POST['email'] ?? '');
        $password = trim($POST['password'] ?? '');
        $phone = trim($POST['phone'] ?? '');

        if(!$this->validateUsername($username)) {
            $_SESSION['error'] = "Please enter a valid username.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['email'] = $email; // Preserve email
            $_SESSION['phone'] = $phone; // Preserve phone
            return;
        }

        if(!$this->validateEmail($email)) {
            $_SESSION['error'] = "Please enter a valid email address.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['email'] = $email; // Preserve email
            $_SESSION['phone'] = $phone; // Preserve phone
            return;
        }

        if(!$this->validatePassword($password)) {
            $_SESSION['error'] = "Please enter a valid password.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['email'] = $email; // Preserve email
            $_SESSION['phone'] = $phone; // Preserve phone
            return;
        }

        // Check for existing username
        $query = "SELECT * FROM users_ WHERE username = :username LIMIT 1";
        $data = $DB->read($query, ['username' => $username]);
        if(is_array($data)) {
            $_SESSION['error'] = "Username already exists.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['email'] = $email; // Preserve email
            $_SESSION['phone'] = $phone; // Preserve phone
            return;
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        $arr['username'] = $username;
        $arr['email'] = $email;
        $arr['phone'] = $phone;
        $arr['url_address'] = get_random_string_max(60);
        $arr['date'] = date("Y-m-d H:i:s");
        $arr['password'] = $hashed_password;

        $query = "INSERT INTO users_ (username, password, email, phone, url_address, date) VALUES (:username, :password, :email, :phone, :url_address, :date)";
        $data = $DB->write($query, $arr);

        if($data)
        {
            unset($_SESSION['username']); // Clear preserved username
            unset($_SESSION['email']);    // Clear preserved email
            unset($_SESSION['phone']);    // Clear preserved phone
            header("Location:". ROOT . "login");
            die;
        } else {
            $_SESSION['error'] = "An error occurred while creating your account. Please try again.";
            $_SESSION['username'] = $username; // Preserve username
            $_SESSION['email'] = $email; // Preserve email
            $_SESSION['phone'] = $phone; // Preserve phone
        }
    }

    function validateUsername($username)
    {
        // Username validation: between 3 and 20 characters
        return (strlen($username) >= 3 && strlen($username) <= 20);
    }

    function validateEmail($email)
    {
        // Email validation using PHP filter_var function
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    function validatePassword($password)
    {
        // Password validation: one uppercase, one special character, length between 6 and 12
        return preg_match('/^(?=.*[A-Z])(?=.*\W).{6,12}$/', $password);
    }

    function check_logged_in()
    {
        $DB = new Database();
        if(isset($_SESSION['user_url']))
        {
            $arr['user_url'] = $_SESSION['user_url'];

            $query = "SELECT * FROM users_ WHERE url_address = :user_url LIMIT 1";
            $data = $DB->read($query,$arr);
            if(is_array($data))
            {
                // Logged in
                $_SESSION['user_name'] = $data[0]->username;
                $_SESSION['user_url'] = $data[0]->url_address;

                return true;
            }
        }

        return false;
    }

    function logout()
    {
        // Log out
        unset($_SESSION['user_name']);
        unset($_SESSION['user_url']);

        header("Location:". ROOT . "login");
        die;
    }

		
}
