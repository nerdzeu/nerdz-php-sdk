<?php

require '../autoload.php';

class NERDZFileTest extends PHPUnit_Framework_TestCase
{
    private $JSONResponse = '{
  "blob_type": "image",
  "compression": 1.0,
  "description": null,
  "extras": [],
  "files": [
    {
      "file": "/Fsh6zkt6Znew.jpg",
      "type": "image/jpeg",
      "url": "https://media.nerdz.eu/Fsh6zkt6Znew.jpg"
    }
  ],
  "flags": {
    "nsfw": false
  },
  "hash": "Fsh6zkt6Znew",
  "metadata": {
    "dimensions": {
      "height": 1944,
      "width": 2592
    }
  },
  "original": "/Fsh6zkt6Znew.jpg",
  "title": null,
  "type": "image/jpeg"
}';

    private $existing_file   = "files/paolo.jpeg";
    private $inexistent_file = "foo";

    public function testNERDZFile_Construct_from_json()
    {
        $info = json_decode($this->JSONResponse);

        $obj = new NERDZ\FileUpload\NERDZFile($info);
    }

    public function testGetHash()
    {
        $info = json_decode($this->JSONResponse);
        $obj  = new NERDZ\FileUpload\NERDZFile($info);

        $this->assertEquals($info->hash, $obj->getHash());

    }

    public function testGetCompression()
    {
        $info = json_decode($this->JSONResponse);
        $obj  = new NERDZ\FileUpload\NERDZFile($info);
        $this->assertEquals($info->compression, $obj->getCompression());
    }

    public function testGetFilePath()
    {
        $info = json_decode($this->JSONResponse);
        $obj  = new NERDZ\FileUpload\NERDZFile($info);
        $this->assertEquals($info->files[0]->url, $obj->getFilePath());
    }

    public function testGetFileName()
    {
        $info = json_decode($this->JSONResponse);
        $obj  = new NERDZ\FileUpload\NERDZFile($info);
        $this->assertEquals(basename($info->files[0]->url), $obj->getFileName());
    }

    public function testNERDZFile_construct_from_string()
    {
        $obj = new NERDZ\FileUpload\NERDZFile($this->existing_file);
    }

    public function testGetPath()
    {
        $obj = new NERDZ\FileUpload\NERDZFile($this->existing_file);
        $this->assertEquals(realpath($this->existing_file), $obj->getFilePath());
    }

    public function test_exception()
    {
        $this->expectException(NERDZ\Exceptions\NERDZSDKException::class);
        $obj = new NERDZ\FileUpload\NERDZFile($this->inexistent_file);
    }
}
