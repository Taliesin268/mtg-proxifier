<?php


namespace Proxifier;


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
        if (empty($searchQuery)) {
            return null;
        }

        //todo finish off the search
        return new Card();
    }

    public static function lookupCardByName(string $name, string $set = ''): Card|null
    {

    }

    /**
     * Makes a request to the Scryfall DB.
     *
     * @param string $endpoint Which endpoint to go to (from Scry::Endpoints).
     * @param array $parameters The parameters to get passed in as part of this request.
     * @param string $method Determines which HTTP method to use. Default 'GET'.
     * @return string The result we get from the Scryfall API.
     *
     * @todo Implement POST version
     */
    private static function performRequest(string $endpoint, array $parameters, string $method = 'GET'): string
    {
        // Set the url
        $url = self::BASE_URL . $endpoint;

        // If the method is GET, put the parameters into the URL.
        if($method == 'GET') {
            $url .= '?' . http_build_query($parameters);
        }

        // Init Curl
        $curl = curl_init($url);
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
        ]);

        // Execute the curl command
        $result = curl_exec($curl);
        // Close this curl instance
        curl_close($curl);

        // Return the result from the curl request
        return $result;
    }
}