<?php

class NERDZCrushTest extends PHPUnit_Framework_TestCase
{

    private $existing_hash1        = "Fsh6zkt6Znew";
    private $existing_hash2        = "C4PFHof8-Bnh";
    private $local_file            = array("tests/files/paolo.jpeg", "iwvA-OjFOssN"); //name of file and its hash
    private $inexistent_local_file = "i_do_not_exist";
    private $existing_url          = "https://www.google.it/images/nav_logo242.png";
    private $non_existing_hash     = "aaaaaaaaaaaa";
    private $default_API_url       = "https://media.nerdz.eu/api/";
    private $default_UserAgent     = "NERDZCrushWrapper";

    public function testApiUrl()
    {

        $old_api = $this->default_API_url;
        $this->assertEquals($old_api, NERDZ\FileUpload\NERDZCrushWrapper::getAPIUrl());

        NERDZ\FileUpload\NERDZCrushWrapper::changeAPIUrl('foo');
        $this->assertEquals('foo', NERDZ\FileUpload\NERDZCrushWrapper::getAPIUrl());

        NERDZ\FileUpload\NERDZCrushWrapper::changeAPIUrl($old_api);
        $this->assertEquals($old_api, NERDZ\FileUpload\NERDZCrushWrapper::getAPIUrl());
    }

    public function testUserAgent()
    {

        $old_user_agent = $this->default_UserAgent;
        $this->assertEquals($old_user_agent, NERDZ\FileUpload\NERDZCrushWrapper::getUserAgent());

        NERDZ\FileUpload\NERDZCrushWrapper::changeUserAgent('foo');
        $this->assertEquals('foo', NERDZ\FileUpload\NERDZCrushWrapper::getUserAgent());

        NERDZ\FileUpload\NERDZCrushWrapper::changeUserAgent($old_user_agent);
        $this->assertEquals($old_user_agent, NERDZ\FileUpload\NERDZCrushWrapper::getUserAgent());
    }

    public function testDoesExist()
    {
        $hash   = $this->existing_hash1;
        $return = NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);

        $this->assertTrue($return);
    }

    public function testDelete()
    {
        $file   = $this->local_file[0];
        $Uphash = NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);

        $result = NERDZ\FileUpload\NERDZCrushWrapper::delete($Uphash);
        $this->assertEquals("success", $result);

    }

    public function testFailDoesExist()
    {
        $this->expectException(NERDZ\Exceptions\NERDZHttpException::class);
        $this->expectExceptionCode(404);
        $hash   = $this->non_existing_hash; //this MUST be an non-existing file
        $return = NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);

        $this->assertFalse($return);
    }

    public function testUploadFile()
    {
        $file   = $this->local_file[0];
        $hash   = $this->local_file[1];
        $Uphash = NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);
        $this->assertEquals($hash, $Uphash);

        $Uphash = NERDZ\FileUpload\NERDZCrushWrapper::delete($Uphash); //clean

        $file   = new NERDZ\FileUpload\NERDZFile($file);
        $Uphash = NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);
        $this->assertEquals($hash, $Uphash);

        $Uphash = NERDZ\FileUpload\NERDZCrushWrapper::delete($Uphash); // clean image
    }

    public function testUploadInexistentFile()
    {
        $this->expectException(NERDZ\Exceptions\NERDZSDKException::class);

        $file   = $this->inexistent_local_file;
        $Uphash = NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);

    }

    public function testUploadFileViaURL()
    {
        $url = $this->existing_url; // MUST be a valid url

        $Uphash = NERDZ\FileUpload\NERDZCrushWrapper::uploadFileViaURL($url);

        $this->assertTrue($Uphash != null);
    }

    public function testGetFileInfo()
    {
        $hash = $this->existing_hash1; //this MUST be an existing file

        $aspected_results = array(
            'getHash'     => $this->existing_hash1,
            'getFilePath' => 'https://media.nerdz.eu/' . $this->existing_hash1 . '.jpg',
            'getFile'     => '/' . $this->existing_hash1 . '.jpg',
            'getFileName' => $this->existing_hash1 . '.jpg',
        );

        $file = NERDZ\FileUpload\NERDZCrushWrapper::getFileInfo($hash);
        $this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $file);
        foreach ($aspected_results as $key => $value) {
            $this->assertEquals($value, $file->$key());
        }
    }

    public function testGetFile()
    {
        $hash = $this->existing_hash1; //this MUST be an existing file

        $aspected_results = array(
            'getHash'     => $this->existing_hash1,
            'getFilePath' => 'https://media.nerdz.eu/' . $this->existing_hash1 . '.jpg',
            'getFile'     => '/' . $this->existing_hash1 . '.jpg',
            'getFileName' => $this->existing_hash1 . '.jpg',
        );

        $file = NERDZ\FileUpload\NERDZCrushWrapper::getFile($hash);
        $this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $file);
        foreach ($aspected_results as $key => $value) {
            $this->assertEquals($value, $file->$key());
        }
    }

    public function testGetInexistentFile()
    {
        $this->expectException(NERDZ\Exceptions\NERDZHttpException::class);
        $this->expectExceptionCode(404);

        $hash = $this->non_existing_hash;

        $file = NERDZ\FileUpload\NERDZCrushWrapper::getFile($hash);

    }

    public function testGetFiles()
    {
        $hashes = array($this->existing_hash1, $this->existing_hash2); //this MUST be an existing file
        //$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);
        $aspected_results1 = array(
            'getHash'     => $this->existing_hash1,
            'getFilePath' => 'https://media.nerdz.eu/' . $this->existing_hash1 . '.jpg',
            'getFile'     => '/' . $this->existing_hash1 . '.jpg',
            'getFileName' => $this->existing_hash1 . '.jpg',
        );
        $aspected_results2 = array(
            'getHash'     => $this->existing_hash2,
            'getFilePath' => 'https://media.nerdz.eu/' . $this->existing_hash2 . '.jpg',
            'getFile'     => '/' . $this->existing_hash2 . '.jpg',
            'getFileName' => $this->existing_hash2 . '.jpg',
        );

        $files = NERDZ\FileUpload\NERDZCrushWrapper::getFiles($hashes);

        $this->assertCount(2, $files);
        $this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files[0]);
        $this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files[1]);

        foreach ($aspected_results1 as $key => $value) {
            $this->assertEquals($value, $files[0]->$key());
        }
        foreach ($aspected_results2 as $key => $value) {
            $this->assertEquals($value, $files[1]->$key());
        }
    }
    public function testGetFileInfos()
    {
        $hashes = array($this->existing_hash1, $this->existing_hash2); //this MUST be an existing file
        //$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);
        $aspected_results1 = array(
            'getHash'     => $this->existing_hash1,
            'getFilePath' => 'https://media.nerdz.eu/' . $this->existing_hash1 . '.jpg',
            'getFile'     => '/' . $this->existing_hash1 . '.jpg',
            'getFileName' => $this->existing_hash1 . '.jpg',
        );
        $aspected_results2 = array(
            'getHash'     => $this->existing_hash2,
            'getFilePath' => 'https://media.nerdz.eu/' . $this->existing_hash2 . '.jpg',
            'getFile'     => '/' . $this->existing_hash2 . '.jpg',
            'getFileName' => $this->existing_hash2 . '.jpg',
        );

        $files = NERDZ\FileUpload\NERDZCrushWrapper::getFileInfos($hashes);

        $this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files[$hashes[0]]);
        $this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files[$hashes[1]]);

        foreach ($aspected_results1 as $key => $value) {
            $this->assertEquals($value, $files[$hashes[0]]->$key());
        }
        foreach ($aspected_results2 as $key => $value) {
            $this->assertEquals($value, $files[$hashes[1]]->$key());
        }
    }

}
