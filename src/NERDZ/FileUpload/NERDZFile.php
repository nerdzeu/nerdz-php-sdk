<?php

    /**
    * 
    *
    */
    namespace NERDZ\FileUpload;

    class NERDZFile{

        /**
        *   Here we have all the infos.
        */
        private $infos=array();

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

        public function __construct($info=null){

            //dirty and shitty. MUST redo.
            $this->infos=$info;

        }
    }