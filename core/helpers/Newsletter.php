<?php

final class Newsletter
{
    public static function Subscribe(array $post): void
    {
        if (trim($post['newsLetterEmail']) != "" &&
            filter_input(INPUT_POST, 'newsLetterEmail', FILTER_VALIDATE_EMAIL &&
                isset($post['contribute']) && $post['contribute'])) {
            $email = $post['newsLetterEmail'];
            $userModel = new UsersModel();
            $userModel->SubToNewsletter($email);
        }
    }
}