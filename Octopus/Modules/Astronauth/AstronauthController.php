<?php
namespace Octopus\Modules\Astronauth;
use Exception;
use Octopus\Modules\Astronauth\Login;
use Octopus\Modules\Astronauth\Account;
use Astronauth\Exceptions\EmptyResultException;
use Octopus\Core\Controller\Router\ControllerCall;
use Octopus\Modules\Astronauth\AstronauthException;
use Octopus\Core\Controller\Exceptions\ControllerException;
use Octopus\Core\Controller\Controllers\AuthenticationController;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Octopus\Core\Model\Attributes\Exceptions\MissingValueException;

class AstronauthController extends AuthenticationController {
	protected string $action; # manual | accompany | login | logout | register
	protected ?Login $login;
	protected bool $silent;
	protected ?AstronauthException $exception;
	protected ?AttributeValueExceptionList $faults;
	private bool $locked;


	public function load() : void { // TODO add manual
		$this->action = $this->call->get_option('action') ?? 'accompany';

		if(in_array($this->action, ['login', 'logout', 'register', 'manual', 'accompany'])){
			throw new ControllerException(500, 'Route: Invalid action.');
		}

		$this->silent = $this->call->get_option('silent') ?? true;
		$this->exception = null;
		$this->faults = null;
		$this->locked = false;
	}


	public function execute() : void {
		if($this->action === 'manual'){
			return;
		}

		try {
			if(isset($_SESSION['loginID'])){
				$this->resume($_SESSION['loginID']);
			} else if($this->request->has_cookie('loginID') || $this->request->has_cookie('loginKey')){
				$this->remember($this->request->get_cookie('loginID') ?? '', $this->request->get_cookie('loginKey') ?? '');
			}
			
			if($this->request->method_is('POST')){
				if($this->action === 'login'){
					$this->login(combined:$this->request->get_post_data());
				} else if($this->action === 'logout'){
					$this->logout(silent:false);
				} else if($this->action === 'register'){
					$this->register(combined:$this->request->get_post_data());
				}
			}

			$this->set_status_code(200);
		} catch(AstronauthException $e){
			$this->set_status_code(500);

			if($this->silent){
				$this->exception = $e;
			} else {
				throw $e;
			}
		}
	}


	public function finish() : void {
		$this->lock();
	}


	private function resume(string $id) : void {
		$this->require_unlocked();

		$this->login = $this->new_login();

		try {
			$this->login->pull($id);
		} catch(EmptyResultException $e){
			$this->logout(silent:true);
			throw new AstronauthException('RS-UKID', 'The autologin id is not known.');
		}

		if($this->login->is_revoked()){
			$this->logout(silent:true);
			throw new AstronauthException('RS-REVK', 'The autologin credentials have been revoked.');
		}

		if($this->login->is_expired()){
			$this->logout(silent:true);
			throw new AstronauthException('RS-EXPD', 'The autologin credentials have expired.');
		}

		$this->refresh_login(rekey:false);
	}


	public function remember(string $id = '', string $key = '') : void {
		$this->require_unlocked();

		$this->login = $this->new_login();

		try {
			$this->login->pull($id);
		} catch(EmptyResultException $e){
			$this->logout(silent:true);
			throw new AstronauthException('RM-UKID', 'The autologin id is not known.');
		}

		if($this->login->is_revoked()){
			$this->logout(silent:true);
			throw new AstronauthException('RM-REVK', 'The autologin credentials have been revoked.');
		}

		if($this->login->is_expired()){
			$this->logout(silent:true);
			throw new AstronauthException('RM-EXPD', 'The autologin credentials have expired.');
		}

		if(!$this->login->verify($key)){
			$this->logout(silent:true);
			throw new AstronauthException('RM-IKEY', 'The autologin key is invalid.');
		}

		$this->refresh_login(rekey:true);
	}


	private function refresh_login(bool $rekey) : void {
		if($this->login->is_persistent() && $rekey){
			$key = $this->login->generate_key();
	
			$this->set_cookie('loginID', $this->login->id, 1000); // TODO
			$this->set_cookie('loginKey', $key, 1000);
		}

		$this->login->push();

		$_SESSION['loginID'] = $this->login->id;
	}


	public function login(string $username = null, string $password = null, ?bool $remember = null, array $combined = []) : void {
		$this->require_unlocked();

		if(empty($username)){
			$username = $combined['username'] ?? '';
		}

		if(empty($password)){
			$password = $combined['password'] ?? '';
		}

		if(!isset($remember)){
			$remember = (bool) $combined['remember'] ?? false;
		}

		if($this->is_logged_in()){
			throw new AstronauthException('LI-ARLI', 'The user is already logged in.');
		}

		$account = $this->new_account();

		try {
			$account->pull($username);
		} catch(EmptyResultException $e){
			throw new AstronauthException('LI-UKUN', 'The username is not known.');
		}

		if(!$account->verify_password($password)){
			throw new AstronauthException('LI-PASS', 'The password is wrong.');
		}

		$this->login = $this->new_login();
		$this->login->initialize($account, $remember ? 'persistent' : 'session');

		$this->refresh_login();
	}


	public function logout(bool $silent = false) : void {
		$this->require_unlocked();

		if(!$this->is_logged_in() && !$silent){
			throw new AstronauthException('LO-ARLO', 'The user is already logged out.');
		}

		$this->login = null;
		$this->delete_cookie('loginID');
		$this->delete_cookie('loginKey');
		unset($_SESSION['loginID']);
	}


	public function register() : void {
		$this->require_unlocked();

		$account = $this->new_account();

		try {
			$account->create();
			$account->receive_input($this->request->get_post_data());
			$account->push();
		} catch(AttributeValueExceptionList $e){
			$this->faults = $e;
			throw new AstronauthException('RE-DATA', 'There have been faults in the user data.');
		}
	}


	public function is_logged_in() : bool {
		return isset($this->login);
	}


	public function get_account() : ?Account {
		return $this->login?->account;
	}


	public function get_exception() : ?AstronauthException {
		return $this->exception;
	}


	public function get_faults() : ?AttributeValueExceptionList {
		return $this->faults;
	}


	private function new_account() : Account {
		return new Account(null, $this->endpoint->get_db());
	}


	private function new_login() : Login {
		return new Login(null, $this->endpoint->get_db());
	}


	private function lock() : void {
		$this->locked = true;
	}


	protected function require_unlocked() : void {
		if($this->locked){
			throw new Exception('This operation can only be performed during execution phase.');
		}
	}
}
?>