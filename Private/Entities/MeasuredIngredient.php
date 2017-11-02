<?php 
	//
	// Ingredient.php
	// 
	// Created on 2016-12-23 @ 08:32 pm.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class MeasuredIngredient extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
			
			$this->defineRelationship(new BLToOneRelationship("recipe", $this, "Recipe", "recipeID", "id"));
			$this->defineRelationship(new BLToOneRelationship("metric", $this, "Metric", "metricID", "id"));
		} 
	 
		public function tableName() 
		{ 
			return "MeasuredIngredient"; 
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
		
		/* 	Override this method if you have any database fields that deal in
			raw binary data.
			WARNING: attributes returned from here do not get escaped when working with the
			MySQLDataSource so be very very careful on trusting the contents of the data
			you are working with!
		*/
		/* public function binaryAttributes()
		{
			return array();
		}
		*/
		/*
		public function awakeFromFetch()
		{
			
		}*/	 
		
		/*public function validateForSave()
		{
			
		}*/
		
		public function metricDisplay(){
			$metric =  $this->valueForRelationship("metric");
			if($metric != null){
				return $metric->field("code");
			}
			else{
				return null;
			}
		}
		
		public function ingredientDisplay(){
			$display = "";
			if($this->field("amount") != null){
				$display = $display . "&nbsp;&nbsp;" . $this->field("amount");
			}
				
			if($this->metricDisplay() != null){
				$display = $display . "&nbsp;" . $this->metricDisplay();
			}
				
			if($this->field("name") != null){
				$display =($display . "&nbsp;&nbsp;&nbsp;" . $this->field("name"));
			}
				
			return $display;
		}
		
	} 
?>
