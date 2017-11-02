<?php
    require_once BLOGIC."/BLogic.php";
    require_once ROOT."/Components/Controllers/PageWrapper.php";
	require_once ROOT."/Components/Controllers/RecipePreview.php";

    class LoginModal extends PLController  {
    	
    	public function __construct($formData)
    	{
    		parent::__construct($formData, "LoginModal");
    	}
    	
    	
  
    	
    	//simple login for now.. needs to be more ellaborate though.. authentication from email etc. 
    	public function signUpAction(){
    		
    		$page = $this->pageWithName("RecipeList");
    		$email = trim(strip_tags($this->formValueForKey("email")));
    		$given = trim(strip_tags($this->formValueForKey("given")));
    		$surname = trim(strip_tags($this->formValueForKey("surname")));
    		$signUpPassword = trim(strip_tags($this->formValueForKey("signUpPassword")));
    		
    		debugln("signupAction bub email: " .$email . " given: " . $given . " surname: " . $surname . " password: " . $signUpPassword);
			
    		
    		if ($email != "" && $signUpPassword != "")
    		{
    			$user = BLGenericRecord::recordMatchingKeyAndValue("User", "email", $email);
    			
    			if($user){
    				debugln("User Already exists with that email.");
    				return;
    			}
    			else{
    				debugln("No user exists with that email");
    			}
    			
    			//debugln("Creating a recipe");
                $newUser = BLGenericRecord::newRecordOfType("User");
                $newUser->setValueForField($surname, "surname");
                $newUser->setValueForField($given, "given");
                $newUser->setValueForField($email, "email");
                $newUser->setValueForField($signUpPassword, "password");
                
                $newUser->save();
                
                storeSessionValues(array(
                "SERVER_GENERATED_SID" => "yes",
                "LAST_REQUEST" => time(),
                "userID" => $newUser->field("id"),
                "user" => $newUser->asDictionary(),
                ));
                removeSessionValueForKey("test"); // clear the test var.
    			
    		}
    		else{
    			debugln("something is wong");
    		}
    	
    		
    		
    		return $page;
    	}
    	
    	
    	public function validateLogin()
    	{
    		setUseHTML(false);
    	
    		if (! sessionValueForKey("test"))
    		{
    			$result = array("error" => 1, "message" => "Sorry your browser does not appear to have cookies enabled. Please ensure you're browser is set to accept cookies before continuing.");
    			echo json_encode($result);
    			return;
    		}
    	
    		// 1 second delay to help prevent brute force bots.
    		sleep(1);
    	
    		$client_ip = safeValue($_SERVER, "REMOTE_ADDR");
    		if ($client_ip && ipIsBanned($client_ip)) {
    			debugln("rejecting $client_ip, too many attempts.");
    			logBadIPAttempt($client_ip);
    			$result = array("error" => 1, "message" => "Too many login attempts. Please try again in a little bit.");
    			echo json_encode($result);
    			return;
    		}
    		else if (! $client_ip) {
    			debugln("#WARNING: no client ip found for login. Can not perform auto-ban check.");
    		}
    	
    		$login = trim(strip_tags($this->formValueForKey("login")));
    		if ($login == "")
    		{
    			$result = array("error" => 1, "message" => "The login you entered is not valid.");
    			echo json_encode($result);
    			return;
    		}
    		$password = trim(strip_tags($this->formValueForKey("password")));
    		if ($password == "")
    		{
    			$result = array("error" => 1, "message" => "The password you entered is not valid.");
    			echo json_encode($result);
    			return;
    		}
    	
    		$user = BLGenericRecord::recordMatchingKeyAndValue("User", "email", $login);
    		debugln("validateLogin: found user: " . ($user != null));
    		//TODO: use has passwords
    		//if (! $user || ! password_verify($password, $user->field("password"))) {
    		if (! $user || ($password !=$user->field("password"))) {
    			$this->incorrectLogin($client_ip);
    			return;
    		}
    		else{
    			debugln("validateLogin: found user");
    		}
    	
    		regen_session_id();
    	
    		storeSessionValues(array(
    		"SERVER_GENERATED_SID" => "yes",
    		"LAST_REQUEST" => time(),
    		"userID" => $user->field("id"),
    		"user" => $user->asDictionary(),
    		));
    		
    		debugln("loginmodal");
    		debugln(sessionKeysExist("SERVER_GENERATED_SID") ?  "i r sesison exists" : "me no exists");
    		
    		removeSessionValueForKey("test"); // clear the test var.
    		//debugln("validateLogin: found user222");
    			
    		$result = array("error" => 0);
    		//debugln("the result is: ");
    		//debugln(print_r($result));
    		echo json_encode($result);
    		}
    }
    
    
    