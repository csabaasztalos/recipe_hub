<?php
final class ValidatePassword {
    public static function Validate (string $password, string $confirmPass) {
        if (mb_strlen($password) < 12) {
            throw new PasswordException("A jelszó túl rövid. Minimum 12 karakter hosszú legyen.");
        }

        if (mb_strlen($password) > 72) {
            throw new PasswordException("A jelszó túl hosszú. Maximum 72 karakter hosszú lehet.");
        }

        if ($password !== $confirmPass) {
            throw new PasswordException("A két jelszó nem egyezik.");
        }
    }
}