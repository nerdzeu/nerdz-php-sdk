<?php

	namespace NERDZ\FileUpload;
	use NERDZ\Exceptions\NERDZHttpException;

	class NERDZCrushWrapper{


		//some config vars
		private static $NERDZAPIUrl='http://media.nerdz.eu/api/';
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
				self::triggerError($infos->error);
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
			$infos=json_decode($infos);

			if(isset($infos->error)){
				self::triggerError($infos->error);
			}

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
		public static function uploadFile(NERDZFile $file){

		}

		/**
		 *	@param string url
		 *
		 *	@return string
		 *
		 */
		public static function uploadFileViaURL($uploadUrl){
			$url= self::$NERDZAPIUrl . 'upload/url';
			$post=array(
				'url' => $uploadUrl
			);

			$context = array(
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => http_build_query($post)
			);

			$response=self::request($url, $context);
			$response=json_decode($response);
			var_dump($response);
			if(isset($response->error))
				self::triggerError($response->error);

			return $response->hash;
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

			$infos=self::request($url, $context);
			$infos=json_decode($infos);
			
			if(isset($infos->error)){
				self::triggerError($infos->error);
			}

			return $infos;

		}

		/**
		 *	@param string hash
		 *
		 *	@return NERDZFile
		 *
		 */
		public static function getFile($hash){
			$file=self::getFileInfo($hash);
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

			curl_setopt_array($ch, $context);
			curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    		if(!$result=curl_exec($ch)){
    			throw new NERDZSDKException(curl_error($ch));
    		}
			if($result == null)
				triggerError(curl_getinfo($ch, CURLINFO_HTTP_CODE));

			curl_close($ch);
			
			return $result;
		}

		private static function triggerError($error){
			switch ($error) {
					case '404':
						throw new NERDZHttpException("The requested file does not exist.", 404);
						break;
					case '400':
						throw new NERDZHttpException("The URL is invalid.", 400);
						break;
					case '401':
						throw new NERDZHttpException("The IP does not match the stored hash.", 401);
						break;
					case '408':
						throw new NERDZHttpException("You are no longer allowed to edit the title or description. (timeout)", 408);
						break;
					case '409':
						//this is success
						break;
					case '413':
						throw new NERDZHttpException("The file is larger than maximum allowed size.", 413);
						break;
					case '414':
						throw new NERDZHttpException("Either of the fields was over 2048 characters.", 414);
						break;
					case '415':
						throw new NERDZHttpException("The file extension is not acceptable.", 415);
						break;
					case '420':
						throw new NERDZHttpException("The rate limit was exceeded. Enhance your calm.", 420);
						break;
					default:
						throw new NERDZHttpException("Unrecognized error", $error);
						break;
				}

		}
	}