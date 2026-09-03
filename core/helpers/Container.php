<?php 
class Container{
    private static array $instances = array();
    
    public static function Set(string $key, object $instance): void {
        self::$instances[$key] = $instance;
    }

    public static function Get(string $key): object {
        if(!isset(self::$instances[$key])) {
            throw new InstanceException("No instance found for key: $key");
        }
        return self::$instances[$key];
    }

    public static function Exists(string $key) {
        return isset(self::$instances[$key]);
    }
}
