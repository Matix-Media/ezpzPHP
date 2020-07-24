<?php

class DB
{
    public static $DB = null;

    public static function setup($connection)
    {
        self::$DB = new \ClanCats\Hydrahon\Builder("mysql", function ($query, $queryString, $queryParameters) use ($connection) {
            $statement = $connection->prepare($queryString);
            $statement->execute($queryParameters);
            if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            }
        });

        return self::$DB;
    }
}
