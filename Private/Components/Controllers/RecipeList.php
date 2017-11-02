<?php
    require_once BLOGIC."/BLogic.php";
    require_once ROOT."/Components/Controllers/ListViewComponent.php";
	require_once ROOT."/Components/Controllers/RecipePreview.php";

    class RecipeList extends ListViewComponent   
    {
        protected $headers = array(
    		'id' => 'id',
            'name' => 'name',
            'options' => 0
        );
        
        protected $default_sort_key = "name";
		
        public function __construct($formData) 
        { 
            parent::__construct($formData, "RecipeList");
            
            if (! $this->formValueForKey("sortBy")) {
                $this->setFormValueForKey($this->default_sort_key, "sortBy");
            }
            if (! $this->formValueForKey("sortOrder")) {
                $this->setFormValueForKey(ORDER_ASCEND, "sortOrder");
            }
        } 
        
        public function handleRequest(){
        	debugln("man handled");
        	debugln($this->formData);
        	$page = parent::handleRequest();
        	return $page;
        }
        
        
        
       
        /* Return Display Group or List */
        public function getDisplayGroup() 
        {
            if (! $this->displayGroup) 
            {
                $quals = array();
                $filters = $this->allFormValuesWhoseKeysStartWith('filter|');
                $foundDate = false;
                foreach ($filters as $filter=>$value) 
                {
                    if ($value == null) {
                        continue;
                    }
                    $key = str_replace('filter|', '', $filter);
                    switch ($key) 
                    {
                        case 'startDate':
                            $quals[] = $this->processStartDate($value, 'filter|endDate');
                            $foundDate = true;
                            break;
                        case 'endDate':
                            // skip if already processed
                            if (! $foundDate) {
                                $quals[] = $this->processEndDate($value);
                            }
                            break;
                        default:
                            $quals[] = new BLKeyValueQualifier($key, OP_CONTAINS, '%'.$value.'%');
                            break;
                    }
                }
            
                $qual = empty($quals) ? null : new BLAndQualifier($quals);
                $this->displayGroup = new PLDisplayGroup('Recipe', $qual, $this->getSortBy($this->default_sort_key), $this->resPerPage(), $this, 'pageNo');
            }
            return $this->displayGroup;
        }
    
        public function printFilters() 
        {
            foreach ($this->headers as $header => $keyPath) 
            {
                print '<td>';
                switch($header) 
                {
                    case('id'): 
                        $this->printFilterInput($keyPath, 'small');
                        break;
                    case('Date'):
                        $this->printDateFilter();
                        break;
                    case("options"):
                        addSubmitButtonWithActions('Go', array(), 'filter');
                    default:
                        if (! (is_numeric($keyPath) && (strpos($keyPath, '.') === FALSE)))
                            $this->printFilterInput($keyPath);
                        break;
                }
                print '</td>';
            }
        }
    
        protected function printFilterInput($keyPath, $class = null) 
        {
            print '<input class="filter '.$class.'" type="text" name="filter|'.$keyPath.'" id="filter|'.$keyPath.'" value="'.$this->formValueForKey('filter|'.$keyPath).'"/>';
        }

        public function printField($header, $value, $id, $entity) 
        {
            // See if controller has method to print field
             switch ($header) 
             {
                 case('options'):
                    addLinkWithParams("Edit", "RecipeEdit/$id", array());
                    break;
                 default:
                    if (is_numeric($value)) {
                        print number_format(floatval($value));
                    } 
                    else if ($value) {
                        print $value;
                    } 
                    else {
                        print "N/A";
                    }
                    break;
             }
        }
		


		public function likeRecipeToggle(){
			debugln("goat here : " . doDecrypt($this->formValueForKey("likeRecipeID")));
			//fixme
			//$this->user()->likeRecipeToggle(doDecrypt($this->formValueForKey("likeRecipeID")));
		}


		public function likesImageString($recipe){
			debugln("doing likesImageString string");
			
			//FIXME
			/*
			if($this->user()->likesRecipe($recipe->vars["id"])){
				return "images/favouriteOn.png";
			}
			else{
				return "images/favouriteOff.png";
			}*/
			
		}
		
		public function createRecipe(){
			//debugln("createRecipe Action");
			//$newRecipe = BLGenericRecord::newRecordOfType("Recipe");
			
			$page = $this->pageWithName("RecipeEdit");
			//$page->nextPage = $this;
			return $page;
		}
		
// 		public function editRecipe($recipeID){
// 			$page = $this->pageWithName("RecipeEdit");
// 			$page->id = $recipeID;
// // 			$page = $this->pageWithName("About");
			
			
// // 			$page->anotherVar = "heyNow";
// // 			//this diverts to the page without having to return here
// // 			$page->goToLocation(null, array("Larry", "balls"));


		public function searchRecipes(){
			$searchString = $this->formValueForKey("searchString");
			
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
			removeSessionValueForKey("test"); // clear the test var.
			//debugln("validateLogin: found user222");
			 
			$result = array("error" => 0);
			//debugln("the result is: " .  print_r($result));
			echo json_encode($result);
			}

    
	}

	?>	
