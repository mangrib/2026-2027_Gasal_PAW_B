<html>
<head>
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <fieldset>
        <legend>Person Details</legend>
        <?php
            $errors = array();
            if (isset($_POST['surname']))
            {
                require 'validate.inc';
                validateName($errors, $_POST, 'surname');
                validateEmail($errors, $_POST, 'email');
                validatePassword($errors, $_POST, 'passwd');
                validateAddress($errors, $_POST, 'address');
                validateState($errors, $_POST, 'state');
                validateSex($errors, $_POST, 'sex');
                if ($errors)
                {
                    echo '<h1>Invalid, correct the following errors:</h1>';
                    foreach ($errors as $field => $error)
                        echo "$field $error</br>";
                    include 'form.inc';
                }
                else
                {
                    echo 'Form submitted successfully with no errors';
                }
            }
            else
                include 'form.inc';
        ?>
    </fieldset>
</body>
</html>