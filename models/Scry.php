<?php

namespace Proxifier;

use Exception;

class Scry
{
    /** @var string The base URL for all requests. */
    public const BASE_URL = 'https://api.scryfall.com';
    /** @var string[] All available endpoints. In the format 'Name' => '/path'. */
    public const ENDPOINTS = ['SearchByName' => '/cards/named', 'Search' => '/cards/search'];
    /** @var string[] Available search terms and their prefix. In the format 'name' => 'prefix'. */
    public const SEARCH_TERMS = ['name' => ''];

    /**
     * Looks up a card based on a collection of search queries.
     *
     * @param array $query An array of search queries to use. Only accepts items in the Scry::SEARCH_TERMS array.
     * @return Card|null Returns a Card if one is found, otherwise null.
     *
     * @todo Finish off the search
     */
    public static function lookupCard(array $query): Card|null
    {
        // Init the final search queries array
        $searchQuery = [];

        // For each field in the query array
        foreach ($query as $field => $value) {
            // If this field is not in the search terms array, move onto the next item.
            if (!in_array($field, array_keys(self::SEARCH_TERMS))) {
                continue;
            }
            // Add this field to the final search query, and get the field prefix from SEARCH_TERMS
            $searchQuery[$field] = self::SEARCH_TERMS[$field] . $value;
        }

        // If no query was built, then return null.
        if (empty($searchQuery)) return null;

        //todo finish off the search
        return null;
    }

    /**
     * Finds a card by name and optional set.
     *
     * @param string $name The name of the card you wish to find.
     * @param string $set Optional. Which set the card is from.
     * @return Card|null Null if no card was found, otherwise it returns the card with the provided name.
     */
    public static function lookupCardByName(string $name, string $set = ''): Card|null
    {
        // If the name does not exist, return null, otherwise add the name to the parameters array.
        if (empty($name)) return null;
        $parameters['fuzzy'] = $name;

        // If there is a set, add that too.
        if (!empty($set)) $parameters['set'] = $set;

        // Get the info from the Scryfall DB.
        $cardInfo = self::performRequest(self::ENDPOINTS['SearchByName'], $parameters);

        // If the endpoint returned nothing, then we should return null
        if (empty($cardInfo)) return null;

        // Otherwise return the card
        try {
            return new Card($cardInfo);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Makes a request to the Scryfall DB.
     *
     * @param string $endpoint Which endpoint to go to (from Scry::Endpoints).
     * @param array $parameters The parameters to get passed in as part of this request.
     * @param string $method Determines which HTTP method to use. Default 'GET'.
     * @return string The result we get from the Scryfall API.
     */
    private static function performRequest(string $endpoint, array $parameters, string $method = 'GET'): string
    {
        // Set the url
        $url = self::BASE_URL . $endpoint;

        // Init Curl
        $curl = curl_init();

        if ($method == 'GET') {
            // If the method is GET, put the parameters into the URL.
            $url .= '?' . http_build_query($parameters);
        } else if ($method == 'POST') {
            // If the method is POST, put the parameters into the post body.
            curl_setopt_array($curl, [
                CURLOPT_POST => count($parameters),
                CURLOPT_POSTFIELDS => http_build_query($parameters)
            ]);
        }
        // Set out options
        curl_setopt_array($curl, [
            // Accept and content type is JSON
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            // Return the results of the request
            CURLOPT_RETURNTRANSFER => true,
            // Timeout after 30 seconds of waiting
            CURLOPT_TIMEOUT => 30,
            // Explicitly state the method
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL => $url
        ]);

        // Execute the curl command
        $result = curl_exec($curl);
        // Close this curl instance
        curl_close($curl);

        // Return the result from the curl request
        return $result;
    }
}