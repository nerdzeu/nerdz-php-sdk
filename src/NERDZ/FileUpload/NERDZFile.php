<?php
    namespace NERDZ\FileUpload
    
    class NERDZFile{

        private $hash;

        private $infos;

        /**
        * @return boolean
        */
        public function getCompression(){}

        /**
        *   @return NERDZFile
        */
        public function getOriginalFile(){}

        /**
        *   @return string
        */
        public function getFile(){}

        /**
        *   @return string
        */
        public function getFileType(){}

        /**
        *   @return NERDZFile[]
        */
        public function getFiles(){}

        /**
        *   @return string
        */
        public function getHash(){}

        /**
        *
        */
        public function getFileStatus(){

        }

        public function __construct(){

        }
    }