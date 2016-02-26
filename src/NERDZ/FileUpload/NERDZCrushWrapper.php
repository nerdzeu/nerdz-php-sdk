<?php

	namespace NERDZ\FileUpload;

	class NERDZCrushWrapper{


		//some config vars
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
			$infos=self::request($url);

			$infos=json_decode($infos);

			if(isset($infos->error)){
				//error! (TODO)
			}

			$file=new NERDZFile($infos);

			return $file;
									
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

			$infos=self::request($url);

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
			$info=self::request($url);

			$info=json_decode($info);
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

			$file=self::request($url, array());		//need error checking
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
			$context= array( 
				CURLOPT_CUSTOMREQUEST => 'DELETE',
			);

			$info=self::request($url, $context);

			$info=json_decode($info);


			//error checking

			return $info;

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

			/* TODO: json_decode*/

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

			return $returnFiles;

		}

		/**
		*	return contents and status code
		*
		*	@return array[]
		*/

		private static function request($url, $context = array()){

			$ch=curl_init($url);

			foreach ($context as $key => $value) {
				curl_setopt($ch, $key, $value);
			}

			//set the user-agent
			curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    		if(!$result=curl_exec($ch)){
    			curl_error($ch);
    		}

			curl_close($ch);

			return $result;
		}
	}