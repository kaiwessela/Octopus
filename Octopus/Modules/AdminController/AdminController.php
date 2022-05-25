<?php
namespace Octopus\Modules\AdminController;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\ConfigLoader;
use \Octopus\Core\Controller\Router\ControllerCall;
use \Octopus\Core\Controller\Router\URLSubstitution;
use \Octopus\Core\Controller\Controllers\Controller;
use \Octopus\Core\Model\Entity;

class AdminController extends Controller {
	private array $config;
	private ?string $entity_name;


	public function load(Request $request, ControllerCall $call) : void {
		$this->config = ConfigLoader::read($call->get_option('config'));
		$entity_name = URLSubstitution::replace($call->get_option('entity_class'), $request, force_string:true);

		if($entity_name === ''){
			$this->entity_name = null;
		} else {
			$this->entity_name = $entity_name;
		}
	}


	public function execute(Request $request) : void {

	}


	public function finish() : void {

	}


	public function get_entity_name() : ?string {
		return $this->entity_name;
	}


	public function get_config() : array {
		return $this->config;
	}


	public function get_entity_config() : ?array {
		return $this->config[$this->entity_name] ?? null;
	}


	public function lang(string $key) : mixed {
		return $this->get_entity_config()['lang'][$key] ?? null;
	}


	public function has_live_view() : bool {
		return !empty($this->get_entity_config()['live-view-url']);
	}


	public function live_view(Entity $entity) : ?string {
		$url = $this->get_entity_config()['live-view-url'] ?? '';

		return preg_replace_callback('/{([^}]*)}/', function($matches) use ($entity){
			$match = $matches[1];
			return $entity->$match ?? '';
		}, $url);
	}
}
?>
