<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT."/Components/Controllers/SessionController.php";
	//weird if i require this one.. it will die!!	
	//require_once ROOT."/Components/Controllers/LoginModal.php";
	require_once BLOGIC."/Utils/FormUtils.php";
		
	//class PageWrapper extends PLController
	class PageWrapper extends SessionController
	{ 
		protected $innerTemplate;
		protected $hasInvalidTransactionID = false;
		
		public $errorMessage;
		public $alertMessage;
		
		public function __construct($formData, $innerTemplate) 
		{ 
			parent::__construct($formData, "PageWrapper"); 
			$this->innerTemplate = $this->templateForName($innerTemplate);
			$this->innerTemplate->set("controller", $this);
			$this->innerTemplate->set("form", $this->form);
			
			addJS("js/form-validation.js");
			
			addJS("js/jquery-2.1.4.min.js");
			
			setSessionValueForKey("yes", "test");
		} 
		
		// prevent page reloads and back button from repeating previous actions.
		public function handleRequest()
		{ 
			$page = parent::handleRequest();
			if (! $page)
			{
			    if (useHTML() && sessionID())
                {
                    $t_id = sessionValueForKey("transactionID");
        			if ($t_id == "" || $this->formValueForKey("transactionID") != $t_id)
        			{
        				$this->hasInvalidTransactionID = true;
        			}
        			setSessionValueForKey(uniqid(), "transactionID");
                }
			}	
            return $page;
		}
		
		public function appendToResponse()
		{
			if (useHTML()) // only render the page when not called from ajax.
				parent::appendToResponse();
		}

// 	public function handleRequest()
// 	{
// 		$page = parent::handleRequest();
// 		if (! $page)
// 		{
// 			if (sessionKeysExist("SERVER_GENERATED_SID", "userID")) {
// 				$this->nextPage();
// 			}
// 		}
// 		return $page;
// 	}
		
		public function validateLogin()
		{	
			setUseHTML(false);
		
			if (! sessionValueForKey("test"))
			{
				$result = array("error" => 1, "message" => "Sorry your browser does not appear to have cookies enabled. Please ensure you're browser is set to accept cookies before continuing.");
				echo json_encode($result);
				return;
			}
				
			// 1 second delay to help prevent brute force bots.
			sleep(1);
		
			$client_ip = safeValue($_SERVER, "REMOTE_ADDR");
			if ($client_ip && ipIsBanned($client_ip)) {
				debugln("rejecting $client_ip, too many attempts.");
				logBadIPAttempt($client_ip);
				$result = array("error" => 1, "message" => "Too many login attempts. Please try again in a little bit.");
				echo json_encode($result);
				return;
			}
			else if (! $client_ip) {
				debugln("#WARNING: no client ip found for login. Can not perform auto-ban check.");
			}
				
			$login = trim(strip_tags($this->formValueForKey("login")));
			if ($login == "")
			{
				$result = array("error" => 1, "message" => "The login you entered is not valid.");
				echo json_encode($result);
				return;
			}
			$password = trim(strip_tags($this->formValueForKey("password")));
			if ($password == "")
			{
				$result = array("error" => 1, "message" => "The password you entered is not valid.");
				echo json_encode($result);
				return;
			}
		
			$user = BLGenericRecord::recordMatchingKeyAndValue("User", "email", $login);
			debugln("validateLogin: found user: " . ($user != null));
			//TODO: use has passwords
			//if (! $user || ! password_verify($password, $user->field("password"))) {
			if (! $user || ($password !=$user->field("password"))) {
				$this->incorrectLogin($client_ip);
				return;
			}
			else{
				debugln("validateLogin: found user");
			}
				
			regen_session_id();
				
			storeSessionValues(array(
			"SERVER_GENERATED_SID" => "yes",
			"LAST_REQUEST" => time(),
			"userID" => $user->field("id"),
			"user" => $user->asDictionary(),
			));
			removeSessionValueForKey("test"); // clear the test var.
			//debugln("validateLogin: found user222");
			
			$result = array("error" => 0);
			//debugln("the result is: " .  print_r($result));
			echo json_encode($result);
		}
		
		public function renderInnerTemplate()
		{
			echo $this->innerTemplate->fetch();
		}
		
		public function hasInvalidTransactionID()
		{
			return $this->hasInvalidTransactionID;
		}
		
		public function formMethod()
		{
			return "post";
		}
        
        public function metaKeywords()
        {
            return appName();
        }
        
        public function metaDescription()
        {
            return "";
        }
        
        public function pageTitle()
        {
            return appName();
        }
        
        public function conocialURL()
        {
            return domainName()."/".$this->className();
        }
        
        /** Data Upload **/
        function checkUpload($form, $varName, $limit=null, $index = null)
        {
        	debugln("checkUpload");
        	//check file was uplaoided to expected var name
        	if (!isset($_FILES[$varName]))
        	{
        		debugln("$varName NOT SET");
        		return false;
        	}
        	$file = $_FILES[$varName];
        
        	//check file uploaded ok
        	$fileStatus = (is_array($file['error']) && isset($index))  ? $file["error"][$index] : $file["error"];
        	$name = (is_array($file['name']) && isset($index))  ? $file["name"][$index] : $file['name'];
        
        	debugln("check upload....." . $fileStatus);
        	if (debugLogging() > 0)
        		debugln("$varName upload status of $name: $fileStatus");
        	if ($fileStatus == UPLOAD_ERR_OK) {
        		//Check File Size
        		$src = (is_array($file['tmp_name']) && isset($index)) ?  $file['tmp_name'][$index] : $file["tmp_name"];
        		debugln("ma limit: ");
        		if(!isset($limit)){
        			$limit  = 50 * 1024 * 1024; // 50MB;
        		}
        		
        		
        		
        		if (filesize($src) > $limit)
        		{
        			$form->errorMessage = "The file $name you tried to attach exceeds the maximum file size allowed. Please keep your attachments below ".floor($limit/1024/1024)."MB in size.";
        			return false;
        		}
        	} else if ($fileStatus != UPLOAD_ERR_NO_FILE) {
        		debugln("Upload Error");
        		if ($fileStatus == UPLOAD_ERR_INI_SIZE)
        		{
        			debugln("PageWrapper: upload error: size");
        			$form->errorMessage = "The file $name you tried to attach exceeds the maximum allowed file size. Please keep your attachments beloow ".floor($limit/1024/1024)."MB in size.";
        		}
        		else if ($fileStatus == UPLOAD_ERR_PARTIAL || $fileStatus ==  UPLOAD_ERR_FORM_SIZE)
        		{
        			$form->errorMessage = "The file $name you attached did not fully upload to the server. Please try again.";
        		}
        		else
        		{
        			$form->errorMessage = "Sorry, an error occured while trying to upload your image $name. <br>".join('<br>', $fileStatus)."<br>Please contact support for assistance.";
        			//trigger_error("Error occured while uploading attachment '$name': $fileStatus\n\nUser: ".$this->user()->fullName());
        		}
        		return false;
        	}
        
        	debugln("Returning true for check upload");
        	//If no errors found, return true;
        	return true;
        }
        
        public function uploadImages($inputName = 'image', $imageEntityName = 'Image', $joinKey = null, $parentJoinKey =null)
        {
        	$entity = $this->currentEntity();
        	debugln("this is the entity: " . get_class($entity));
        	if (! $entity)
        	{
        		$this->errorMessage = "Error Uploading Image. Please ensure you have created the item first by clicking save.";
        		return;
        	}
        	debugln("first goat");
        	 
        	//$imageFileType = pathinfo(basename($_FILES["fileToUpload"]["name"]),PATHINFO_EXTENSION);
        	// Check if image file is a actual image or fake image
        	 
        	//debugln("fileType is: " . $imageFileType);
        	debugln("da files : " . var_dump($_FILES));
        	if (! empty($_FILES))
        	{
        		debugln("goat");
        		$entityID = $entity->field($entity->pkNames());
        		if (! $joinKey) {
        			$joinKey = strtolower($entity->tableName()).strtoupper($entity->pkNames());
        		}
        		 
        		foreach ($_FILES as $inputName => $fileInfo)
        		{
        			if (empty($inputName) || empty($fileInfo)) {
        				continue;
        			}
        			$count = sizeof($fileInfo["tmp_name"]);
        			$limit = 50 * 1024 * 1024; // 50MB;
        
        			$fileStatus = $fileInfo["error"];
        			$name = $fileInfo["name"];
        			if (empty($name)) {
        				continue;
        			}
        			
        			//if (! $this->checkUpload($this, $inputName, $limit, 0)) {
        			if (! $this->checkUpload($this, $inputName, null, 0)) {
        				continue;
        			}
        			$src = $fileInfo["tmp_name"];
        
        			debugln("goat fileName: " . $src);
        			 
        			$size = getimagesize($src);
        			$mimeType = $newImage->vars["mimeType"] = image_type_to_mime_type($size[2]);
        
        			if (strpos($mimeType, 'image') !== 0) {
        				$this->errorMessage = 'Uploaded file: ' . $name . ' is not an image';
        				continue;
        			}
        
        			$this->checkImageInfo($size);
        			if ($this->errorMessage) {
        				// if image check set an error message, return this to the user and don't upload image
        				return;
        			}
        
        			$destPath = "";
        			if ($imageEntityName)
        			{
        				$newImage = BLGenericRecord::newRecordOfType($imageEntityName);
        				$newImage->vars["fileName"] = $name;
        				$newImage->vars["width"] = $size[0];
        				$newImage->vars["height"] = $size[1];
        				$newImage->vars["mimeType"] = $mimeType;
        				$parts = explode(".", $name);
        				$newImage->vars["fileExtension"] = end($parts);
        				$newImage->vars[$joinKey] = $entityID;
        				try {
        					$newImage->save();
        				}
        				catch (Exception $error) {
        					debugln($error->getMessage());
        					$this->errorMessgae = "There was an error saving your image. Please consult the log file for more information.";
        					return;
        				}
        				 
        				//configure any fields and joins, return image path
        				$destPath = $this->getImagePath($newImage);
        				debugln("dest path is: " . $destPath);
        			}
        			if (! $destPath)
        				return;
        
        			/** init directory structure as necessary **/
        			$dir = dirname($destPath);
        			if (! is_dir($dir)) {
        				mkdir($dir, 0777, true);
        			}
        
        			debugln("saving in da upload image $destPath", 1);
        			// Store full resolution image
        			if (! move_uploaded_file($src, $destPath))
        			{
        				$newImage->delete();
        				debugln("failed to move file to $destPath");
        				$this->errorMessage = "Sorry there was a problem uploading the file $name. Please try again.";
        				return;
        			}
        			debugln("doing image svaed!!!!!!:");
        			$this->imageSaved($newImage);
        		}
        	}
        	else{
        		debugln("No files to be uploaded");
        	}
        }
        
        protected function imageSaved($image) {
        
        	debugln("imageSaved being called");
        
        	$lowres = new Imagick($image->imagePath());
        	if ($image->field("width") > $image->field("height"))
        		$lowres->thumbnailImage(240, 0);
        	else
        		$lowres->thumbnailImage(0, 240);
        	debugln("thumbnail path: " . $image->thumbnailImagePath());
        	file_put_contents($image->thumbnailImagePath(), $lowres);
        }
        
     
	} 
?>
