<?php

require '../autoload.php';

//used files: https://media.nerdz.eu/C4PFHof8-Bnh.jpg

class NERDZCrushTest extends PHPUnit_Framework_TestCase{

	public function testApiUrl()
    {

    	$old_api='https://media.nerdz.eu/api/';
        $this->assertEquals($old_api, NERDZ\FileUpload\NERDZCrushWrapper::getAPIUrl());

        NERDZ\FileUpload\NERDZCrushWrapper::changeAPIUrl('foo');
        $this->assertEquals('foo', NERDZ\FileUpload\NERDZCrushWrapper::getAPIUrl());

        NERDZ\FileUpload\NERDZCrushWrapper::changeAPIUrl($old_api);
        $this->assertEquals($old_api, NERDZ\FileUpload\NERDZCrushWrapper::getAPIUrl());
    }

    public function testUserAgent()
    {

    	$old_user_agent='NERDZCrushWrapper';
        $this->assertEquals($old_user_agent, NERDZ\FileUpload\NERDZCrushWrapper::getUserAgent());

        NERDZ\FileUpload\NERDZCrushWrapper::changeUserAgent('foo');
        $this->assertEquals('foo', NERDZ\FileUpload\NERDZCrushWrapper::getUserAgent());

        NERDZ\FileUpload\NERDZCrushWrapper::changeUserAgent($old_user_agent);
        $this->assertEquals($old_user_agent, NERDZ\FileUpload\NERDZCrushWrapper::getUserAgent());
    }

    public function testDoesExist(){
    	$hash="Fsh6zkt6Znew"; //this MUST be an existing file
    	$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);

    	$this->assertTrue($return);
    }

    /**
     * @expectedException     NERDZ\Exceptions\NERDZHttpException
     *
     * @expectedExceptionCode 404
     */
     public function testFailDoesExist(){
     	$hash="Fsh6zkt6ZneW"; //this MUST be an non-existing file
    	$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);

    	$this->assertFalse($return);
     }

    public function TestUploadFile(){
    	$file='files/paolo.jpeg';
    	$hash="iwvA-OjFOssN"; //hash of the file
        $Uphash=NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);

        $this->assertEquals($hash, $Uphash);

       	$file= new NERDZ\FileUpload\NERDZFile($file);
        $Uphash=NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);

        $this->assertEquals($hash, $Uphash);

    }

    public function testUploadFileViaURL(){
    	$url="http://www.starcoppe.it/images/grafica-immagine-b.jpg"; // MUST be a valid url

    	$Uphash=NERDZ\FileUpload\NERDZCrushWrapper::uploadFileViaURL($url);

    	$this->assertTrue($Uphash!=null);
    }

    public function testDelete(){
    	$file='files/paolo.jpeg';
        $Uphash=NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);

        $result=NERDZ\FileUpload\NERDZCrushWrapper::delete($Uphash);
        $this->assertEquals("success", $result);

    }

    public function testGetFileInfo(){
    	$hash="Fsh6zkt6Znew"; //this MUST be an existing file
    	//$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);

    	$file=NERDZ\FileUpload\NERDZCrushWrapper::getFileInfo($hash);
    	$this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $file);
    	$this->assertEquals("Fsh6zkt6Znew", $file->getHash());
    	$this->assertEquals("https://media.nerdz.eu/Fsh6zkt6Znew.jpg", $file->getFilePath());
    	$this->assertEquals("/Fsh6zkt6Znew.jpg", $file->getFile());
    	$this->assertEquals("Fsh6zkt6Znew.jpg", $file->getFileName());
    }

    public function testGetFile(){
    	$hash="Fsh6zkt6Znew"; //this MUST be an existing file
    	//$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);

    	$file=NERDZ\FileUpload\NERDZCrushWrapper::getFile($hash);
    	$this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $file);
    	$this->assertEquals("Fsh6zkt6Znew", $file->getHash());
    	$this->assertEquals("https://media.nerdz.eu/Fsh6zkt6Znew.jpg", $file->getFilePath());
    	$this->assertEquals("/Fsh6zkt6Znew.jpg", $file->getFile());
    	$this->assertEquals("Fsh6zkt6Znew.jpg", $file->getFileName());
    }

      public function testGetFiles(){
    	$hashes=array("Fsh6zkt6Znew", "C4PFHof8-Bnh"); //this MUST be an existing file
    	//$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);
    	$results_first=array(
    		'getHash' => 'Fsh6zkt6Znew',
    		'getFilePath' => 'https://media.nerdz.eu/Fsh6zkt6Znew.jpg',
    		'getFile' => '/Fsh6zkt6Znew.jpg',
    		'getFileName' => 'Fsh6zkt6Znew.jpg'
    		);
    	$results_second=array(
    		'getHash' => 'C4PFHof8-Bnh',
    		'getFilePath' => 'https://media.nerdz.eu/C4PFHof8-Bnh.jpg',
    		'getFile' => '/C4PFHof8-Bnh.jpg',
    		'getFileName' => 'C4PFHof8-Bnh.jpg'
    		);

    	$files=NERDZ\FileUpload\NERDZCrushWrapper::getFiles($hashes);

    	$this->assertCount(2, $files);
    	$this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files[0]);
    	$this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files[1]);

    	foreach ($results_first as $key => $value) {
    		$this->assertEquals($value, $files[0]->$key());
    	}
    	foreach ($results_second as $key => $value) {
    		$this->assertEquals($value, $files[1]->$key());
    	}
    }
    public function testGetFileInfos(){
    	$hashes=array("Fsh6zkt6Znew", "C4PFHof8-Bnh"); //this MUST be an existing file
    	//$return= NERDZ\FileUpload\NERDZCrushWrapper::doesExist($hash);
    	$results_first=array(
    		'getHash' => 'Fsh6zkt6Znew',
    		'getFilePath' => 'https://media.nerdz.eu/Fsh6zkt6Znew.jpg',
    		'getFile' => '/Fsh6zkt6Znew.jpg',
    		'getFileName' => 'Fsh6zkt6Znew.jpg'
    		);
    	$results_second=array(
    		'getHash' => 'C4PFHof8-Bnh',
    		'getFilePath' => 'https://media.nerdz.eu/C4PFHof8-Bnh.jpg',
    		'getFile' => '/C4PFHof8-Bnh.jpg',
    		'getFileName' => 'C4PFHof8-Bnh.jpg'
    		);

    	$files=NERDZ\FileUpload\NERDZCrushWrapper::getFileInfos($hashes);

    	$this->assertCount(2, $files);
    	$this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files['Fsh6zkt6Znew']);
    	$this->assertInstanceOf('NERDZ\FileUpload\NERDZFile', $files['C4PFHof8-Bnh']);

    	foreach ($results_first as $key => $value) {
    		$this->assertEquals($value, $files['Fsh6zkt6Znew']->$key());
    	}
    	foreach ($results_second as $key => $value) {
    		$this->assertEquals($value, $files['C4PFHof8-Bnh']->$key());
    	}
    }


}
