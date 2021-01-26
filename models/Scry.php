<?php


namespace Proxifier;


class Scry
{
    public const SEARCH_TERMS = ['name' => ''];

    public static function lookupCard(array $query): Card | null
    {
        $searchQuery = '';
        foreach ($query as $field => $value) {
            if (!in_array($field, array_keys(self::SEARCH_TERMS))) {
                continue;
            }
            $searchQuery .= self::SEARCH_TERMS['value'] . $value;
        }
        if (empty($searchQuery)) { return null; }

        //todo
        return new Card();
    }
}