<?php
final class GeneratePassword
{
    public static function Generate(int $length): string
    {
        try {
            $lowercase = 'abcdefghijklmnopqrstuvwxyz';
            $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $numbers = '0123456789';
            $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
            $allChars = $lowercase.$uppercase.$numbers.$symbols;
            $allCharsLength = strlen($allChars);

            $password = '';

            for ($i = 0; $i < $length; $i++) {
                $password .= $allChars[random_int(0, $allCharsLength - 1)];
            }
            $password = str_shuffle($password);

            return $password;
        } catch (\Throwable $th) {
            Logger::Log("A jelszó generálása sikertelen volt.", logLvl::Error);
            throw new PasswordGenerateException("A jelszó generálása sikertelen volt.");
        }
    }
}