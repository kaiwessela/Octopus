<?php
namespace Octopus\Modules\Web;
use Exception;
use Octopus\Core\Controller\Environment;
use Octopus\Core\Controller\Request;
use Octopus\Core\Controller\Response;
use Octopus\Core\Controller\Routine;
use Octopus\Core\Model\Database\DatabaseAccess;
use Startschreiber\Server\Authentication\Authenticator;
use Startschreiber\Server\BoxInteraction\BoxMessage;
use Startschreiber\Server\Config;
use Startschreiber\Server\Server\CookieManager;
use Startschreiber\Server\Server\SessionManager;

final class WebEnvironment implements Environment {
	private Config $config;
	private Request $request;
	private Response $response;
	private SessionManager $session_manager;
	private CookieManager $cookie_manager;
	private DatabaseAccess $db;
	private Authenticator $authenticator;
	private array $routines;
	private array $running_routines;


	function __construct() {
		$this->config = new Config(require __DIR__ . '/../../../../config/config.php');

		if($this->config->get('debug_mode', true) === true){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		} else {
			error_reporting(0);
			ini_set('display_errors', 0);
		}

		$this->request = new Request();
		$this->response = new Response();

		$this->session_manager = new SessionManager();
		$this->cookie_manager = new CookieManager();

		$this->db = new DatabaseAccess(
			$this->config->get('database.host'),
			$this->config->get('database.dbname'),
			$this->config->get('database.dbuser'),
			$this->config->get('database.password')
		);

		$this->authenticator = new Authenticator($this->db);

		$this->routines = [];
		$this->running_routines = [];
	}


	final public function run(Routine &$routine, ?string $name = null, bool $pass_errors = false) : void {
		if(isset($name)){
			if(isset($this->routines[$name])){
				throw new RoutineCollisionException();
			}

			$this->routines[$name] = $routine;

			array_push($this->running_routines, $name);
		}

		try {
			$routine->bind($this, $name);
			$routine->run();
		} catch(Exception $e){
			if($pass_errors){
				throw $e;
			} else {
				$this->handle_exception($e);
			}
		} finally {
			if(isset($name)){
				array_pop($this->running_routines);
			}
		}
	}


	final public function substitute(Routine &$routine, string $name, bool $pass_errors = false) : void {
		if(end($this->running_routines) !== $name){
			throw new Exception('cannot substitute');
		}

		unset($this->routines[$name]);

		$this->run($routine, $name, $pass_errors);
	}


	final public function handle_exception(Exception $exception) : void {
		throw $exception;
	}


	final public function get_routine(string $name) : Routine {
		if(!isset($this->routines[$name])){
			throw new Exception('routine not found');
		}

		return $this->routines[$name];
	}


	final public function send_header() : void {
		$this->response->send_headers();
		$this->cookie_manager->send_headers();
	}


	final public function send_and_exit(string $content) : void {
		$this->send_header();
		echo $content;
		exit;
	}


	final public function &get_request() : Request {
		return $this->request;
	}


	final public function &get_response() : Response {
		return $this->response;
	}


	final public function &get_db() : DatabaseAccess {
		return $this->db;
	}


	final public function &get_session_manager() : SessionManager {
		return $this->session_manager;
	}


	final public function &get_cookie_manager() : CookieManager {
		return $this->cookie_manager;
	}


	final public function &get_authenticator() : Authenticator {
		return $this->authenticator;
	}
}