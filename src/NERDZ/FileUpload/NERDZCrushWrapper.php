<?php

	namespace NERDZ\FileUpload;

	class NERDZCrushWrapper{

		private static $NERDZAPIUrl='https://media.nerdz.eu/api/';
		private static $userAgent='NERDZCrushWrapper';



		/**
     	* Change the server URL where the API resides.
     	* 
     	* @param serverApiUrl The URL where the API waits for connections.
     	*/
    	public static function changeApiURL($serverApiUrl) {
    		self::$NERDZAPIUrl = $serverApiUrl;
    	}

    	/**
     	* Get the server URL where the API resides.
     	* @return
     	*        The URL where the API waits for connections.
     	*/
    	public static function getApiURL() {
        	return self::$NERDZAPIUrl;
    	}

    	
		/**
		 *	@param string hash
		 *
		 *	@return NERDZFile
		 *
		 */
		public static function getFileInfo($hash){

			$url= self::$NERDZAPIUrl . $hash;
			$context= array('http' => array(
				'method' => 'GET',
				'user-agent' => self::$userAgent)
			);

			$infos=self::request($url, $context);

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

			$url= self::$NERDZAPIUrl . 'info?list=';
			$i=0;
			//quick and dirty way to not have a comma in the end of the url
			foreach ($hashes as $hash) {
				if($i!=0){
					$url .= ',';
				}
				$i++;
				$url .= $hash;
			}
			
			$context= array('http' => array(
				'method' => 'GET',
				'user-agent' => self::$userAgent)
			);

			

			$infos=self::request($url, $context);

			//da finire. Basta iterare e creare i vari oggetti

		}

		/**
		 *	@param string hash
		 *
		 *	@return boolean
		 *
		 */
		public static function doesExist($hash){

			$url= self::$NERDZAPIUrl . $hash . '/exists';
			$context= array('http' => array(
				'method' => 'GET',
				'user-agent' => self::$userAgent)
			);

			$info=self::request($url, $context);

			return $info->exists;

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

			$file=file_get_contents($url);		//need error checking
			self::uploadFile($file);

		}

		/** Need to create another method to deliting NERDZCrushFile instead.
		 *	@param string hash
		 *
		 *	
		 *
		 */
		public static function delete($hash){
			$url= self::$NERDZAPIUrl . $hash ;
			$context= array('http' => array(
				'method' => 'DELETE',
				'user-agent' => self::$userAgent)
			);

			$info=self::request($url, $context);

			//check for errors...to-do (:

		}

		/**
		 *	@param string hash
		 *
		 *	@return NERDZFile
		 *
		 */
		public static function getFile($hash){
			if(!self::doesExist($hash))
				return null;

			$file=self::getFileInfo($hash);

			/*..*/

			return $file;

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

		private static function request($url, $context){

			$scontext= stream_context_create($context);	//need to add error checking
			try{
				$fd = @fopen($url, 'rb' , false, $scontext);
				$response = @stream_get_contents($fd);
			}
			catch(Exception $e){
				echo $e->message;
			}
			return json_decode($response);
		}
	}