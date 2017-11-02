<?php 
	require_once BLOGIC."/BLogic.php"; 
	 
	class MediaUpload extends PLController 
	{ 
		public function __construct($formData) 
		{ 
			parent::__construct($formData);
			setUseHTML(false);
		} 
		
		public function appendToResponse()
		{
		    if (useHTML()) {
		        parent::appendToResponse();
			}
		}
		
		public function handleRequest()
		{
			$page = parent::handleRequest();
			if (! $page) {
				$this->process();
			}
			else {
				http_response_code(401);
				echo "You were denied access.";
				exit;
			}
		}
		
		public function process()
		{
			$fn = safeValue($_SERVER, "HTTP_FILENAME");
			$token = safeValue($_SERVER, "HTTP_TOKEN");
			$mimeType = safeValue($_SERVER, "HTTP_MIMETYPE");
			$type = strtolower(safeValue($_SERVER, "HTTP_UTYPE"));
			debugln("== Media Upload ==", 1);
			debugln($fn, 1);
			debugln($token, 1);
			debugln($type, 1);
			
			if (strlen($token) != 13) {
				echo "invalid token.";
				exit;
			}
			
			global $allowed_upload_types;
			$entity = safeValue($allowed_upload_types, $type);
			if (! $entity) {
				echo "invalid type.";
				exit;
			}
			
			$blacklist = array(".php", ".phtml", ".html", ".php3", ".php4", ".js", ".shtml", ".pl" ,".py", ".sh", ".exe");
			foreach ($blacklist as $file) 
			{
				if (preg_match("/$file\$/i", $fn)) 
				{
					echo "ERROR: Uploading executable files not allowed\n";
					exit;
				}
			}
			
			if ($fn && $token && $mimeType && $type) 
			{
				$fileReq = BLGenericRecord::newRecordOfType($entity);
				$fileReq->vars["token"] = $token;
				$fileReq->vars["fileName"] = $fn;
				$parts = explode(".", $fn);
				$fileReq->vars["fileExtension"] = end($parts);
				$fileReq->vars["mimeType"] = $mimeType;
				$fileReq->vars["type"] = $entity;
				$fileReq->save();
								
				$path = $fileReq->highresPath();
				file_put_contents($path, file_get_contents('php://input'));
				
				if ($fileReq->isImage()) 
				{
					$size = getimagesize($path);
					if (! $size) {
						$fileReq->delete();
						echo "invalid file.";
						exit;
					}
					$fileReq->vars["width"] = $size[0];
					$fileReq->vars["height"] = $size[1];
					
					if ($size[0] > 500 || $size[1] > 500)
					{
						// generate lowres thumbnail.
						$lowres = new Imagick($fileReq->highresPath());
						if ($fileReq->field("width") > $fileReq->field("height"))
							$lowres->thumbnailImage(500, 0);
						else
							$lowres->thumbnailImage(0, 500);
						file_put_contents($fileReq->lowresPath(), $lowres);
					}
				}
				$fileReq->vars["fileSize"] = filesize($path);
				$fileReq->save();
				
				echo json_encode(["pass" => 1, "csrf" => sessionValueForKey("transactionID")]);
			}
			else {
				debugln("missing tokens, aborted.");
				echo "invalid upload.";
			}
		} 
	} 
?>
