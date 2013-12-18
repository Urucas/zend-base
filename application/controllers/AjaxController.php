<?php

class AjaxController extends Zend_Controller_Action 
{

    public function init()
    {
    }

    public function indexAction()
    {
        // action body
    }

	public function uploadprofileimageAction(){

		$valid_formats = array("jpg", "png", "gif", "bmp","jpeg","JPG");
		if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
		{
			$name = $_FILES['photoimg']['name'];
			$size = $_FILES['photoimg']['size'];
			if(strlen($name))
			{
				list($txt, $ext) = explode(".", $name);
				if(in_array($ext,$valid_formats))
				{
					if($size<(1024*1024)) // Image size max 1 MB
					{
						$actual_image_name = "avatar.jpg";
						$tmp = $_FILES['photoimg']['tmp_name'];

						$sesion = new Zend_Session_Namespace("usuario");
						$id = $sesion->user_id;
						//move_uploaded_file($tmp,"resources/avatars/".$id."/".$_FILES["file"]["name"]);

						if (move_uploaded_file($tmp,"resources/avatars/".$id."/".$actual_image_name))
						{
							//la redimensiono
							//$url_imagen = "resources/avatars/".$id."/".$_FILES["file"]["name"];
							//$nueva = $this->redimensionar_imagen($url_imagen,"resources/avatars/".$id."/",500);

							$table = new Zend_Db_Table("usuarios");
							$table->update(array("fb_pic"=>"0"),$id);
							//mysql_query("UPDATE users SET profile_image='$actual_image_name' WHERE uid='$session_id'");
							die( json_encode(array("html"=>"<img src='/user/avatar/id/".$id."/?r=".rand(8)."' class='avatar' onmouseover=\"$('#change-img').show()\" onmouseout=\"$('#change-img').hide()\">")));
						}
						else
							die( json_encode(array("error"=>"Falló la subida de la imagen, prueba de nuevo")));
					}
					else
						die(json_encode(array("error"=>"La imagen es muy grande, tiene que ser menor a 1 MB"))); 
				}
				else
					die (json_encode(array("error"=>"Formato de imagen invalido.."))); 
			}
			else
				die(json_encode(array("error"=>"Selecciona una foto..!")));
			exit;
		}

	}
}
