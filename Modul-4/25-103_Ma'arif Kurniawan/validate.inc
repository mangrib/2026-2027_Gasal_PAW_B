<?php
    function validateName(&$errors, $field_list, $field_name)
    {
        $pattern = "/^[a-zA-Z'-]+$/";
        if (!isset($field_list[$field_name]) || empty($field_list[$field_name]))
            $errors[$field_name] = 'required';
        else if (!preg_match($pattern, $field_list[$field_name]))
            $errors[$field_name] = 'invalid';
    }

    function validateEmail(&$errors, $field_list, $field_name)
    {
        $pattern = "/^[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}$/";
        if (!isset($field_list[$field_name]) || empty($field_list[$field_name]))
            $errors[$field_name] = 'required';
        else if (!preg_match($pattern, $field_list[$field_name]))
            $errors[$field_name] = 'invalid';
    }

    function validatePassword(&$errors, $field_list, $field_name)
    {
        if (!isset($field_list[$field_name]) || empty($field_list[$field_name]))
            $errors[$field_name] = 'required';
    }

    function validateAddress(&$errors, $field_list, $field_name)
    {
        if (!isset($field_list[$field_name]) || empty($field_list[$field_name]))
            $errors[$field_name] = 'required';
    }

    function validateState(&$errors, $field_list, $field_name)
    {
        if (!isset($field_list[$field_name]) || $field_list[$field_name] == '-1')
            $errors[$field_name] = 'required';
    }

    function validateSex(&$errors, $field_list, $field_name)
    {
        if (!isset($field_list[$field_name]))
            $errors[$field_name] = 'required';
    }
?>