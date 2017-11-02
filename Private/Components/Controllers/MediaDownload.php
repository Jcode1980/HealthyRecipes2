<?php 
	require_once BLOGIC."/BLogic.php"; 
	 
	class MediaDownload extends PLController 
	{ 
		public function __construct($formData) 
		{ 
			parent::__construct($formData);
			setUseHTML(false);
		} 
		
		public function appendToResponse()
		{
		    $params = url_params();
			if (count($params) < 2) {
				http_response_code(400);
				echo "invalid request";
				exit;
			}
			
			$token = $params[0];
			$id = $params[1];
			if (! is_numeric($id)) {
				http_response_code(400);
				echo "invalid request";
				exit;
			}
			$res = safeValue($params, 2, "full");			
			
			// validate user can access the requested post.
			$qual = new BLAndQualifier(array(
				new BLKeyValueQualifier("token", OP_EQUAL, $token),
				new BLKeyValueQualifier("id", OP_EQUAL, $id)
			));
			$file = BLGenericRecord::findSingle("FileUpload", $qual);
			if (! $file) {
				http_response_code(400);
				echo "invalid request";
				exit;
			}
			$realEntity = $file->vars["type"];
			if ($realEntity != $file->className()) {
				// transform the entity into it's real subclass.
				$dict = $file->asDictionary();
				$dict["entity"] = $realEntity;
				unset($realEntity, $file);
				$file = BLGenericRecord::restoreFromDictionary($dict);
				unset($dict);
			}
						
			$path = ($res == "reduced") ? $file->safeLowresPath() : $file->highresPath();
			$name = $file->field("fileName");
			
			header("Content-Type: ". $file->field("mimeType"));
			header("Content-Length: ".filesize($path));
			if (! $file->isImage() && ! $file->isVideo())
				header("Content-Disposition: attachment; filename=$name");
			
			$handle = fopen($path, "rb");
			fpassthru($handle);
			exit;
		}
		
		public function handleRequest()
		{
			$page = parent::handleRequest();
			if ($page) {
				http_response_code(401);
				echo "You were denied access.";
				exit;
			}
		}
	} 
?>
