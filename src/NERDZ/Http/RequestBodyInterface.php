<?php


namespace NERDZ\Http;

/**
 * Interface
 *
 * @package NERDZ
 */
interface RequestBodyInterface
{
    /**
     * Get the body of the request to send to Graph.
     *
     * @return string
     */
    public function getBody();
}
