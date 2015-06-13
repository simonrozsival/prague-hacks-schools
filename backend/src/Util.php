<?php
namespace Hacks;

class Util
{
    public static function generateRandomHash()
    {
        return sha1(openssl_random_pseudo_bytes(64));
    }
}
