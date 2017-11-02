<?php 
	require_once BLOGIC."/BLogic.php"; 
	require_once ROOT."/Components/Controllers/EditViewComponent.php";
	 
	class RecipeView extends EditViewComponent 
	{ 
		protected $currentRecipe;
		
		public function __construct($formData) 
		{ 
			parent::__construct($formData, "RecipeView");
		} 
	
		public function handleRequest(){
			$page = parent::handleRequest();

			if(!$page){
				$this->id = safeValue(url_params(), 0);
				debugln("whats the id? " . $this->id); 

			}
			
			return $page;
		} 
		
		public function currentEntity(){
			return $this->currentRecipe();
		}
		
		
		public function currentRecipe()
		{
			//debugln("currentRecipe: my id is111: " . $this->id);
			//debugln(var_dump(debug_backtrace()));
			if (!$this->currentRecipe)
			{
				debugln("my id is: " . $this->id);
				//debugln(print_r(debug_backtrace(), TRUE));
				if (isset($this->id) && is_numeric($this->id))
				{
					$this->currentRecipe = BLGenericRecord::recordMatchingKeyAndValue("Recipe", "id", $this->id);
				}
			}

		
			debugln("this is the current recipe: " . $this->currentRecipe->vars["id"]);
			debugln("this is the current recipe instructions: " . $this->currentRecipe->field("instructions"));
			return $this->currentRecipe;
		}
		
		
	
	} 
	
?>
