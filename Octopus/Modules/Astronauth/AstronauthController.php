<?php
namespace Octopus\Modules\Astronauth;
use Exception;
use Octopus\Core\Controller\Request;
use Octopus\Modules\Astronauth\Account;
use Octopus\Modules\Astronauth\Login;
use Astronauth\Exceptions\EmptyResultException;
use Octopus\Core\Controller\Router\ControllerCall;
use Octopus\Core\Controller\Exceptions\ControllerException;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use Octopus\Core\Controller\Controllers\AuthenticationController;

class AstronauthController extends AuthenticationController {
	protected string $action; # accompany | login | logout
	protected ?Login $login;


	public function load(Request $request, ControllerCall $call) : void {
		$this->action = $call->get_option('action') ?? 'accompany';

		if($call->get_importance() === 'primary'){
			if(!in_array($this->action, ['login', 'logout', 'register'])){
				throw new ControllerException(500, 'Route: Invalid action.');
			}
		} else if($this->action !== 'accompany'){
			throw new ControllerException(500, 'Route: Invalid action («accompany» required).');
		}
	}


	public function execute(Request $request) : void {
		if(isset($_SESSION['loginID'])){
			$this->load_login($_SESSION['loginID']);
		} else if($this->request->has_cookie('loginID') && $this->request->has_cookie('loginKey')){
			$this->load_login($this->request->get_cookie('loginID'));

			$this->login->verify($this->request->get_cookie('loginKey'));
			$this->refresh_login();
		} else {
			$this->logout();
		}

		if($this->request->method_is('POST')){
			if($this->action === 'login'){
				$this->login(combined:$this->request->get_post_data());
			} else if($this->action === 'logout'){
				$this->logout();
			} else if($this->action === 'register'){
				$this->register();
			}
		}
	}


	public function finish() : void {

	}


	private function load_login(string $id) : void {
		$this->login = $this->new_login();

		try {
			$this->login->pull($id);
		} catch(EmptyResultException $e){
			throw new Exception('login not found'); // TODO
		}

		if(!$this->login->is_valid()){
			throw new Exception('login invalid'); // TODO
		}
	}


	private function refresh_login() : void {
		if(!$this->login->is_valid()){
			throw new Exception('invalid logins cannot be refreshed');
		}

		if($this->login->is_persistent()){
			$key = $this->login->generate_key();
	
			$this->response->set_cookie('loginID', $this->login->id); // TODO
			$this->response->set_cookie('loginKey', $key);
		}

		$this->login->push();

		$_SESSION['loginID'] = $this->login->id;
	}


	public function login(?string $username = null, ?string $password = null, ?bool $remember = null, array $combined = []) : void {
		if(!isset($username)){
			$username = $combined['username'] ?? null;
		}

		if(!isset($password)){
			$password = $combined['password'] ?? null;
		}

		if(!isset($remember)){
			$remember = $combined['remember'] ?? false;
		}

		if($this->is_logged_in()){
			throw new Exception('already logged in');
		}

		$account = $this->new_account();

		try {
			$account->pull($username);
		} catch(EmptyResultException $e){
			throw new Exception('unknown account'); // differentiating between unknown account and wrong password is a security risk
		}

		if(!$account->verify_password($password)){
			throw new Exception('wrong password');
		}

		$this->login = $this->new_login();
		$this->login->initialize($account, $remember ? 'persistent' : 'session');

		$this->refresh_login();
	}


	public function logout() : void {
		$this->login = null;
		$this->response->delete_cookie('loginID');
		$this->response->delete_cookie('loginKey');
	}


	public function register() : void {
		$account = $this->new_account();

		$account->create();
		$account->receive_input($this->request->get_post_data());
		$account->push();
	}


	public function is_logged_in() : bool {
		return isset($this->login);
	}


	private function new_account() : Account {
		return new Account(null, $this->endpoint->get_db());
	}


	private function new_login() : Login {
		return new Login(null, $this->endpoint->get_db());
	}
}
?>