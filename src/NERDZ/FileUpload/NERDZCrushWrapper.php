<?php

	namespace NERDZ\FileUpload

	public class NERDZCrushWrapper{

		private static $NERDZAPIUrl='';
		private static $UserAgent='NERDZCrushWrapper'


		/**
		 *	@param string hash
		 *
		 *	@return NERDZFile
		 *
		 */
		public static function getFileInfo($hash){

			$url= $NERDZAPIUrl . $hash;
			$request= array('http' => array(
				'method' => 'GET',
				'user-agent' => self::$UserAgent)
			)

			$context= stream_context_create($request);	//need to add error checking
			$fd = fopen($url, 'rb' , false, $context);
			$response = stream_get_contents($fd);

			$infos=json_decode($response);

			if(isset($infos['error'])){
				//error! (TODO)
			}

			$file=new NERDZFile($infos);
									
		}

		/**
		 *	@param string[] hash
		 *
		 *	@return NERDZFile[]
		 *
		 */
		public static function getFileInfos($hashes){

			

		}

		/**
		 *	@param string hash
		 *
		 *	@return boolean
		 *
		 */
		public static function doesExist($hash){

		}
		/**
		 *	@param file file
		 *
		 *	@return string
		 *
		 */
		public static function uploadFile($file){

		}

		/**
		 *	@param string url
		 *
		 *	@return string
		 *
		 */
		public static function uploadFileViaURL($url){

		}

		/**
		 *	@param string hash
		 *
		 *	
		 *
		 */
		public static function delete($hash){

		}

		/**
		 *	@param string hash
		 *
		 *	@return NERDZFile
		 *
		 */
		public static function getFile($hash){

		}
		/**
		 *	@param string[] hashes
		 *
		 *	@return NERDZFile[]
		 *
		 */
		public static function getFiles($hashes){

			$returnFiles=array();
			$i=0;

			foreach ($hashes as $hash) {
				$returnFiles[$i++]=self::getFile($hash);
			}

			return returnFiles;

		}
	}