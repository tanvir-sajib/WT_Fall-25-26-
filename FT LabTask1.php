<?php
$nameErr = $emailErr = $dobErr = $genderErr = $degreeErr = "";
$name = $email = $gender = "";
$day = $month = $year = "";
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // NAME VALIDATION
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = trim($_POST["name"]);
 
        if (!preg_match("/^[A-Za-z][A-Za-z.\- ]+$/", $name)) {
            $nameErr = "Invalid name format";
        } elseif (str_word_count($name) < 2) {
            $nameErr = "Name must contain at least two words";
        }
    }
 
    // EMAIL VALIDATION
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = $_POST["email"];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }
 
    // DATE OF BIRTH VALIDATION
    $day = $_POST["day"];
    $month = $_POST["month"];
    $year = $_POST["year"];
 
    if (empty($day) || empty($month) || empty($year)) {
        $dobErr = "Date of Birth is required";
    } elseif ($day < 1 || $day > 31 || $month < 1 || $month > 12 || $year < 1953 || $year > 1998) {
        $dobErr = "Invalid Date of Birth";
    }
 
    // GENDER VALIDATION
    if (empty($_POST["gender"])) {
        $genderErr = "Please select a gender";
    } else {
        $gender = $_POST["gender"];
    }
}
?>
 
<!DOCTYPE html>
<html>
<head>
    <title>PHP Form Validation</title>
    <style>
        .error { color: red; }
        fieldset { width: 300px; margin-bottom: 20px; }
    </style>
</head>
<body>
 
<!-- NAME FORM -->
<form method="post"><br><br>
<fieldset><br><br>
    <legend><b>NAME</b></legend>
    <input type="text" name="name">
    <span class="error">* <?php echo $nameErr; ?></span><br><br>
    <input type="submit" value="Submit">
</fieldset><br><br>
</form>
 
<!-- EMAIL FORM -->
<form method="post">
<fieldset>
    <legend><b>EMAIL</b></legend>
    <input type="text" name="email">
    <span class="error">* <?php echo $emailErr; ?></span><br><br>
    <input type="submit" value="Submit">
</fieldset><br><br>
</form>
 
<!-- DATE OF BIRTH FORM -->
<form method="post">
<fieldset>
    <legend><b>DATE OF BIRTH</b></legend>
    DD <input type="number" name="day" style="width:50px;">
    MM <input type="number" name="month" style="width:50px;">
    YYYY <input type="number" name="year" style="width:50px;">
    <br>
    <span class="error">* <?php echo $dobErr; ?></span><br><br>
    <input type="submit" value="Submit">
</fieldset><br><br>
</form>
 
<!-- GENDER FORM -->
<form method="post">
<fieldset>
    <legend><b>GENDER</b></legend>
    <input type="radio" name="gender" value="Male"> Male
    <input type="radio" name="gender" value="Female"> Female
    <input type="radio" name="gender" value="Other"> Other
    <br>
    <span class="error">* <?php echo $genderErr; ?></span><br><br>
    <input type="submit" value="Submit">
</fieldset><br><br>
</form>
<form method="post">
    <fieldset>
        <legend><b>DEGREE</b></legend>
        <input type="checkbox" name="degree[]" value="SSC"> SSC
        <input type="checkbox" name="degree[]" value="HSC"> HSC
        <input type="checkbox" name="degree[]" value="BSc"> BSc
        <input type="checkbox" name="degree[]" value="MSc"> MSc
        <br>
        <span class="error">* <?php echo $degreeErr; ?></span><br><br>
        <input type="submit" value="Submit">
    </fieldset>
</form>
<form method="post">
    <fieldset>
        <legend><b>BLOOD GROUP</b></legend>
        <select name="bloodgroup">
            <option value=""></option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
    </fieldset>
</form>
</body>
</html>