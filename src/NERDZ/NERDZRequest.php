<?php
	
	namespace NERDZ;

	use NERDZ\Exceptions\NERDZSDKException;

	class NERDZRequest {

	/**
     * @var NERDZApp 
     */
    protected $app;

      /**
     * @var string The HTTP method for this request.
     */
    protected $method;

     /**
     * @var array The headers to send with this request.
     */
    protected $headers = [];

    /**
     * @var array The parameters to send with this request.
     */
    protected $params = [];

    /**
     * @var array The files to send with this request.
     */
    protected $files = [];

     /**
     * Creates a new Request entity.
     *
     * @param NERDZApp|null        $app
     * @param string|null          $method
     * @param array|null           $params
     */
	 public function __construct(NERDZApp $app = null, $method = null, array $params = []){
        $this->setApp($app);
        $this->setMethod($method);
        $this->setParams($params);
    }

    /**
     * Set App
     *
     * @param NERDZApp $app
     */
    public function setApp(NERDZApp $app){
    	$this->app=$app;
    }

    /**
     * Get App
     *
     * @return  NERDZApp $app
     */
    public function getApp(){
    	return $this->app;
    }

    /**
     * Set Params
     *
     * @param array $params
     */
    public function setParams(array $params){
    	$this->params=$params;
    }

     /**
     * Get Params
     *
     * @return  array $params
     */
     public function getParams(){
    	return $this->params;
    }

    /**
     * Set Method
     *
     * @param string $method
     */
    public function setMethod($method){
    	$this->method=$method;
    }

    /**
     * Get Params
     *
     * @return  string $method
     */
    public function getMethod(){
    	return $this->method;
    }


     /**
     * Validate that the HTTP method is set.
     *
     * @throws NERDZSDKException
     */
    public function validateMethod()
    {
        if (!$this->method) {
            throw new NERDZSDKException('HTTP method not specified.');
        }
        if (!in_array($this->method, ['GET', 'POST', 'DELETE'])) {
            throw new NERDZSDKException('Invalid HTTP method specified.');
        }
    }

}