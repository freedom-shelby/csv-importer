<?php

namespace Importer;

use PDO;

class DatabaseConnectionFactory
{
    public function makeConnection(string $host, string $name, string $user, string $password): PDO
    {
        return new PDO(
            "mysql:host=$host;dbname=$name",
            $user,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}