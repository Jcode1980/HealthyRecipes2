<?php 
	//
	// Recipe.php
	// 
	// Created on 2016-12-23 @ 07:52 pm.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class Recipe extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
			
			$this->defineRelationship(new BLToManyRelationship("mesauredingredients", $this, "MeasuredIngredient", "id", "recipeID", false, null, ["sortID" => ORDER_ASCEND]));
			$this->defineRelationship(new BLToManyRelationship("images", $this, "RecipeImage", "id", "recipeID", true, null, ["id" => ORDER_ASCEND]));
			$this->defineRelationship(new BLToOneRelationship("mainImage", $this, "RecipeImage", "mainImageID", "id"));
			$this->defineRelationship(new BLToOneRelationship("createdby", $this, "User", "userID", "userID"));
			
			$this->vars["created"] = mysql_date();
		} 
	 
		public function tableName() 
		{ 
			return "Recipe"; 
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
		
        public function displayImagePath()
        {
            $path = $this->valueForKeyPath("mainImage.safeImagePath");
            if (! $path) {
                $images = $this->arrayValueForRelationship("images");
                if (count($images) > 0)
                    $path = $images[0]->safeImagePath();
                else {
                    $path = "images/NoImage.jpg";
                }
            }
            return $path;
        }
        
        public function displayImageThumbnailPath()
        {
            $path = $this->valueForKeyPath("mainImage.safeThumbnailPath");
            if (! $path) {
                $images = $this->arrayValueForRelationship("images");
                if (count($images) > 0)
                    $path = $images[0]->safeThumbnailPath();
                else {
                    $path = "images/NoImage_thumb.jpg";
                }
            }
            return $path;
        }
        
        public function hasMasterImage()
        {
            return $this->vars["mainImageID"] > 0 ? "Yes" : "No";
        }
		
        public function sortedMeasuredIngredients(){
        	$found = BLGenericRecord::find("MeasuredIngredient", new BLKeyValueQualifier("recipeID", OP_EQUAL, $this->vars["id"]), array("sortID" => ORDER_ASCEND, "id" =>ORDER_ASCEND));
        	debugln("found ingredients : " . count($found));
        	return $found;
        }
        
        public function sortedInstructions(){
        	$found = BLGenericRecord::find("Instruction", new BLKeyValueQualifier("recipeID", OP_EQUAL, $this->vars["id"]), array("sortID" => ORDER_ASCEND, "id" =>ORDER_ASCEND));
        	return $found;
        }
        
        public function createInstruction(){
        	debugln("creating instruction");
        		
        	$newInstruction = BLGenericRecord::newRecordOfType("Instruction");
        	$newInstruction->vars["recipeID"] = $this->vars["id"];
        
        	$lastInstruction = $this->lastInstruction();
        		
        	$sortID =  $lastInstruction ? $lastInstruction->vars["sortID"] + 1 : 1;
        	$newInstruction->vars["sortID"] = $sortID;
        		
        	return $newInstruction;
        }
        
        public function reassignInstructionSortIDs(){
        	$this->reassignSortID($this->sortedInstructions());
        }
        
        public function reassignIngredientSortIDs(){
        	$this->reassignSortID($this->sortedMeasuredIngredients());
        }
        
        public function reassignSortID($objectsArray){
        	//$andQualifier = new BLAndQualifier(array(new BLKeyValueQualifier("recipeID", OP_EQUAL, $this->vars["id"]), new BLKeyValueQualifier("sortID", OP_GREATER, $this->vars["sortID"])));
        	//$found = BLGenericRecord::find("Instruction", $andQualifier, array("sortID" => ORDER_ASCEND, "id" =>ORDER_ASCEND));
        	debugln("reassignSortID");
        	//find all instructions that have a greater sortID
        	foreach ($objectsArray as $object){
        		$newSortID = array_search($object, $objectsArray);
        		debugln("I'm sortID: " . $newSortID);
        		$object->vars["sortID"] = $newSortID+1;
        		$object->save();
        	}
        	//loop through all Instructions
        		//set sort -1
        		
        	
        }
        
        public function lastInstruction(){
        	$currentInstructions = $this->sortedInstructions();
        	return end($currentInstructions);
        }
        
        public function createMeasuredIngredient(){
        	debugln("creating ingredeitn");
        
        	$newIngredient = BLGenericRecord::newRecordOfType("MeasuredIngredient");
        	$newIngredient->vars["recipeID"] = $this->vars["id"];
        
        	
        	$lastIngredient = $this->lastMesauredIngredient();
        	$sortID =  $lastIngredient ? $lastIngredient->field("sortID") + 1 : 1;
        		
        	$newIngredient->vars["sortID"] = $sortID;
        
        	return $newIngredient;
        
        }
        
        public function lastMesauredIngredient(){
        		
        	$currentIngredients = $this->sortedMeasuredIngredients();
        	if(count($currentIngredients) > 0){
        		return end($currentIngredients);
        	}
        	else{
        		return null;
        	}
        }
        
        
        public function sortedComents(){
        	$found = BLGenericRecord::find("Comment", new BLKeyValueQualifier("recipeID", OP_EQUAL, $this->vars["id"]), array("created" => ORDER_ASCEND));
        	return $found;
        }
        
        public function addMealType($mealType){
        	//check if meal Type already exists in many to many relationship
        		
        	if(!$this->mealTypeExistsInRecipe($mealType)){
        		$newMealTypeRecipe = BLGenericRecord::newRecordOfType("MealTypeRecipe");
        		$newMealTypeRecipe->vars["recipeID"] = $this->vars["id"];
        		$newMealTypeRecipe->vars["mealTypeID"] = $mealType->vars["id"];
        
        		$newMealTypeRecipe->save();
        	}
        		
        }
        
//         public function mealTypeExistsInRecipe($mealType){
//         	$qual = new BLAndQualifier(array(
//         			new BLKeyValueQualifier("recipeID", OP_EQUAL, $this->vars["id"]),
//         			new BLKeyValueQualifier("mealTypeID", OP_EQUAL, $mealType->vars["id"]),
        				
//         	));
//         	$found = BLGenericRecord::find("MealTypeRecipe", $qual);
//         	debugln("found mealType in recipe: " + count($found));
        		
//         	return count($found) > 0;
//         }

        public function mealTypeExistsInRecipe($mealType){
        	return $this->objectFromInManyToManyRelationship($mealType, "MealTypeRecipe", "mealTypeID");
        }
        
        public function mealTypes(){
        	$id = $this->field("id");
        	$mealTypes = array();
        		
        	if(!$id){
        		return $mealTypes;
        	}
        		
        	$qual = new BLAndQualifier(array(
        			new BLKeyValueQualifier("recipeID", OP_EQUAL, $id),
        	));
        	$foundMealTypeRecipes = BLGenericRecord::find("MealTypeRecipe", $qual);
        
        	if(count($foundMealTypeRecipes) > 0){
        		$mealTypeRecipeArray = array();
        
        		//Is there a shortcut for this??
        		foreach ($foundMealTypeRecipes as $mealTypeRecipe){
        				
        			array_push($mealTypeRecipeArray, $mealTypeRecipe->vars["mealTypeID"]);
        		}
        
        		$mealTypes = $this->objectMatchingKeyAndValue("MealType", "id", $mealTypeRecipeArray);
        
        	}
        	debugln("found meal Types: " . count($mealTypes));
        	return $mealTypes;
        }
        
        public function dietaryCategories(){
        	return $this->objectsFromManyToManyRelationship("DietaryCategory", "DietaryCategoryRecipe", "dietaryCategoryID");
        }
        
        public function dietaryCategoryExistsInRecipe($dietaryCategory){
        	return $this->objectExistsInManyToManyRelationship($dietaryCategory, "DietaryCategory", "dieteryCategoryID");
        }
        
        public function addDietaryCategory($dietaryCategory){
        	//check if meal Type already exists in many to many relationship
        
        	$this->addObjectToManyToManytRelationship($dietaryCategory, "DietaryCategoryRecipe", "dietaryCategoryID");
        }
        
        
        public function addNutritionalBenefit($nutritionalBenefit){
        	//check if meal Type already exists in many to many relationship
        
        	$this->addObjectToManyToManytRelationship($nutritionalBenefit, "NutritionalBenefitRecipe", "nutritionalBenefitID");
        }
        
        public function nutritionalBenefits(){
        	return $this->objectsFromManyToManyRelationship("NutritionalBenefit", "NutritionalBenefitRecipe", "nutritionalBenefitID");
        }
        
        public function nutritionalBenefitExistsInRecipe($nutritionalBenefit){
        	return $this->objectExistsInManyToManyRelationship($nutritionalBenefit, "NutritionalBenefit", "nutritionalBenefitID");
        }
        
        public function objectsFromManyToManyRelationship($entity, $joinTable, $foreignKey){
        	$id = $this->field("id");
        	$objectsArray = array();
        	
        	if(!$id){
        		return $objectsArray;
        	}
        	
        	$qual = new BLAndQualifier(array(
        			new BLKeyValueQualifier("recipeID", OP_EQUAL, $id),
        	));
        	$foundJoinFields = BLGenericRecord::find($joinTable, $qual);
        	
        	if(count($foundJoinFields) > 0){
        		$objectsIDArray = array();
        	
        		//Is there a shortcut for this??
        		foreach ($foundJoinFields as $joinField){
        	
        			array_push($objectsIDArray, $joinField->vars[$foreignKey]);
        		}
        	
        		$objectsArray = $this->objectMatchingKeyAndValue($entity, "id", $objectsIDArray);
        	
        	}
        	debugln("found objects: " . count($objectsArray));
        	return $objectsArray;
        }
        
        public function addObjectToManyToManytRelationship($object, $joinTable, $foreignKey){
        	//check if meal Type already exists in many to many relationship
        
        	if(!$this->objectFromInManyToManyRelationship($object, $joinTable, $foreignKey)){
        		$newObject = BLGenericRecord::newRecordOfType($joinTable);
        		$newObject->vars["recipeID"] = $this->vars["id"];
        		$newObject->vars[$foreignKey] = $object->vars["id"];
        
        		$newObject->save();
        	}
        }
        
        
        
        	
        public function objectFromInManyToManyRelationship($object, $joinTable, $foreignKey){
        	$qual = new BLAndQualifier(array(
        			new BLKeyValueQualifier("recipeID", OP_EQUAL, $this->vars["id"]),
        			new BLKeyValueQualifier($foreignKey, OP_EQUAL, $object->vars["id"]),
        
        	));
        	$found = BLGenericRecord::find($joinTable, $qual);
        	//debugln("found mealType in recipe: " + count($found));
        
        	if(count($found) > 0){
        		return $found[0];
        	}
        	else{
        		return null;
        	}
        }
        
        public function createCommentForUser($user){
        	debugln("creating comment");
        
        	$newComment = BLGenericRecord::newRecordOfType("Comment");
        	$newComment->vars["recipeID"] = $this->vars["id"];
        
        	$newComment->vars["userID"] = $user->vars["id"];
        	return $newComment;
        }
        
        //public function objectMatchingKeyAndValue("ReportType", "reportTypeID", pk);
        public function objectMatchingKeyAndValue($entity, $key, $arrayValues){
        	$qualifiers = array();
        		
        	foreach ($arrayValues as $value) {
        		debugln("pushing mealTypeIDs as qual: " . $value);
        		array_push($qualifiers, new BLKeyValueQualifier($key, OP_EQUAL, $value));
        	}
        		
        	return BLGenericRecord::find($entity, new BLOrQualifier($qualifiers));
        }
        
	    public function removeObjectFromManyToManytRelationship($object, $joinTable, $foreignKey){
        	//check if meal Type already exists in many to many relationship
        	$foundObject = $this->objectFromInManyToManyRelationship($object, $joinTable, $foreignKey);
        	if($foundObject){
        		$foundObject->delete();
        	}
        }
        
        public function removeMealType($mealType){
        	$this->removeObjectFromManyToManytRelationship($mealType, "MealTypeRecipe", "mealTypeID");
        }
        
        public function removeDietaryCategory($dietaryCategory){
        	$this->removeObjectFromManyToManytRelationship($dietaryCategory, "DietaryCategoryRecipe", "dietaryCategoryID");
        }
        
        public function removeNutritionalBenefit($nutritionalBenefit){
        	$this->removeObjectFromManyToManytRelationship($nutritionalBenefit, "NutritionalBenefitRecipe", "nutritionalBenefitID");
        }
        
        public function instructionsDisplay(){
        	return nl2br($this->field("instructions"));
        }
        
        
        public function userCanEditRecipe($user){
        	if($this->field("userID") != null && $user != null){
        		$createdByUser = $this->valueForRelationship("user");
        		if($createdByUser != null){
        			return ($user->field("id") == $createdByUser->field("userID"));
        		}
        		else{
        			return true;
        		}
        	}
        	else
        		return true;
        	
        }
        
        
        
	} 
?>
