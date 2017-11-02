<?php	
	require_once(BLOGIC."/BLogic.php");
	require_once ROOT."/Components/Controllers/PageWrapper.php";
	
	class FrontPage extends PageWrapper
	{
		public function __construct($formData)
		{
			parent::__construct($formData, "FrontPage");
		}
		
		protected $recipes;
		
		public function recipes()
		{
			//debugln("am i getting here??? recipes");
			
			if (! $this->recipes)
			{
				$this->recipes = BLGenericRecord::find("Recipe");
			}
			return $this->recipes;
		}
		
		public function recipesByGrid()
		{
			$recipes = $this->recipes();
			
			$rows = [];
			$row = [];
			$j = 1;
			for ($i = 0; $i < count($recipes); $i++)
			{
				$row[] = $recipes[$i];
				if ($j == 3) {
					$rows[] = $row;
					$j = 1;
					$row = [];
				}
				else
					$j++;
			}
			if (count($row) > 0)
				$rows[] = $row;
			return $rows;
		}
	}
?>