<?php
// ===========================
// = Database Initialisation =
// ===========================
//  Enable this to utilise a database. 
//    Datasources include:
//        - BLMySQLDataSource
//        - BLMSSQLDataSource
//        - BLFileMakerDataSource
//        - BLCSVDatasource



	require_once BLOGIC."/BL/BLMySQLDataSource.php";
	
	debugln("database.php set");
	
	if (DEPLOYED)
	{
		// production database
		//BLDataSource::setDefaultDataSource(new BLMySQLDataSource("localhost", "healthykitch", "Lanas111", "healthykitch"));
		BLDataSource::setDefaultDataSource(new BLMySQLDataSource("localhost", "healthyKitchUser", "games", "HealthyKitch"));
	}
	else if (TESTING)
	{
		// testing database
		debugln("being set");
		BLDataSource::setDefaultDataSource(new BLMySQLDataSource("localhost", "root", "", "HealthyRecipes2"));
	}

?>