<?php
final class MaskEmail {
    public static function Mask($email) {
        [$name, $domain] = explode("@", $email);
        return substr($name, 0, 1) . '*****@' . $domain;
    }
}
