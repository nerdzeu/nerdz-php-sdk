<?php

namespace NERDZ\Authentication;
use NERDZ\App;

/*
 * Class OAuth2Client
 *
 * @package NERDZ\Authentication
 */
class OAuth2Client {

    /*
     * @var \League\OAuth2\Client\Provider\GenericProvider generic OAuth2 provider
     */

    private $provider;

    /*
     * @const string base url of OAuth2 end point
     */
    const BASE_URL = 'https://api.nerdz.eu';

    public function __construct(App $app, array $scope, $grant) {
        $this->provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $app->getId(),
            'clientSecret'            => $app->getSecret(),
            'urlAuthorize'            => static::BASE_URL.'/authorize',
            'urlAccessToken'          => static::BASE_URL.'/token',
            'urlResourceOwnerDetails' => static::BASE_URL.'/resource',
            'scope'                   => $scope
        ]);
    }
}
