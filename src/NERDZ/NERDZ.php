<?php

namespace NERDZ;

use NERDZ\Authentication\OAuth2Client;
use NERDZ\Exceptions\NERDZSDKException;

/*
 * Class NERDZ
 * @package NERDZ
 */
class NERDZ {

    /*
     * @const string Version number of the NERDZ PHP SDK
     */
    const VERSION = '1.0.0';

    /*
     * @const string The name of the environment variable that contains the app ID
     */
    const APP_ID_ENV_NAME = 'NERDZ_APP_ID';

    /*
     * @const string The name of the environment variable that contains the app secret
     */
    const APP_SECRET_ENV_NAME = 'NERDZ_APP_SECRET';

     /**
     * @var NERDZApp The NERDZ application
     */
    protected $app;

    /**
     * @var OAuth2Client The NERDZ OAuth2 client
     */
    protected $oauth2client;

    public function __construct(array $config = []) {
        $config = array_merge([
            'app_id' => getenv(static::APP_ID_ENV_NAME),
            'app_secret' => getenv(static::APP_SECRET_ENV_NAME)
        ], $config);


        if (!$config['app_id']) {
            throw new NERDZSDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . static::APP_ID_ENV_NAME . '"');
        }
        if (!$config['app_secret']) {
            throw new NERDZSDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }

		$this->app = new App($config['app_id'], $config['app_secret']);
	    $this->client = new OAuth2Client($this->app);
    }

    /*
     * Returns the OAuth2Client
     *
     * @return OAuth2Client
     */
    public function getOAuth2Client() {
        return $this->oauth2client;
    }

    /*
     * Returns the NERDZ App
     *
     * @return App
     */
    public function getApp() {
        return $this->app;
    }
}
