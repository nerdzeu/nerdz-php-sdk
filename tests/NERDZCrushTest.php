<?php

require '../autoload.php';

$file= new NERDZ\FileUpload\NERDZFile('files/paolo.jpeg');

$hash=NERDZ\FileUpload\NERDZCrushWrapper::uploadFile($file);

var_dump($hash);

var_dump(NERDZ\FileUpload\NERDZCrushWrapper::doesExist('Fsh6zkt6Znew'));

$n = NERDZ\FileUpload\NERDZCrushWrapper::getFileInfo('Fsh6zkt6Znew');
var_dump($n);

echo NERDZ\FileUpload\NERDZCrushWrapper::uploadFileViaURL('http://www.keenthemes.com/preview/metronic/theme/assets/global/plugins/jcrop/demos/demo_files/image1.jpg');

var_dump(NERDZ\FileUpload\NERDZCrushWrapper::doesExist('tcRpfLPj6cZW'));

var_dump(NERDZ\FileUpload\NERDZCrushWrapper::delete('tcRpfLPj6cZW'));
