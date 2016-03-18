<?php
/*+++++++++++README++++++++++++++*/
/* Questa classe la tengo per sport, ma fa riferimento ai grafi che su nerdz non dovremmo avere(?)
penso comunque che adrebbe modificata. Attendo ordini dal sovrano*/

namespace NERDZ;

use NERDZ\HttpClients\NERDZHttpClientInterface;
use NERDZ\HttpClients\NERDZCurlHttpClient;
use NERDZ\HttpClients\NERDZStreamHttpClient;
use NERDZ\Exceptions\NERDZSDKException;

/**
 * Class NERDZClient
 *
 * @package NERDZ
 */
class NERDZClient
{
    /**
     * @const string Production Graph API URL.
     */
    const BASE_GRAPH_URL = '';

    /**
     * @const string Graph API URL for video uploads.
     */
    const BASE_GRAPH_VIDEO_URL = '';

    /**
     * @const string Beta Graph API URL.
     */
    const BASE_GRAPH_URL_BETA = '';

    /**
     * @const string Beta Graph API URL for video uploads.
     */
    const BASE_GRAPH_VIDEO_URL_BETA = '';

    /**
     * @const int The timeout in seconds for a normal request.
     */
    const DEFAULT_REQUEST_TIMEOUT = 60;

    /**
     * @const int The timeout in seconds for a request that contains file uploads.
     */
    const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 3600;

    /**
     * @const int The timeout in seconds for a request that contains video uploads.
     */
    const DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT = 7200;

    /**
     * @var bool Toggle to use Graph beta url.
     */
    protected $enableBetaMode = false;

    /**
     * @var NERDZHttpClientInterface HTTP client handler.
     */
    protected $httpClientHandler;

    /**
     * @var int The number of calls that have been made to Graph.
     */
    public static $requestCount = 0;

    /**
     * Instantiates a new NERDZClient object.
     *
     * @param NERDZHttpClientInterface|null $httpClientHandler
     * @param boolean                          $enableBeta
     */
    public function __construct(NERDZHttpClientInterface $httpClientHandler = null, $enableBeta = false)
    {
        $this->httpClientHandler = $httpClientHandler ?: $this->detectHttpClientHandler();
        $this->enableBetaMode = $enableBeta;
    }

    /**
     * Sets the HTTP client handler.
     *
     * @param NERDZHttpClientInterface $httpClientHandler
     */
    public function setHttpClientHandler(NERDZHttpClientInterface $httpClientHandler)
    {
        $this->httpClientHandler = $httpClientHandler;
    }

    /**
     * Returns the HTTP client handler.
     *
     * @return NERDZHttpClientInterface
     */
    public function getHttpClientHandler()
    {
        return $this->httpClientHandler;
    }

    /**
     * Detects which HTTP client handler to use.
     *
     * @return NERDZHttpClientInterface
     */
    public function detectHttpClientHandler()
    {
        return extension_loaded('curl') ? new NERDZCurlHttpClient() : new NERDZStreamHttpClient();
    }

    /**
     * Toggle beta mode.
     *
     * @param boolean $betaMode
     */
    public function enableBetaMode($betaMode = true)
    {
        $this->enableBetaMode = $betaMode;
    }

    /**
     * Returns the base Graph URL.
     *
     * @param boolean $postToVideoUrl Post to the video API if videos are being uploaded.
     *
     * @return string
     */
    public function getBaseGraphUrl($postToVideoUrl = false)
    {
        if ($postToVideoUrl) {
            return $this->enableBetaMode ? static::BASE_GRAPH_VIDEO_URL_BETA : static::BASE_GRAPH_VIDEO_URL;
        }

        return $this->enableBetaMode ? static::BASE_GRAPH_URL_BETA : static::BASE_GRAPH_URL;
    }

    /**
     * Prepares the request for sending to the client handler.
     *
     * @param NERDZRequest $request
     *
     * @return array
     */
    public function prepareRequestMessage(NERDZRequest $request)
    {
        $postToVideoUrl = $request->containsVideoUploads();
        $url = $this->getBaseGraphUrl($postToVideoUrl) . $request->getUrl();

        // If we're sending files they should be sent as multipart/form-data
        if ($request->containsFileUploads()) {
            $requestBody = $request->getMultipartBody();
            $request->setHeaders([
                'Content-Type' => 'multipart/form-data; boundary=' . $requestBody->getBoundary(),
            ]);
        } else {
            $requestBody = $request->getUrlEncodedBody();
            $request->setHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);
        }

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $requestBody->getBody(),
        ];
    }

    /**
     * Makes the request to Graph and returns the result.
     *
     * @param NERDZRequest $request
     *
     * @return NERDZResponse
     *
     * @throws NERDZSDKException
     */
    public function sendRequest(NERDZRequest $request)
    {
        if (get_class($request) === 'NERDZ\NERDZRequest') {
            $request->validateAccessToken();
        }

        list($url, $method, $headers, $body) = $this->prepareRequestMessage($request);

        // Since file uploads can take a while, we need to give more time for uploads
        $timeOut = static::DEFAULT_REQUEST_TIMEOUT;
        if ($request->containsFileUploads()) {
            $timeOut = static::DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT;
        } elseif ($request->containsVideoUploads()) {
            $timeOut = static::DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT;
        }

        // Should throw `NERDZSDKException` exception on HTTP client error.
        // Don't catch to allow it to bubble up.
        $rawResponse = $this->httpClientHandler->send($url, $method, $body, $headers, $timeOut);

        static::$requestCount++;

        $returnResponse = new NERDZResponse(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getHttpResponseCode(),
            $rawResponse->getHeaders()
        );

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }

   /*
    public function sendBatchRequest(NERDZBatchRequest $request)
    {
        $request->prepareRequestsForBatch();
        $NERDZResponse = $this->sendRequest($request);

        return new NERDZBatchResponse($request, $NERDZResponse);
    }*/
}
