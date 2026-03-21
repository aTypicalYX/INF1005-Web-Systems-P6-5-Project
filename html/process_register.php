<?php
session_start();

$errorMsg = '';
$success = true;

// 1. Database Connection
$dbPath = file_exists(__DIR__ . '/../config/db.php') ? __DIR__ . '/../config/db.php' : dirname(__DIR__) . '/config/db.php';
if (!file_exists($dbPath)) {
    $_SESSION['error'] = "System error: Database config not found.";
    header("Location: signup.php");
    exit;
}
require_once $dbPath;
require_once __DIR__ . '/includes/profanity.php'; // Updated to use __DIR__ for reliable pathing

try {
    if (!isset($pdo) || $pdo === null) {
        throw new Exception("Database connection is not available.");
    }

    // 2. Collect Account Basics
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic Account Constraints
    if (strlen($password) < 8) {
        throw new Exception("Password must be at least 8 characters long.");
    }
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        throw new Exception("Username must be between 3 and 20 characters and contain only letters, numbers, and underscores.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    $domain = substr(strrchr($email, "@"), 1);
    if ($domain !== 'sit.singaporetech.edu.sg') {
        throw new Exception("Only SIT email addresses (@sit.singaporetech.edu.sg) are allowed.");
    }

    // Profanity check on registration fields
    $fieldsToCheck = [
        'First name'    => $firstName,
        'Last name'     => $lastName,
        'Username'      => $username,
        'Display name'  => trim($_POST['display_name'] ?? ''),
        'Biography'     => trim($_POST['biography'] ?? ''),
        'Location'      => trim($_POST['location'] ?? ''),
        'Occupation'    => trim($_POST['occupation'] ?? ''),
        'Favourite song'=> trim($_POST['favourite_song'] ?? ''),
    ];

    foreach ($fieldsToCheck as $fieldName => $value) {
        if (containsProfanity($value, $profanityList, $wholeWordOnly)) {
            throw new Exception("{$fieldName} contains inappropriate language. Please revise it.");
        }
    }

    // Validate uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        throw new Exception("An account with that username or email already exists.");
    }

    // Start Transaction
    $pdo->beginTransaction();

    // 3. Insert into `users` table
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'user', NOW(), NOW())");
    $stmt->execute([$firstName, $lastName, $username, $email, $hashedPassword]);
    $userId = $pdo->lastInsertId();

    // 4. Handle Specific Photo Upload Slots
    $uploadDir = __DIR__ . '/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $finalImages = array_fill(0, 6, null); 
    $inputNames = ['main_image', 'image_2', 'image_3', 'image_4', 'image_5', 'image_6'];
    
    $uploadedFiles = []; // IMPROVEMENT: Track files to delete if transaction fails
    $finfo = new finfo(FILEINFO_MIME_TYPE); // IMPROVEMENT: Initialize MIME checker

    foreach ($inputNames as $index => $inputName) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES[$inputName]['tmp_name'];
            $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
            
            // Strictly check the actual file contents, not just the name
            $mime = $finfo->file($tmpName);
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

            if (in_array($ext, $allowedExts) && in_array($mime, $allowedMimes)) {
                $newName = 'user_' . $userId . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                    $finalImages[$index] = $newName;
                    $uploadedFiles[] = $uploadDir . $newName; // Track it!
                }
            } else {
                throw new Exception("Invalid file format uploaded for image slot " . ($index + 1));
            }
        }
    }

    if (empty($finalImages[0])) {
        throw new Exception("A main profile picture is required to create an account.");
    }

    // 5. Insert into `profile` table
    $stmtProf = $pdo->prepare('
        INSERT INTO profile (
            user_id, display_name, age, gender, pronouns, location, occupation, 
            education, love_language, height, biography, pets, workout, social_media, favourite_song, 
            main_image, image_2, image_3, image_4, image_5, image_6, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    $stmtProf->execute([
        $userId, 
        trim($_POST['display_name'] ?? ''), 
        (int)($_POST['age'] ?? 0), 
        trim($_POST['gender'] ?? ''), 
        trim($_POST['pronouns'] ?? ''), 
        trim($_POST['location'] ?? ''), 
        trim($_POST['occupation'] ?? ''), 
        trim($_POST['education'] ?? ''), 
        trim($_POST['love_language'] ?? ''), 
        (int)($_POST['height'] ?? 0),
        trim($_POST['biography'] ?? ''), 
        trim($_POST['pets'] ?? 'None'),
        trim($_POST['workout'] ?? 'Sometimes'),
        trim($_POST['social_media'] ?? 'Sometimes'),
        trim($_POST['favourite_song'] ?? ''), 
        $finalImages[0], $finalImages[1], $finalImages[2], $finalImages[3], $finalImages[4], $finalImages[5]
    ]);

    // 6. Insert into `preferences` table
    $ageMin = (int)($_POST['age_min'] ?? 18);
    $ageMax = (int)($_POST['age_max'] ?? 99);
    
    if ($ageMin < 18) $ageMin = 18;
    if ($ageMax > 99) $ageMax = 99;
    if ($ageMin > $ageMax) {
        $temp = $ageMin;
        $ageMin = $ageMax;
        $ageMax = $temp;
    }

    $stmtPref = $pdo->prepare('INSERT INTO preferences (user_id, looking_for, show_me, age_min, age_max) VALUES (?, ?, ?, ?, ?)');
    $stmtPref->execute([
        $userId, 
        trim($_POST['looking_for'] ?? 'A Relationship'), 
        trim($_POST['show_me'] ?? 'Everyone'),
        $ageMin, 
        $ageMax
    ]);

    // 7. Insert into `user_interests` table
    if (!empty($_POST['interests']) && is_array($_POST['interests'])) {
        $stmtInt = $pdo->prepare('INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)');
        foreach (array_slice($_POST['interests'], 0, 5) as $interestId) {
            $stmtInt->execute([$userId, (int)$interestId]);
        }
    }

    $pdo->commit();
    $_SESSION['success'] = "Account created! Welcome to S³, please log in.";
    header("Location: login.php");
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // IMPROVEMENT: Destroy any images that were uploaded if the database insertion failed
    if (isset($uploadedFiles) && is_array($uploadedFiles)) {
        foreach ($uploadedFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
    }
    
    $_SESSION['old_signup'] = $_POST;
    $_SESSION['error'] = $e->getMessage();
    header("Location: signup.php");
    exit;
}