<?php
namespace Hacks;

class Util
{
    public static function generateRandomToken()
    {
        return sha1(openssl_random_pseudo_bytes(64));
    }
}
