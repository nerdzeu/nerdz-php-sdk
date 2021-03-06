<?php

namespace NERDZ\FileUpload;

use NERDZ\Exceptions\NERDZHttpException;
use NERDZ\Exceptions\NERDZSDKException;

class NERDZCrushWrapper
{
    //remote API url
    private static $NERDZAPIUrl = 'https://media.nerdz.eu/api/';
    //useragent
    private static $userAgent = 'NERDZCrushWrapper';

    /**
     * Change the server URL where the API resides.
     *
     * @param serverApiUrl The URL where the API waits for connections.
     */
    public static function changeApiURL($serverApiUrl)
    {
        self::$NERDZAPIUrl = $serverApiUrl;
    }

    /**
     * Get the server URL where the API resides.
     *
     * @return The URL where the API waits for connections.
     *        
     */
    public static function getApiURL()
    {
        return self::$NERDZAPIUrl;
    }

    /**
     * Change the user agent.
     *
     * @param useragent New useragent
     */
    public static function changeUserAgent($useragent)
    {
        self::$userAgent = $useragent;
    }

    /**
     * Get the userAgent
     *
     * @return  Current user agent.
     *
     */
    public static function getUserAgent()
    {
        return self::$userAgent;
    }

    /**
     *  Downlaod infos of a specified file.
     *
     *    @param hash The hash of the file to retrive info about.
     *
     *    @return NERDZFile
     *
     */
    public static function getFileInfo($hash)
    {

        $url   = self::$NERDZAPIUrl . $hash;
        $infos = self::request($url);

        $infos = json_decode($infos);

        if (isset($infos->error)) {
            self::triggerError($infos->error);
        }

        $file = new NERDZFile($infos);

        return $file;
    }

    /**
     *  Download files infos. It uses the API info?list method and return a dictionary hash->NERDZFile
     *
     *    @param  hashes An array of hashes to retrive info about.
     *
     *    @return a dictionary with key=hash and value=NERDZFile. NOTE: this is the main difference with getFiles()
     *
     */
    public static function getFileInfos($hashes)
    {
        $url = self::$NERDZAPIUrl . 'info?list=';
        $i   = 0;
        //quick and dirty way to not have a comma in the end of the url
        foreach ($hashes as $hash) {
            if ($i != 0) {
                $url .= ',';
            }
            $i++;
            $url .= $hash;
        }

        $response = self::request($url);
        $response = json_decode($response);

        if (isset($response->error)) {
            self::triggerError($response->error);
        }

        $toReturn = array();
        foreach ($response as $key => $value) {
            $toReturn[$key] = new NERDZFile($value);
        }

        return $toReturn;

    }

    /**
     *  Check if a given file exists on media.nerdz.eu
     *
     *    @param string hash
     *
     *    @return boolean
     *
     */
    public static function doesExist($hash)
    {
        $url  = self::$NERDZAPIUrl . $hash . '/exists';
        $info = self::request($url);
        $info = json_decode($info);

        return $info->exists;
    }

    /**
     *  Upload a file to media.nerdz.eu.
     *
     *    @param local file filename or NERDZFile
     *
     *    @return string hash
     *
     */
    public static function uploadFile($file)
    {

        $url = self::$NERDZAPIUrl . 'upload/file';

        if ($file instanceof NERDZFile) {
            $filename = $file->getFilePath();
        } else {
            $filename = $file;
        }

        if (!file_exists($filename)) {
            throw new NERDZSDKException("File cannot be found");
        }

        $post = array(
            'file' => curl_file_create($filename), //for some reason the autoload not allow to load CURLFile class. buh
        );

        $context = array(
            CURLOPT_POST       => 1,
            CURLOPT_POSTFIELDS => $post,
        );
        $response = self::request($url, $context);
        $response = json_decode($response);

        if (isset($response->error)) {
            self::triggerError($response->error);
        }

        return $response->hash;

    }

    /**
     *  Upload a file from an url.
     *
     *    @param string The url of a file to upload
     *
     *    @return string hash
     *
     */
    public static function uploadFileViaURL($uploadUrl)
    {
        $url = self::$NERDZAPIUrl . 'upload/url';

        $post = array(
            'url' => $uploadUrl,
        );

        $context = array(
            CURLOPT_POST       => 1,
            CURLOPT_POSTFIELDS => http_build_query($post),
        );

        $response = self::request($url, $context);
        $response = json_decode($response);

        if (isset($response->error)) {
            self::triggerError($response->error);
        }

        return $response->hash;
    }

    /**
     *  Delete $file uploaded on NerdzCrush
     *
     *    @param Nerdzfile or hash of a remote file that will be deleted
     *
     *    @return status of the operation
     *
     */
    public static function delete($file)
    {

        if ($file instanceof NERDZFile) {
            $hash = $file->getHash();
        }
        //need to check if file is on server.
        else {
            $hash = $file;
        }

        $url     = self::$NERDZAPIUrl . $hash;
        $context = array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        );

        $infos = self::request($url, $context);
        $infos = json_decode($infos);

        if (isset($infos->error)) {
            self::triggerError($infos->error);
        }

        return $infos->status;
    }

    /**
     *  Aliad for getFileInfo
     *
     *    @param string hash of the file to retrive
     *
     *    @return NERDZFile
     *
     */
    public static function getFile($hash)
    {
        //yes, this is an alias for GetFileInfo.
        $file = self::getFileInfo($hash);
        return $file;
    }

    /**
     *  Retrive a list of file Not using the API info?list= method. Return an array instead of a dictionary
     *
     *    @param string[] hashes
     *
     *    @return NERDZFile array
     *
     */
    public static function getFiles($hashes)
    {

        $returnFiles = array();
        $i           = 0;

        foreach ($hashes as $hash) {
            $returnFiles[$i++] = self::getFile($hash);
        }

        return $returnFiles;
    }

    /**
     *   Return contents
     *
     *    @return array[]
     */
    private static function request($url, $context = array())
    {

        $ch = curl_init($url);

        curl_setopt_array($ch, $context);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!$result = curl_exec($ch)) {
            throw new NERDZSDKException(curl_error($ch));
        }

        //if the result is null, check if there is an error code
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            self::triggerError(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        }

        curl_close($ch);

        return $result;
    }

    private static function triggerError($error)
    {
        switch ($error) {
            case '200':
                //no error, continue
                break;
            case '404':
                throw new NERDZHttpException("The requested file does not exist.", 404);
                break;
            case '400':
                throw new NERDZHttpException("The URL is invalid.", 400);
                break;
            case '401':
                throw new NERDZHttpException("The IP does not match the stored hash.", 401);
                break;
            case '408':
                throw new NERDZHttpException("You are no longer allowed to edit the title or description. (timeout)", 408);
                break;
            case '409':
                //no error, continue
                break;
            case '413':
                throw new NERDZHttpException("The file is larger than maximum allowed size.", 413);
                break;
            case '414':
                throw new NERDZHttpException("Either of the fields was over 2048 characters.", 414);
                break;
            case '415':
                throw new NERDZHttpException("The file extension is not acceptable.", 415);
                break;
            case '420':
                throw new NERDZHttpException("The rate limit was exceeded. Enhance your calm.", 420);
                break;
            default:
                throw new NERDZHttpException("Unrecognized error", $error);
                break;
        }

    }
}
