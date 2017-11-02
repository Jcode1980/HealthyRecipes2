<?php 
	//
	// Metric.php
	// 
	// Created on 2017-04-10 @ 11:18 pm.
	 
	require_once BLOGIC."/BLogic.php"; 
	 
	class Metric extends BLGenericRecord 
	{ 
		public function __construct($dataSource = null) 
		{ 
			parent::__construct($dataSource); 
		} 
	 
		public function tableName() 
		{ 
			return "Metric"; 
		} 
		 
		public function pkNames() 
		{ 
			return "id"; 
		}
		
		
		public static function allMetrics(){
			return BLGenericRecord::find("Metric", null);
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
		
		
		/*
			If you are using a JSON datasource and need more fine grained control in sorting over how any particular 
			field data type is interpretted then you can use this function to return hints for keys you wish to sort
			by.
			Available Hints: SORT_HINT_STRING, SORT_HINT_NUMBER, SORT_HINT_DATE. The default is always SORT_HINT_STRING.
		*/
		/*public function sortHintForAttribute($key)
		{
			return SORT_HINT_STRING;
		}
		*/
		
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
		
	} 
?>
