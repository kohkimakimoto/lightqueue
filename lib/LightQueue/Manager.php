<?php
class LightQueue_Manager
{
    protected static $dsn;
    protected static $user;
    protected static $password;
    protected static $conn;
    
    public static function setup($dsn, $user, $password)
    {
        self::$dsn      = $dsn;
        self::$user     = $user;
        self::$password = $password;
    }
    
    public static function getConnection()
    {
        if (!self::$conn) {
            self::$conn = new PDO(self::$dsn, self::$user, self::$password);
        }
        return self::$conn;
    }
    
}