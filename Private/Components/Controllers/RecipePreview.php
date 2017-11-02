<?php

    require_once BLOGIC."/BLogic.php";
    require_once ROOT."/Components/Controllers/EditViewComponent.php";
    require_once ROOT."/Components/Controllers/SessionController.php";
    require_once ROOT."/Entities/MealType.php";
    require_once BLOGIC."/BLogic.php";
    //require_once ROOT."/Utils/imagick.php";

    class RecipePreview extends SessionController   
    {
        protected $currentRecipe;

		public function __construct($formData) 
        { 
            parent::__construct($formData, "RecipePreview");
            
            global $bl_url_args;
            $this->id = safeValue($bl_url_args, 0);
            
            //addCSS('css/editform.css');
        } 

		public function currentRecipe(){
			return $this->currentRecipe;
		}

		public function setCurrentRecipe($recipe){
			$this->currentRecipe = $recipe;
		}
    
		
		public function editRecipe($recipeID){
				$page = $this->pageWithName("RecipeEdit");
				$page->id = $recipeID;	
    	
				return $page;
		}
    	
		public function canEditRecipe(){
			if($this->hasUser()){
				return $this->currentRecipe()->userCanEditRecipe($this->user());
			}
			else{
				return false;
			}
		}
		
    }

?>