<?php

namespace NERDZ\FileUpload;

use NERDZ\Exceptions\NERDZSDKException;

/**
 * Class NERDZFile
 *
 * @package NERDZ
 */
class NERDZFile
{
    /**
     * @var string The path to the file on the system.
     */
    protected $path;

    /**
     * @var int The maximum bytes to read. Defaults to -1 (read all the remaining buffer).
     */
    private $maxLength;

    /**
     * @var int Seek to the specified offset before reading. If this number is negative, no seeking will occur and reading will start from the current position.
     */
    private $offset;

    /**
     * @var resource The stream pointing to the file.
     */
    protected $stream;


    private $hash;
    private $status;
    private $compression;
    private $original;
    private $type;
    private $fileType;
    private $files;
    //private $orginalFile;
    private $file;

    public function getCompression(){
        return $this->compression;
    }

    public function getFiles(){
        return $this->files;
    }

    public function getHash(){
        return $this->hash;
    }

    public function getStatus(){
        return $this->getStatus;
    }



    /**
     * Creates a new NERDZFile entity.
     *
     * @param string $filePath
     * @param int $maxLength
     * @param int $offset
     *
     * @throws NERDZSDKException
     */
    public function __construct($info, $maxLength = -1, $offset = -1)
    {
        if($info instanceof string){
            $this->path = $info;
        }else{
            $this->hash=$info->hash;
            $this->compression=$info->compression;
            $this->files=$info->files;
            //!!!!! Must check if album or not
            $this->path=$info->files[0]->url;
            $this->fileType=$info->type;
            $this->file=$info->files[0]->file;
        }

        $this->maxLength = $maxLength;
        $this->offset = $offset;
        $this->open();

    }

    /**
     * Closes the stream when destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Opens a stream for the file.
     *
     * @throws NERDZSDKException
     */
    public function open()
    {
        if (!$this->isRemoteFile($this->path) && !is_readable($this->path)) {
            throw new NERDZSDKException('Failed to create NERDZFile entity. Unable to read resource: ' . $this->path . '.');
        }

        $this->stream = fopen($this->path, 'r');

        if (!$this->stream) {
            throw new NERDZSDKException('Failed to create NERDZFile entity. Unable to open resource: ' . $this->path . '.');
        }
    }

    /**
     * Stops the file stream.
     */
    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * Return the contents of the file.
     *
     * @return string
     */
    public function getContents()
    {
        return stream_get_contents($this->stream, $this->maxLength, $this->offset);
    }

    /**
     * Return the name of the file.
     *
     * @return string
     */
    public function getFileName()
    {
        return basename($this->path);
    }

    /**
     * Return the path of the file.
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->path;
    }

    /**
     * Return the size of the file.
     *
     * @return int
     */
    public function getSize()
    {
        return filesize($this->path);
    }

    /**
     * Return the mimetype of the file.
     *
     * @return string
     */
    public function getMimetype()
    {
        return Mimetypes::getInstance()->fromFilename($this->path) ?: 'text/plain';
    }

    /**
     * Returns true if the path to the file is remote.
     *
     * @param string $pathToFile
     *
     * @return boolean
     */
    protected function isRemoteFile($pathToFile)
    {
        return preg_match('/^(https?|ftp):\/\/.*/', $pathToFile) === 1;
    }
}
