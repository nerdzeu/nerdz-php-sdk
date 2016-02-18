<?php

namespace NERDZ;


class NERDZApp implements \Serializable{

    /**
     * @var string The app ID.
     */
    protected $id;

    /**
     * @var string The app secret.
     */
    protected $secret;

    /**
     * @param string $id
     * @param string $secret
     */

    public function __construct($id, $secret){
        $this->id = $id;
        $this->secret = $secret;
    }

    /**
     * Returns the app ID.
     *
     * @return string
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Returns the app secret.
     *
     * @return string
     */
    public function getSecret(){
        return $this->secret;
    }

    /**
     * Serializes the NERDZApp entity as a string.
     *
     * @return string
     */
    public function serialize(){
        return serialize([$this->id, $this->secret]);
    }

    /**
     * Unserializes a string as a NERDZApp entity.
     *
     * @param string $serialized
     */
    public function unserialize($serialized){
        list($id, $secret) = unserialize($serialized);
        $this->__construct($id, $secret);
    }
}