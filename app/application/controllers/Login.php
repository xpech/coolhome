<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Login extends CoolHomeController {
	var $acl = false;
	var $show_navbar = false;
	var $show_menus = false;
	var $show_footer = false;
	
	var $salt = 'popodqdsdsqdqdl,sqdl,';

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		if ($this->input->post()) {
			$sec = $this->input->post('secret');
			$pass = $this->input->post('p_' . $sec);
			$login = $this->input->post('l_' . $sec);
			if ($u = CUser::getUser($login,$pass)) {
				CDebug::log('Store user into session');
				$this->session->user = $u;
				if (CHtml::val('backto')) $this->redirect(CHtml::val('backto'));
				else $this->redirect("/");
				return;
			}
		}
		$this->view('login/index',['secret' => 'F' . dechex(time())]);
	}
	
	public function recover()
	{
		$this->view('login/recover');
	}
	private function emailKey($email)
	{
		return md5($email.$this->salt);
	}
	
	public function create()
	{
		if (CHtml::action('SendCreateMail'))
		{
			$email = CHtml::val('email');
			if (filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				if (substr($email,0,3) == CHtml::val('securebot'))
				{
					$conf = CConfig::load('mail');
					$mail = new PHPMailer();
					// $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
					CConfig::setupMail($mail);
					$mail->addAddress($email);
					$mail->Subject = '[CoolHome] Lien de création de compte';
    				$mail->Body = sprintf("Bonjour,\n veuillez cliquer sur le lien suivant pour créer votre compte: \nhttp://coolhome.ovh/login/setpassword/%s/%s \nL'équipe de CoolHome",
    						urlencode($email),
    						$this->emailKey($email)
    						);
    				if ($mail->send())
    					$this->message("Vous allez recevoir un mail de création sur l'adresse fournie");
    				else 
    					$this->error("Une erreur s‘est produite, veuillez vérifier votre email ?");

				} else $this->error('Êtes vous un robot ?');
			} else $this->error('Mail non conforme');
		}
		$this->view('login/create');
	}

	function setPassword($email,$key)
	{
		$email = urldecode($email);
		if($this->emailKey($email) == $key )
		{

			if (CHtml::action("SendUpdatePwd"))
			{
				$u = CUser::withEmail($email);
				if (!$u)
				{
					$u = new CUser();
					$u->email = $email;
					if (!$u->create()) $u = null;
				}
				if ($u)
				{
					$u->setPassword(CHtml::val('pwd1'));
					$this->session->user = $u;
					$this->redirect('/');
					return;
				} else { 
						$this->error("Erreur de création"); 
				}

			}
			$this->view('login/setpwd',['email' => $email, 'key' => $key]);
		}

	}
	function out()
	{
		$this->session->user = null;
		$this->redirect();
	}
}
