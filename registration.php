<?php

$name = $email = "";
$errors = [];
$success = "";


function read_users($file) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
    }
    $data = file_get_contents($file);
    $arr = json_decode($data, true);
    return is_array($arr) ? $arr : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors['name'] = "Name is required.";
    }

    if ($email === '') {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if ($password === '') {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif (!preg_match('/[!@#$%^&*(),.?\":{}|<>]/', $password)) {
        $errors['password'] = "Password needs at least one special character.";
    }

    if ($confirm === '') {
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($confirm !== $password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $file = __DIR__ . '/users.json';
        $users = read_users($file);
        foreach ($users as $u) {
            if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
                $errors['email'] = "This email is already registered.";
                break;
            }
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $newUser = [
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'registered_at' => date('Y-m-d H:i:s')
        ];

        $users[] = $newUser;

        if (file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT)) === false) {
            $errors['file'] = "Could not save user data. Check file permissions.";
        } else {
            $success = "Registration successful!";
            $name = $email = "";
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Simple Registration</title>
  <link rel="stylesheet" type="text/css" href="Style.css">
  </head>
<body>

<h2>User Registration </h2>

<?php if ($success): ?>
  <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" action="">
  <label>Name
    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
    <div class="error"><?php echo $errors['name'] ?? ''; ?></div>
  </label>

  <label>Email
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
    <div class="error"><?php echo $errors['email'] ?? ''; ?></div>
  </label>

  <label>Password
    <input type="password" name="password">
    <div class="error"><?php echo $errors['password'] ?? ''; ?></div>
  </label>

  <label>Confirm Password
    <input type="password" name="confirm_password">
    <div class="error"><?php echo $errors['confirm_password'] ?? ''; ?></div>
  </label>

  <div class="error"><?php echo $errors['file'] ?? ''; ?></div>

  <button type="submit">Register</button>
</form>

</body>
</html>
