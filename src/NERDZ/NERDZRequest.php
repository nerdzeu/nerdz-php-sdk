<?php

namespace NERDZ;

use NERDZ\Authentication\AccessToken;
use NERDZ\Url\NERDZUrlManipulator;
use NERDZ\FileUpload\NERDZFile;
use NERDZ\FileUpload\NERDZVideo;
use NERDZ\Http\RequestBodyMultipart;
use NERDZ\Http\RequestBodyUrlEncoded;
use NERDZ\Exceptions\NERDZSDKException;

/**
 * Class Request
 *
 * @package NERDZ
 */
class NERDZRequest
{
    /**
     * @var NERDZApp The NERDZ app entity.
     */
    protected $app;

    /**
     * @var string|null The access token to use for this request.
     */
    protected $accessToken;

    /**
     * @var string The HTTP method for this request.
     */
    protected $method;

    /**
     * @var string The Graph endpoint for this request.
     */
    protected $endpoint;

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
     * @param AccessToken|string|null $accessToken
     * @param string|null             $method
     * @param string|null             $endpoint
     * @param array|null              $params
     */
    public function __construct(NERDZApp $app = null, $accessToken = null, $method = null, $endpoint = null, array $params = [])
    {
        $this->setApp($app);
        $this->setAccessToken($accessToken);
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setParams($params);
    }

    /**
     * Set the access token for this request.
     *
     * @param AccessToken|string
     *
     * @return NERDZRequest
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        if ($accessToken instanceof AccessToken) {
            $this->accessToken = $accessToken->getValue();
        }

        return $this;
    }

    /**
     * Sets the access token with one harvested from a URL or POST params.
     *
     * @param string $accessToken The access token.
     *
     * @return NERDZRequest
     *
     * @throws NERDZSDKException
     */
    public function setAccessTokenFromParams($accessToken)
    {
        $existingAccessToken = $this->getAccessToken();
        if (!$existingAccessToken) {
            $this->setAccessToken($accessToken);
        } elseif ($accessToken !== $existingAccessToken) {
            throw new NERDZSDKException('Access token mismatch. The access token provided in the NERDZRequest and the one provided in the URL or POST params do not match.');
        }

        return $this;
    }

    /**
     * Return the access token for this request.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Return the access token for this request an an AccessToken entity.
     *
     * @return AccessToken|null
     */
    public function getAccessTokenEntity()
    {
        return $this->accessToken ? new AccessToken($this->accessToken) : null;
    }

    /**
     * Set the NERDZApp entity used for this request.
     *
     * @param NERDZApp|null $app
     */
    public function setApp(NERDZApp $app = null)
    {
        $this->app = $app;
    }

    /**
     * Return the NERDZApp entity used for this request.
     *
     * @return NERDZApp
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Generate an app secret proof to sign this request.
     *
     * @return string|null
     */
    public function getAppSecretProof()
    {
        if (!$accessTokenEntity = $this->getAccessTokenEntity()) {
            return null;
        }

        return $accessTokenEntity->getAppSecretProof($this->app->getSecret());
    }

    /**
     * Validate that an access token exists for this request.
     *
     * @throws NERDZSDKException
     */
    public function validateAccessToken()
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new NERDZSDKException('You must provide an access token.');
        }
    }

    /**
     * Set the HTTP method for this request.
     *
     * @param string
     *
     * @return NERDZRequest
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Return the HTTP method for this request.
     *
     * @return string
     */
    public function getMethod()
    {
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

    /**
     * Set the endpoint for this request.
     *
     * @param string
     *
     * @return NERDZRequest
     *
     * @throws NERDZSDKException
     */
    public function setEndpoint($endpoint)
    {
        // Harvest the access token from the endpoint to keep things in sync
        $params = NERDZUrlManipulator::getParamsAsArray($endpoint);
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        // Clean the token & app secret proof from the endpoint.
        $filterParams = ['access_token', 'appsecret_proof'];
        $this->endpoint = NERDZUrlManipulator::removeParamsFromUrl($endpoint, $filterParams);

        return $this;
    }

    /**
     * Return the HTTP method for this request.
     *
     * @return string
     */
    public function getEndpoint()
    {
        // For batch requests, this will be empty
        return $this->endpoint;
    }

    /**
     * Generate and return the headers for this request.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = static::getDefaultHeaders();

        if ($this->eTag) {
            $headers['If-None-Match'] = $this->eTag;
        }

        return array_merge($this->headers, $headers);
    }

    /**
     * Set the headers for this request.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }


    /**
     * Set the params for this request.
     *
     * @param array $params
     *
     * @return NERDZRequest
     *
     * @throws NERDZSDKException
     */
    public function setParams(array $params = [])
    {
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        // Don't let these buggers slip in.
        unset($params['access_token'], $params['appsecret_proof']);

        // @TODO Refactor code above with this
        //$params = $this->sanitizeAuthenticationParams($params);
        $params = $this->sanitizeFileParams($params);
        $this->dangerouslySetParams($params);

        return $this;
    }

    /**
     * Set the params for this request without filtering them first.
     *
     * @param array $params
     *
     * @return NERDZRequest
     */
    public function dangerouslySetParams(array $params = [])
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Iterate over the params and pull out the file uploads.
     *
     * @param array $params
     *
     * @return array
     */
    public function sanitizeFileParams(array $params)
    {
        foreach ($params as $key => $value) {
            if ($value instanceof NERDZFile) {
                $this->addFile($key, $value);
                unset($params[$key]);
            }
        }

        return $params;
    }

    /**
     * Add a file to be uploaded.
     *
     * @param string       $key
     * @param NERDZFile $file
     */
    public function addFile($key, NERDZFile $file)
    {
        $this->files[$key] = $file;
    }

    /**
     * Removes all the files from the upload queue.
     */
    public function resetFiles()
    {
        $this->files = [];
    }

    /**
     * Get the list of files to be uploaded.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Let's us know if there is a file upload with this request.
     *
     * @return boolean
     */
    public function containsFileUploads()
    {
        return !empty($this->files);
    }

    /**
     * Let's us know if there is a video upload with this request.
     *
     * @return boolean
     */
    public function containsVideoUploads()
    {
        foreach ($this->files as $file) {
            if ($file instanceof NERDZVideo) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the body of the request as multipart/form-data.
     *
     * @return RequestBodyMultipart
     */
    public function getMultipartBody()
    {
        $params = $this->getPostParams();

        return new RequestBodyMultipart($params, $this->files);
    }

    /**
     * Returns the body of the request as URL-encoded.
     *
     * @return RequestBodyUrlEncoded
     */
    public function getUrlEncodedBody()
    {
        $params = $this->getPostParams();

        return new RequestBodyUrlEncoded($params);
    }

    /**
     * Generate and return the params for this request.
     *
     * @return array
     */
    public function getParams()
    {
        $params = $this->params;

        $accessToken = $this->getAccessToken();
        if ($accessToken) {
            $params['access_token'] = $accessToken;
            $params['appsecret_proof'] = $this->getAppSecretProof();
        }

        return $params;
    }

    /**
     * Only return params on POST requests.
     *
     * @return array
     */
    public function getPostParams()
    {
        if ($this->getMethod() === 'POST') {
            return $this->getParams();
        }

        return [];
    }


    /**
     * Generate and return the URL for this request.
     *
     * @return string
     */
    public function getUrl()
    {
        $this->validateMethod();

        $endpoint = NERDZUrlManipulator::forceSlashPrefix($this->getEndpoint());

        $url = $graphVersion . $endpoint;

        if ($this->getMethod() !== 'POST') {
            $params = $this->getParams();
            $url = NERDZUrlManipulator::appendParamsToUrl($url, $params);
        }

        return $url;
    }

    /**
     * Return the default headers that every request should use.
     *
     * @return array
     */
    public static function getDefaultHeaders()
    {
        return [
            'User-Agent' => 'nz-php-' . NERDZ::VERSION,
            'Accept-Encoding' => '*',
        ];
    }
}
