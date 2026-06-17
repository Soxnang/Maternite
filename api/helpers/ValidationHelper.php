<?php
class ValidationHelper {
    public static function required(array $data, array $fields): array {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) $errors[] = "$field est requis";
        }
        return $errors;
    }
    public static function email(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
