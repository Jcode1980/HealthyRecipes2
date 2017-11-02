<?php
	require_once BLOGIC."/BLogic.php";
	
		
	class SessionController extends PLController
	{
		public function __construct($formData, $componentName)
		{
			parent::__construct($formData, $componentName);
		}
		
		/*
			Examine the time elapsed between this request and the previous one. If it's found to be beyond
			a certain time frame then destory the session and log the user out.
		*/
		public function handleRequest()
		{

            $page = parent::handleRequest();
            if (! $page)
            {
    			$lastRequest = safeValue($_SESSION, "LAST_REQUEST");
    			if ($lastRequest == "")
    				$lastRequest = 0;

    			$timedOut = (time()-$lastRequest) > 3600; // 1 hour idle timeout
    			debugln("i'm being handled gay gay: lastRequest: " . $lastRequest .  " timedout: " . ($timedOut ? 'true' : 'false') . "  time out value: " . (time()-$lastRequest));
								

				$error = "";
				//debugln("goat here sessionController with user: " . $this->user()->field("given"));
    			if ($timedOut && isset($_SESSION["SERVER_GENERATED_SID"]))
    			{
					debugln("i'm timing out session");
    				$error = "Forr security reasons your session timed out as it was idle for too long. Please log in again.";
    				$this->logout($error);
    			}
    			
    			$_SESSION["LAST_REQUEST"] = time();
    			
//     			//no page ever requires login ... except for profiles but that would only be available to login users anyway
//     			else if (empty($_SESSION["userID"]) || ! isset($_SESSION["SERVER_GENERATED_SID"]))
//     			{
//     				$error = "Your session was deemed to be invalid. Please log in again.";
//     			}
			
//     			if ($error)
//     			{
//     				$this->logout($error);
//     			}
//     			else{
//     				$_SESSION["LAST_REQUEST"] = time();
//     			}	
            }
            return $page;
		}
		
		
		protected $user;
		
		public function user()
		{
			if (! $this->user && isset($_SESSION["user"]))
			{
				$this->user = BLGenericRecord::restoreFromDictionary($_SESSION["user"]);
			}
			return $this->user;
		}
		
		public function logout($errorMessage = "")
		{
			debugln("Calling logout");
            if (session_id() != "") 
            {
    			session_unset();
    			session_destroy();
    			removeSessionKeys("SERVER_GENERATED_SID");
    			//debugln("server genereted SID?? " . var_dump(isset($_SESSION["SERVER_GENERATED_SID"])));
    			$severSID = sessionValueForKey("SERVER_GENERATED_SID");
    			debugln("what's my sid logout?? " . $severSID);
    			if(sessionKeysExist()){
    				debugln("not set");
    			}
    			else{
    				debugln("is set");
    			}
    			//debugln("server genereted SID?? " . $crap);
    			//debugln("Calling logout 2 user: " . (user() == null));
            }
            session_start();
            $page = $this->pageWithName("RecipeList");
            if ($errorMessage) {
                $page->errorMessage = $errorMessage;
            }
            return null;
		}
		
		public function hasUser(){
			$severSID = sessionValueForKey("SERVER_GENERATED_SID");
			debugln("what's my sid?? " . $severSID);
			if(sessionKeysExist("SERVER_GENERATED_SID")){
				debugln("sessionKeysExist user: is set");
			}
			else{
				debugln("sessionKeysExist user: is not set");
			}
			//return $this->user() != null && isset($_SESSION["SERVER_GENERATED_SID"]);
			debugln("hasUser!: " . ($this->user() != null && sessionKeysExist("SERVER_GENERATED_SID")));
			return $this->user() != null && sessionKeysExist("SERVER_GENERATED_SID");
		}
	}
?>