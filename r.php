<?php

	/* Authur : Vignesh Shanmugasundaram */
	/* Last Modified : 2/Sep/2015 16:59:00 */

	include '../routes.php';
	include '../modal/Modal.php';
	include '../Controllers/Controller.php';
	include 'Session.php';
	include 'Time.php';
	include 'Views.php';

	$routeto = $_GET['routeto'];
	$pieces = explode("/", $routeto);

	try{

		//If a rewrite rule is define, go for it
		if(isset($route[$routeto])){
			$control = explode("=>", $route[$routeto]);
			$controller = ucfirst($control[0]);
			$pieces[0] = $control[0];
			$pieces[1] = $control[1];
		}

		//If rewrite rule is not defined, and URL pattern is not recoganizable, look for app root
		else if(!isset($pieces[1])){

			//If app root is defined go for it
			if(isset($app_root)){
				$control = explode("=>", $app_root);
				if(count($control) > 1){
					$controller = ucfirst($control[0]);
					$pieces[0] = $control[0];
					$pieces[1] = $control[1];	
				}else{

					//Render the app_root file
					include $app_root;
					exit;
				}
			}

			//If not app route render index.php
			else include '../404.php';
		}
		//If URL is recogonizable, execute the controller
		else $controller = ucfirst($pieces[0]);

		//Find if the controller is present
		if(is_file("../Controllers/".$controller.".php"))
			include "../Controllers/".$controller.".php";
		else throw new Exception("Controller not found");

		//Create a controller instance
		$render = new $controller;
		if(isset($pieces[1]) && $pieces[1] != "")

			//Check if the method exists
			if(method_exists($render, $pieces[1]))
				$render->$pieces[1]();

		//If method is not found, render  by request method
		else{
			$pieces[1] == strtolower($_SERVER['REQUEST_METHOD']);

			//GET handler
			if($_SERVER['REQUEST_METHOD'] == "GET")
				$render->get();

			//POST handler
			elseif($_SERVER['REQUEST_METHOD'] == "POST")
				$render->post();

			//DELETE handler
			elseif($_SERVER['REQUEST_METHOD'] == "DELETE"){
				$render->delete();
			}
		}

		$pieces[0] = ucfirst($pieces[0]);
		if(!$render->json && !$render->getDefaultAction()){

			// $application for whole application content in application _layout
			// $content for controller _layout

			// Find if application layout is present
			if(is_file('../views/_layout.php')){

				//Find if controller layout is present
				if(is_file('../views/'.$pieces[0]."/_layout.php")){

					// If controller layout is present add it to $application
					$application = '../views/'.$pieces[0]."/_layout.php";

					//Find relevent view
					if(is_file("../views/".$pieces[0]."/".$pieces[1].".php"))

						// Add relevent view as $content
						$content =  "../views/".$pieces[0]."/".$pieces[1].".php";

					// Add default handler
					elseif(is_file("../views/".$pieces[0]."/request.php"))
						$content = "../views/".$pieces[0]."/request.php";

					//Else throw an error
					else throw new Exception("No view found for '".$pieces[1]."'");
				}

				//If not application layout found, add view as application content
				else
					$application = "../views/".$pieces[0]."/".$pieces[1];
				include 'views/_layout.php';
			}

			//If not, find Controller layout
			elseif(is_file('../views/'.$pieces[0]."/_layout.php")){

				//Find for relevent view
				if(is_file("../views/".$pieces[0]."/".$pieces[1].".php"))

					//If relevent  view fonund add view as content
					$content =  "../views/".$pieces[0]."/".$pieces[1].".php";

				//If not found add default request handler
				elseif(is_file("../views/".$pieces[0]."/request.php"))
						$content = "../views/".$pieces[0]."/request.php";

				//Else throw an error
				else throw new Exception("No view found for '".$pieces[1]."'");
				include '../views/'.$pieces[0]."/_layout.php";
			}

			//Else render only the view
			else{

				//Find for view 
				if(is_file("../views/".$pieces[0]."/".$pieces[1].".php"))

					//Add view as content
					$content =  "../views/".$pieces[0]."/".$pieces[1].".php";
				elseif(is_file("../views/".$pieces[0]."/request.php"))

					//Add default request handler if no view found
					$content = "../views/".$pieces[0]."/request.php";

				//Else throw an error
				else throw new Exception("No view found for '".$pieces[1]."'");
				include $content;
			}
		}elseif($render->getToRender()){
			include '../views/'.$render->getToRender();
		}
	}catch(Exception $e){
		echo "View error: <b>".$e->getMessage()."</b>";
	}
	
?>
