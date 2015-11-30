<?php

namespace NERDZ;

/*
 * Class App
 * @package NERDZ
 */
class App {

    /*
     * @var int the App id
     */
    private $id;

    /*
     * @var string the App secret
     */
    private $secret;

    /*
     * @param string $id
     * @param string $secret
     */
    public function __construct($id, $secret) {
        $this->id = (int)$id;
        $this->secret = $secret;
    }

    /*
     * Returns the app secret
     */
    public function getSecret() {
        return $this->secret;
    }

    /*
     * Returns the app id
     */
    public function getId() {
        return $this->id;
    }

}
