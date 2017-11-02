<?php
    require_once BLOGIC."/BLogic.php";
    require_once ROOT."/Components/Controllers/EditViewComponent.php";
    require_once ROOT."/Entities/MealType.php";
    require_once ROOT."/Entities/DietaryCategory.php";
    require_once ROOT."/Entities/NutritionalBenefit.php";
    require_once ROOT."/Entities/Metric.php";
    require_once BLOGIC."/BLogic.php";
    //require_once ROOT."/Utils/imagick.php";

    class RecipeEdit extends EditViewComponent   
    {
        protected $prevPage = 'RecipeList';
        protected $currentRecipe;
        protected $selectedMealType;
		protected $selectedMeasuredIngredient;
		protected $selectedInstruction;
		protected $selectedComment;        

        public function __construct($formData) 
        { 
            parent::__construct($formData, "RecipeEdit");
        } 


	 	public function handleRequest(){
			$page = parent::handleRequest();

			if(!$page){
				$this->id = safeValue(url_params(), 0);
				//debugln("whats the id? " . $this->id); 

			}
			
			return $page;
		} 

    
        public function currentRecipe() 
        {
			("currentRecipe: my id is111: " . $this->id);
			////debugln(var_dump(debug_backtrace()));
            if (!$this->currentRecipe) 
            {
				//debugln("my id is: " . $this->id);
				////debugln(print_r(debug_backtrace(), TRUE));
                if (isset($this->id) && is_numeric($this->id)) 
                {
                    $this->currentRecipe = BLGenericRecord::recordMatchingKeyAndValue("Recipe", "id", $this->id);
                } 
                else {
                	//debugln("Creating a recipe");
                    $this->currentRecipe = BLGenericRecord::newRecordOfType("Recipe");
                    $this->currentRecipe->save();
                    $this->id = $this->currentRecipe->field("id");
                    $bl_url_args[0] = $this->id;
                }
            } 
			else{
				//debugln("currentRecipe is set?? ");
			}
            
			//debugln("this is the current recipe: " . $this->currentRecipe->vars["id"]);
			return $this->currentRecipe;
        }
        
        

        /**
         * Wrapper for current entity, for ease of use with wrapper and templates
         */
        public function currentEntity() {
			//debugln("calling current entity");
            return $this->currentRecipe();
        }
    
        public function selectedMealType(){
        	return selectedMealType;
        }
        
        public function setSelectedMealType($theMealType){
        	$this->selectedMealType = $theMealType;
        }
        
        
        /**
         * Save Entity
         */
        public function save() 
        {
        	debugln($this->formData);
            $this->processFormValueKeyPathsForSave();
            try {
            	$this->currentEntity()->save();	

   				if($this->selectedComment()){
                	$this->selectedComment()->save();
                }
                
                if($this->selectedMeasuredIngredient()){
                	$this->selectedMeasuredIngredient()->save();
                }
                
                if($this->selectedInstruction()){
                	$this->selectedInstruction()->save();
                }
                
                $this->alertMessage = "Changes have been saved.";
            }
    		catch (Exception $error) {
    			//debugln($error->getMessage());
    			$this->errorMessage = "There was a problem saving changes to this item. Please try again.";
    			return;
    		}
        
        
            if (! $this->id) {
                $id = $this->currentEntity()->field($this->currentEntity()->pkNames());
                $this->goToLocation(null, array($id));
            }
        }
        
        public function backAction(){
        	$this->save();
        	//return nextPage();
        	$nextPage = $this->pageWithName("RecipeList");
        	return $nextPage;
        } 
        
        public function editIngredient() {
        	try {
        		/*$encrypted = [];
        		$ignored = [
        		
        		];
        		$escapeHTML = false;
        		$containingOnlyPrefix = "";
        		$this->processFormValueKeyPathsForSave($encrypted, $ignored, $escapeHTML, $containingOnlyPrefix);
        		$this->currentEntity()->save();
        		$this->*/
        		
        		$this->save();
        		if (! $this->errorMessage) {
        			//debugln("editIngredient: " . $this->formValueForKey("newIngID"));
        			$this->setFormValueForKey($this->formValueForKey("newIngID"), "selectedIngredientID");
        			$this->selectedMeasuredIngredient = null;
        		}
        		
        		//return $this->pageWithName("");
        	}
        	catch (Exception $error) {
        		//debugln($error->getMessage());
        		$this->errorMessage = "There was a problem saving the page, please try again.";
        	}
        		
        }
        
        public function createInstruction()
        {
        	if ($this->currentEntity()){
        		$this->save();
        		$newInstruction = $this->currentEntity()->createInstruction();
        		
        		$this->processFormValueKeyPathsForSave();
        
        		try {
        			$newInstruction->save();
        			//debugln("new instruction selected should be: " . $newInstruction->field("sortID"));
        			$this->selectedInstruction = null;
        			$this->setFormValueForKey(doEncrypt($newInstruction->field("id")), "selectedInstructionID");
        			//$savedID = doEncrypt($newInstruction->vars["incomeSourceID"]);
        			//$this->setFormValueForKey($savedID, "selectedIncomeSourceID");
        		}
        		catch (Exception $error) {
        			$this->errorMessage = "There was an error creating the Income Source. Please try again.";
        			debugln($error->getMessage());
        		}
        	}
        	//debugln("done creating instruction", 2);
        }
        
        public function createIngredient()
        {
        	if ($this->currentEntity()){
        		$this->save();
        		$newIngredient = $this->currentEntity()->createMeasuredIngredient();
        
        		$this->processFormValueKeyPathsForSave();
        
        		try {
        			$newIngredient->save();
        
        			$this->selectedMeasuredIngredient = null;
        			$this->setFormValueForKey(doEncrypt($newIngredient->field("id")), "selectedIngredientID");
        			
        			//$savedID = doEncrypt($newInstruction->vars["incomeSourceID"]);
        			//$this->setFormValueForKey($savedID, "selectedIncomeSourceID");
        		}
        		catch (Exception $error) {
        			$this->errorMessage = "There was an error creating the Income Source. Please try again.";
        			//debugln($error->getMessage());
        		}
        	}
        	//debugln("done creating instruction", 2);
        }
        
   
        public function mealTypesArray(){
        	return MealType::allMealTypes();
        }
         
        public function dietaryCategoriesArray(){
        	return DietaryCategory::allDietaryCategories();
        }
        
        public function nutritionalBenefitsArray(){
        	return NutritionalBenefit::allNutritionalBenefits();
        }
        
        public function addMealType(){
        	//debugln("form value for key for mealt type is : " .  $this->formValueForKey("mealTypeSelection"));
        	$id =  $this->formValueForKey("mealTypeSelection");
        	
        	$foundMealType = BLGenericRecord::recordMatchingKeyAndValue("MealType", "id", $id);
        	$this->currentRecipe()->addMealType($foundMealType);
        }
        
        public function removeMealType(){
        	$id =  doDecrypt($this->formValueForKey("objectForDeletionID"));
        	//debugln("going to remove meal type : " . $id);
        	$this->currentRecipe()->removeMealType(BLGenericRecord::recordMatchingKeyAndValue("MealType", "id", $id));
        }
        
        public function mealTypesForRecipe(){
        	return $this->currentEntity()->mealTypes();
        }
        
        
        public function addDietaryCategory(){
        	//debugln("form value for key for dietaryCategory type is : " .  $this->formValueForKey("dietaryCategorySelection"));
        	$id =  $this->formValueForKey("dietaryCategorySelection");
        	 
        	$foundDietaryCategory = BLGenericRecord::recordMatchingKeyAndValue("DietaryCategory", "id", $id);
        	$this->currentRecipe()->addDietaryCategory($foundDietaryCategory);
        }
        
        public function removeDietaryCategory(){
        	$id =  doDecrypt($this->formValueForKey("objectForDeletionID"));
        	//debugln("going to removeDietaryCategory : " . $id);
        	$this->currentRecipe()->removeDietaryCategory(BLGenericRecord::recordMatchingKeyAndValue("DietaryCategory", "id", $id));
        }
        

        public function addNutritionalBenefit(){
        	//debugln("form value for key for nutritionalBenefit type is : " .  $this->formValueForKey("nutritionalBenefitSelection"));
        	$id =  $this->formValueForKey("nutritionalBenefitSelection");
        
        	$foundNutritionalBenefit = BLGenericRecord::recordMatchingKeyAndValue("NutritionalBenefit", "id", $id);
        	$this->currentRecipe()->addNutritionalBenefit($foundNutritionalBenefit);
        }
        
        public function removeNutritionalBenefit(){
        	$id =  doDecrypt($this->formValueForKey("objectForDeletionID"));
        	//debugln("going to removeNutritionalBenefit : " . $id);
        	$this->currentRecipe()->removeNutritionalBenefit(BLGenericRecord::recordMatchingKeyAndValue("NutritionalBenefit", "id", $id));
        }
        
        
		public function createComment(){
			$comment = $this->currentRecipe()->createCommentForUser($this->user());
			$comment->save();	
		}    

		public function deleteComment() 
        {
    		$this->deleteObject("Comment", "selectedCommentID");        
		}

		public function deleteInstruction() 
        {
    		$this->deleteObject("Instruction", "selectedInstructionID");
    		$this->currentRecipe()->reassignInstructionSortIDs();
		} 

		public function deleteIngredient()
		{
			$this->deleteObject("MeasuredIngredient", "selectedIngredientID");
			$this->currentRecipe()->reassignIngredientSortIDs();
		}
				
		public function deleteObject($entity, $selectedIDString){
			//debugln("delete object");
			$objectDeletionID = doDecrypt($this->formValueForKey("objectForDeletionID"));

			$selectedID = doDecrypt($this->formValueForKey("objectForDeletionID"));
			if(isset($selectedID) && $selectedID == $objectDeletionID){
				$this->setFormValueForKey(null, $selectedIDString);
			}

			$objectForDeletion = BLGenericRecord::recordMatchingKeyAndValue($entity, "id", $objectDeletionID);
			$objectForDeletion->delete();		
		}

		public function isCurrentComment($commentID){
			$selectedCommentID =  doDecrypt($this->formValueForKey("selectedCommentID"));
			//debugln("this is the selectedCOmment: " . $selectedCommentID);
			
			return (isset($selectedCommentID) && $selectedCommentID == $commentID);
		}

		public function isCurrentInstruction($instructionID){
			$selectedInstructionID =  doDecrypt($this->formValueForKey("selectedInstructionID"));
			////debugln("this is the selec: " . $selectedInstructionID);
			
			return (isset($selectedInstructionID) && $selectedInstructionID == $instructionID);
		}

		public function isCurrentIngredient($ingredientID){
			$selectedIngredientID =  doDecrypt($this->formValueForKey("selectedIngredientID"));
			//debugln("this is the selectedIngredient: " . $selectedIngredientID);
			
			return (isset($selectedIngredientID) && $selectedIngredientID == $ingredientID);
		}

		public function selectedComment(){
			if (!$this->selectedComment) 
            {
				////debugln("my id is: " . $this->id);
				$selectedCommentID = doDecrypt($this->formValueForKey("selectedCommentID"));

                if (isset($selectedCommentID) && is_numeric($selectedCommentID)) 
                {
                    $this->selectedComment = BLGenericRecord::recordMatchingKeyAndValue("Comment", "id", $selectedCommentID);
                } 
            } 
            return $this->selectedComment;

		}

		public function selectedMeasuredIngredient(){
			
			$selectedIngredientID = doDecrypt($this->formValueForKey("selectedIngredientID"));
			//debugln("selectedMeasuredIngredient : got here : " . $selectedIngredientID);
			
			if (!isset($this->selectedMeasuredIngredient) && isset($selectedIngredientID)  && is_numeric($selectedIngredientID)) 
            {
				
				//debugln("my selected ingredient is: " . $selectedIngredientID);
				$this->selectedMeasuredIngredient = BLGenericRecord::recordMatchingKeyAndValue("MeasuredIngredient", "id", $selectedIngredientID);
                

                //debugln("selectedIngredient 1"  .  get_class($this->selectedMeasuredIngredient));
            } 
            
            return $this->selectedMeasuredIngredient;
		}

		public function selectedInstruction(){
			
			if (!$this->selectedInstruction) 
            {
				////debugln("my id is: " . $this->id);
				$selectedInstructionID = doDecrypt($this->formValueForKey("selectedInstructionID"));

                if (isset($selectedInstructionID) && is_numeric($selectedInstructionID)) 
                {
                    $this->selectedInstruction = BLGenericRecord::recordMatchingKeyAndValue("Instruction", "id", $selectedInstructionID);
                    //debugln("my insturciton id is: " . $this->selectedInstruction->field("id"));
                } 
                else{
                	//debugln("instruction not set");
                }
                
                //debugln("selectedInstruction 1: returning: " .  get_class($this->selectedInstruction));
            } 
            
           
            return $this->selectedInstruction;
		}

		
		public function metricsArray(){
			return Metric::allMetrics();
		}
		
        


        /****************************************          
         *          Image Assist functions      *
         *          - Override as necessary     *
         ****************************************/
    
        /**
        * Override to specify the entity for images.    
        */
    	public function processImages()
    	{
            $this->save();
            if (! $this->errorMessage)
    		    parent::uploadImages('image', 'RecipeImage', "recipeID");
            
    	}
    
        /**
         * Called before saving an image - check size, filetype, etc.
         * if $this->errorMessage is set, image will not be saved
         */
        /*protected function checkImageInfo($imgInfo) {
        } */
     

   
        /*******************************************/
    }

?>