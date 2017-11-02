<?php 
	//
	// RecipeImage.php
	// 
	// Created on 2016-12-23 @ 08:30 pm.
	 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT."/Entities/File.php";
	
	class RecipeImage extends File 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
		} 
	 
	
		public function type(){
			return 1;
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
		
		public function imagePath()
		{
			//debugln("imagae path being called returning: " . "/Users/john/Desktop/Images/Recipes/".$this->vars["id"].".".$this->vars["fileExtension"]);
			//return "/Users/john/Desktop/Images/Recipes/".$this->vars["id"].".".$this->vars["fileExtension"];
			return $this->filePath();
		}
		
		public function hasImage()
		{
			$path = getcwd()."/".$this->imagePath();
			return file_exists($path);
		}
        
// 		public function thumbnailImagePath()
// 		{
// 			return "images/recipes/".$this->vars["id"]."_thumb.".$this->vars["fileExtension"];
// 		}
		
		public function hasThumbnailImage()
		{
			//$path = getcwd()."/".$this->thumbnailImagePath();
			$path = $this->thumbnailImagePath();
			return file_exists($path);
		}
        
        public function delete()
        {
            $path = getcwd()."/".$this->imagePath();
            if (file_exists($path))
                unlink($path);
            return parent::delete();
        }
        
        public function safeThumbnailPath()
        {
            if ($this->hasThumbnailImage()) {
                return $this->thumbnailImagePath();
            }
            return "images/NoImage_thumb.jpg";
        }
        
        public function safeImagePath()
        {
        	debugln("my path: " . $this->imagePath());
            if ($this->hasImage()) {
                return $this->imagePath();
            }
            return "images/NoImage.jpg";

        }
		
	} 
?>
