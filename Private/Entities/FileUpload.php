<?php 
	//
	// FileUpload.php
	// 
	// Created on 2017-05-13 @ 03:44 pm.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class FileUpload extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
		} 
	 
		public function tableName() 
		{ 
			return "FileUpload"; 
		} 
		 
		public function pkNames() 
		{ 
			return "id"; 
		}
		
		/*
			Override this method if you have any database fields which should not
			be modified or saved back to the server. This provides only 'quiet' protection.
			It does not pass any errors or warnings back if field data has changed, it merely
			ommits the fields from the save request.
		*/
		public function readOnlyAttributes()
		{
			return array("id");
		}	
		
		public function isImage()
		{
			return BLStringUtils::startsWith($this->field("mimeType"), "image/");
		}
		
		public function hasImageData()
		{
			return ($this->field("width") != "" && $this->field("height") != "");
		}
		
		public function isPlayableVideo()
		{
			return BLStringUtils::startsWith($this->field("mimeType"), "video/");
		}
		
		public function highresPath()
		{
			return $this->folderPath()."/".$this->vars["id"].".".$this->vars["fileExtension"];
		}
		
		public function hasHighres()
		{
			$path = getcwd()."/".$this->highresPath();
			return file_exists($path);
		}
		
		public function lowresPath()
		{
			return $this->folderPath()."/".$this->vars["id"]."_thumb.".$this->vars["fileExtension"];
		}
		
		protected function folderPath()
		{
			return ROOT."/Persistence/Uploads";
		}
		
		public function hasLowres()
		{
			$path = getcwd()."/".$this->lowresPath();
			return file_exists($path);
		}
		
		public function safeLowresPath()
		{
			if ($this->hasLowres())
				return $this->lowresPath();
			return $this->hasHighres() ? $this->highresPath() : "images/noImage_thumb.jpg";
		}
		
		public function url($low = false)
		{
			$parts = [
				DOWNLOAD_ROUTE,
				$this->field("token"),
				$this->field("id")
			];
			if ($low) {
				$parts[] = "reduced";
			}
			return implode("/", $parts);
		}
        
        public function delete()
        {
			$this->removeFile();
            return parent::delete();
        }
		
		public function removeFile()
		{
			$cwd = getcwd();
            $path = "$cwd/".$this->highresPath();
            if (file_exists($path))
                unlink($path);
            $path = "$cwd/".$this->lowresPath();
            if (file_exists($path))
                unlink($path);
		}
	} 
?>
