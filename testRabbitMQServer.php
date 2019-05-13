#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

//Login Function//**************************************************************
function doLogin($username,$password)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);

  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM users WHERE username = '$username' AND password = '$password' ");
  $count = mysqli_num_rows($query);

  //Check if credentials match the database
  if ($count == 1)
  { echo "USERS CREDENTIALS VERIFIED"; return true; }
  else { echo "User Credential did not match!!!!"; return false; }

  if ($mydb->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
    exit(0);
  }
}

//Register Function//***********************************************************
function doregister($firstname,$lastname,$username,$password)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);

  if ($mydb->errno != 0)
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM users WHERE username = '$username'");
  $count = mysqli_num_rows($query);

 //Check if credentials match the database
  if ($count > 0)
  { echo "<br><br>Please register with differernt username"; return false; }
  else 
  { 
    $query = mysqli_query($mydb,"INSERT INTO users (firstname, lastname, username, password, BMI, gender, height, weight) VALUES ('$firstname','$lastname','$username','$password', ' ', ' ', ' ', ' ')");
    echo "Register Successful!!!!";
    return true;
  }

  if ($mydb->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
    exit(0);
  }
}

//Success Page//**************************************************************
function doSuccess($username)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);
  
  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM users WHERE username = '$username'");
  $count = mysqli_num_rows($query);

  if ($count == 1)
  { 
      echo "USERS CREDENTIALS VERIFIED"; 
      while($r = mysqli_fetch_array ($query))
      {
	$username = $r["username"];
	$firstname = $r["firstname"];
	$lastname = $r["lastname"];
        $BMI = $r["BMI"];
      }
    
    return $firstname." ".$lastname."<br>"."Your BMI is $BMI"; 
   }
  else { echo "User Credential did not match!!!!"; return false; }
}

//fetch Api Data Function//*****************************************************
function fetchItem($item)
{
   $curl = curl_init();
   curl_setopt_array($curl, array(
   			CURLOPT_URL => "https://api.nutritionix.com/v1_1/search/'.$item.'?results=0:20&fields=item_name,item_id,nf_calories,nf_protein,nf_sugars,nf_sodium&appId=93504f94&appKey=7df6de5740084c54cf4fb20d3dd81b4d",
  		
   			CURLOPT_RETURNTRANSFER => true,
   			CURLOPT_ENCODING => "",
  			CURLOPT_MAXREDIRS => 10,
   			CURLOPT_TIMEOUT => 30,
  			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	                CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array("Postman-Token: eb158d0e-a543-4895-8d47-e4e0f967be96", 
							"cache-control: no-cache"),));

   $response = curl_exec($curl);
   $err = curl_error($curl);

   curl_close($curl);

   if($err) { echo "CURL ERROR #:".$err; }
   else{
          echo $response;
          $result = json_decode($response, true);
	  $hits = $result['hits'];
	  $item_info = $hits['0'];

          $_SESSION['test'] = $item_info;

	  print_r($item_info);
	  return $item_info;	
       }
}

//Save Favorite Food function//*************************************************
function dofavorite($username,$foodname,$calories)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);

  if ($mydb->errno != 0)
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "<br>successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM fav WHERE username = '$username' AND foodname = '$foodname'");
  $count = mysqli_num_rows($query);

  if ($count >=1)
  { 
    $foodexist="<br><br>This food is already in your favorite list!!!"; 
    echo $foodexist; 
    return $foodexist;
  }
  else 
  { 
    $query = mysqli_query($mydb,"INSERT INTO fav (username, foodname, calories) VALUES ('$username','$foodname','$calories')");
    $added ="Item added to your favorite list";
    return $added;
  }

  if ($mydb->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
    exit(0);
  }

}

//Delete Favorite food Function//***********************************************
function dodelete($username, $foodname)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);
  
  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }
  $query = mysqli_query($mydb,"DELETE FROM fav WHERE username = '$username' AND foodname = '$foodname' LIMIT 1");
}

//Display Favorite Food Function//**********************************************
function dodisplayfav($username)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);
  
  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }
  $query = mysqli_query($mydb,"SELECT * FROM fav WHERE username = '$username'");
  $count = mysqli_num_rows($query);
  $showFav = "";
  if ($count >= 1)
  { 
      echo "USERS CREDENTIALS VERIFIED"; 
     $showFav .= "<br> <table style= 'text-align:center;' border=5 width=45% bgcolor='white'>";
     $showFav .= "<tr>";
     $showFav .= "<td><b>Foodname</b></td>";
     $showFav .= "<td><b>Calories</b></td>";
     $showFav .= "<td><b>Add To<br>Meal Plan</b></td>";
     $showFav .= "<td><b>Delete Item</b></td>";
     $showFav .= "</tr>";
      while($r = mysqli_fetch_array ($query))
      {
	$foodname = $r["foodname"];
	$calories = $r["calories"];
	$showFav .= "<tr>";
 	$showFav .= "<td>$foodname</td>";
        $showFav .= "<td>$calories</td>";
        $showFav .= "<td><form action = 'addFavtoDay.php'>
	<input type='hidden' name='food' value='$foodname'>
	<input type='hidden' name='calories' value='$calories'>
			 <select name = 'day' id = 'option'>
      			 <option value = '' > Select Day         </option>
     			 <option value = 'Monday' > Monday       </option>
     			 <option value = 'Tuesday' > Tuesday     </option> 
     			 <option value = 'Wednesday' > Wednesday </option>
    			 <option value = 'Thursday' > Thursday   </option>
    			 <option value = 'Friday' > Friday       </option>
     			 <option value = 'Saturday' > Saturday   </option>
      			 <option value = 'Sunday' > Sunday       </option>
			 <input type='submit' value='Add'></form></td>";

        //$showFav .= "<td><a href='addFavtoDay.php?id=".$r['foodname']."'>Add</a></td>";
        $showFav .= "<td><a href='deleteitem.php?id=".$r['foodname']."'>Delete</a></td>";
        $showFav .= "</tr>";
      }
    
    return $showFav; 
   }
  else { echo "can not display saved list!!!!"; return false; }
}

//Calculate BMI Function//******************************************************
function doBMI($username, $BMI, $gender, $height, $weight)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);

  if ($mydb->errno != 0)
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else {echo "You are on Save BMI Fucntion ".PHP_EOL; 
        echo "successfully connected to database. ".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM users WHERE username = '$username'");
  $count = mysqli_num_rows($query);

  if ($count ==1)
  {
    $query = mysqli_query($mydb,"update users set BMI='$BMI', gender='$gender', height='$height', weight='$weight' where username='$username'");
    $save = "Your BMI saved!!!";
    return $save; 
  }
  else 
  { echo "<br><br>Can not save your BMI score"; return false; }

  if ($mydb->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
    exit(0);
  }
}

//User Profile Function//*******************************************************
function douserProfile($username)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);
  
  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM users WHERE username = '$username'");
  $count = mysqli_num_rows($query);
  $info = "";
 
  if ($count >= 1)
  { 
      echo "USERS CREDENTIALS VERIFIED"; 
     $info .= "<br> <table style= 'text-align:center;' border=5 width = 35% bgcolor='white'>";
     $info .= "<tr>";
     $info .= "<td>First Name</td>";
     $info .= "<td>Last Name</td>";
     $info .= "<td>Gender</td>";
     $info .= "<td>Height<br>(inch)</td>";
     $info .= "<td>Weight<br>(pound)</td>";
     $info .= "<td>BMI</td>";
     $info .= "</tr>";
      while($r = mysqli_fetch_array ($query))
      {
	$firstname = $r["firstname"];
	$lastname = $r["lastname"];
	$BMI = $r["BMI"];
	$gender = $r["gender"];
	$height = $r["height"];
	$weight = $r["weight"];

	$info .= "<tr>";
 	$info .= "<td>$firstname</td>";
        $info .= "<td>$lastname</td>";
 	$info .= "<td>$gender</td>";
        $info .= "<td>$height</td>";
 	$info .= "<td>$weight</td>";
        $info .= "<td>$BMI</td>";
        $info .= "<tr>";
      }
    
    return $info; 
   }
  else { echo "can not display your profile page!!!!"; return false; }
}

//Meal Planner function//******************************************************
function domeal($username,$foodname,$day,$calories)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);

  if ($mydb->errno != 0)
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "<br>successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM mp WHERE username = '$username' AND foodname = '$foodname' AND day = '$day'");
  $count = mysqli_num_rows($query);
  $totalCalories = 0;
  
  $query1 = mysqli_query($mydb,"SELECT * FROM mp WHERE username = '$username' AND day = '$day'");
  while($r = mysqli_fetch_array ($query1))
      {
        $calorie = $r["calories"];
        $totalCalories += $calorie;
        echo "$totalCalories";
      }
  
  if ($count >= 1)
  { 
    $foodexist=" is already in your meal planner list!!!"; 
    echo $foodexist; 
    return $foodexist;
  }
  else 
  {  
     $totalCalories = $totalCalories + $calories;
     if($totalCalories <=2000)
     {
       $query = mysqli_query($mydb,"INSERT INTO mp (username, foodname, day, calories) VALUES ('$username','$foodname','$day','$calories')");
       $added ="is added to your meal planner list";
       return $added;
     }
     else 
     { 
       $totalCalories = 2000 - ($totalCalories - $calories); 
       return "$foodname can not be add because it has $calories calories. \\nYou have only $totalCalories calories remain to add in for the day.";
     }
   }

  if ($mydb->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
    exit(0);
  }
}

//Show Meal Plan Function//*****************************************************
function doShowMealPlan($username, $day)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);
  
  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM mp WHERE username = '$username' and day = '$day'");
  $count = mysqli_num_rows($query);
  $showMealPlan = "";
  $totalCalories = 0;
  
  if ($count >= 1)
  { 
      echo "USERS CREDENTIALS VERIFIED"; 
     $showMealPlan .= "<br> <table style= 'text-align:center;' border=5 width=30% bgcolor='white'>";
     $showMealPlan .= "<tr>";
     $showMealPlan .= "<td>Foodname</td>";
     $showMealPlan .= "<td>Calories</td>";
     $showMealPlan .= "<td>Delete Item</td>";
     $showMealPlan .= "</tr>";
      while($r = mysqli_fetch_array ($query))
      {
	$foodname = $r["foodname"];
        $calories = $r["calories"];
	$showMealPlan .= "<tr>";
 	$showMealPlan .= "<td>$foodname</td>";
        $showMealPlan .= "<td>$calories</td>";
        $showMealPlan .= "<td><a href='deletemeal.php?id=".$r['foodname']."'>Delete</a></td>";
        $showMealPlan .= "</tr>";
        $totalCalories += $calories;
      }
     $showMealPlan .= "<tr>";
     $showMealPlan .=  "<td><b>Total Calories</td>";
     $showMealPlan .=  "<td><b>$totalCalories</td>";
     $showMealPlan .= "</tr>";
     return $showMealPlan; 
   }
  else { echo "can not display meal plan list!!!!"; return false; }
}

//Delete meal Plan Function//******************************************************
function dodeletemeal($username, $foodname, $day)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);
  
  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }
  $query = mysqli_query($mydb,"DELETE FROM mp WHERE username = '$username' AND foodname = '$foodname' and day = '$day'");
}
 
//Call suggest function//**********************************************************
function dosuggest($username, $day)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);
  
  if ($mydb->errno != 0) 
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "successfully connected to database".PHP_EOL; }
  
  //Counting Total Calories
  $query1 = mysqli_query($mydb,"SELECT * FROM mp WHERE username = '$username' AND day = '$day'");
  $totalCalories = 0;
  while($r = mysqli_fetch_array ($query1))
  {
     $calorie = $r["calories"];
     $totalCalories += $calorie;
  }
  $totalCalories = 2000 - $totalCalories;

  //Getting Suggession for user  
  $query = mysqli_query($mydb,"SELECT foodname, serving, calories from suggest where calories<=$totalCalories ORDER BY RAND() limit 5");
  
  $addSuggest = "";
  $addSuggest .= "<br> <table style= 'text-align:center;' border=5 width=30% bgcolor='white'>";
  $addSuggest .= "<tr>";
  $addSuggest .= "<td>Foodname</td>";
  $addSuggest .= "<td>Calories</td>";
  $addSuggest .= "<td>Serving</td>";
  $addSuggest .= "<td>Add to<br>Meal Plan</td>";
  $addSuggest .= "</tr>";
  while($r = mysqli_fetch_array ($query))
  {
    $foodname = $r["foodname"];
    $calories = $r["calories"];
    $serving = $r["serving"];
    $addSuggest .= "<tr>";
    $addSuggest .= "<td>$foodname</td>";
    $addSuggest .= "<td>$calories</td>";
    $addSuggest .= "<td>$serving</td>";
    $addSuggest .= "<td><a href='addSuggestFood.php?id=".$r['foodname']."'>Add</a></td>";
    $addSuggest .= "</tr>";
  }
    
  return $addSuggest; 
}

//Add suggested food to meal plan
function doAddSuggest($username,$foodname,$day)
{
  include('account.php');
  $mydb = new mysqli($dbserver,$dbuser,$dbpass,$dbname);

  if ($mydb->errno != 0)
  { echo "failed to connect to database: ". $mydb->error . PHP_EOL; exit(0); }
  else { echo "<br>successfully connected to database".PHP_EOL; }

  $query = mysqli_query($mydb,"SELECT * FROM suggest WHERE foodname = '$foodname'");
  $query1 = mysqli_query($mydb,"SELECT * FROM mp WHERE username = '$username' AND foodname = '$foodname' AND day = '$day'");
  $count = mysqli_num_rows($query1);
 
  while($r = mysqli_fetch_array ($query))
  { $calories = $r["calories"]; }
  
  if ($count == 0)
  {
       $query = mysqli_query($mydb,"INSERT INTO mp (username, foodname, day, calories) VALUES ('$username','$foodname','$day','$calories')");
       $added ="is added to your meal planner list";
       return $added;
  }
  else { return "is already in your daily plan list"; }

  if ($mydb->errno != 0)
  {
    echo "failed to execute query:".PHP_EOL;
    echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
    exit(0);
  }
}

//Call Function as requested//*****************************************************
function requestProcessor($request)
{
  echo ", received request".PHP_EOL;
  var_dump($request);

  if(!isset($request['type'])) { return "ERROR: unsupported message type"; }

  switch ($request['type'])
  {
    case "login":
         return doLogin($request['username'],$request['password']);
	 break;
    case "register":
         return doregister($request ['firstname'],$request['lastname'],$request['username'],$request['password']);
     	 break;
    case "success1":
         return doSuccess($request['username']);
	 break;
    case "success2":
         return doBMI($request['username'], $request['BMI'],$request['gender'], $request['height'],$request['weight']);
	 break;
    case "search";
	 return fetchItem($request['item']);
	 break;
    case "favorite":
         return dofavorite($request ['username'],$request['foodname'],$request['calories']);
     	 break;
    case "meal":
         return domeal($request ['username'],$request['foodname'],$request['day'], $request['calories']);
     	 break;
    case "delete":
         return dodelete($request['username'], $request['foodname']);
	 break;
    case "deletemeal":
         return dodeletemeal($request['username'], $request['foodname'],$request['day']);
	 break;
    case "displayfav":
         return dodisplayfav($request['username']);
	 break;
    case "showMealPlan":
         return doShowMealPlan($request['username'], $request['day']);
         break;
    case "userProfile":
         return douserProfile($request['username']);
	 break;
    case "suggest":
         return dosuggest($request['username'], $request['day']);
	 break;
    case "addSuggest":
         return doAddSuggest($request['username'], $request['foodname'], $request['day']);
	 break;
    case "validate_session":
         return doValidate($request['sessionId']);
	 break;
  }

  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");
echo "rabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "rabbitMQServer END".PHP_EOL;
exit();



?>
