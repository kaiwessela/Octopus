<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotLoadedException;
use \Octopus\Core\Model\Attributes\Events\AttributeEditEvent;
use \Octopus\Core\Model\Events\Prevention;

abstract class Attribute {
	protected Entity|Relationship $parent;
	protected string $name;
	protected bool $is_loaded;
	protected bool $is_required;
	protected bool $is_editable;
	protected bool $is_dirty;
	protected mixed $value;
	protected mixed $old_value;
	protected AttributeEditEvent $on_edit;

	/* TEMP */
	public function &get_on_edit() : AttributeEditEvent {
		return $this->on_edit;
	}
	//



	final function __construct(bool $is_required, bool $is_editable) {
		$this->is_required = $is_required;
		$this->is_editable = $is_editable;
		$this->on_edit = new AttributeEditEvent();
	}


	# abstract public function define() : Attribute;


	final public function bind(string $name, Entity|Relationship $parent) : void {
		$this->name = $name;
		$this->parent = &$parent;
		$this->value = null;
		$this->is_dirty = false;
		$this->is_loaded = false;
	}


	# abstract public function load($data) : void;


	final public function edit(mixed $input) : void {
		if(!$this->is_loaded()){
			throw new AttributeNotLoadedException($this);
		}

		$former_value = $this->value;

		$this->_edit($input);

		if(!$this->equals($former_value)){
			try {
				$this->on_edit->fire($this);
			} catch(Prevention $p){ # revert edit
				$this->value = $former_value;
				$p->throw_exception();
				return;
			}

			if(!$this->is_dirty()){ # first edit
				$this->set_dirty();
				$this->old_value = $former_value;
			} else if($this->equals($this->old_value)){ # set back to the old value
				$this->set_clean();
				unset($this->old_value);
			}
		}
	}


	abstract protected function _edit(mixed $input) : void;


	final public function is_loaded() : bool {
		return $this->is_loaded;
	}


	final public function is_required() : bool {
		return $this->is_required;
	}


	final public function is_editable() : bool {
		return $this->is_editable;
	}


	final public function is_dirty() : bool {
		return $this->is_dirty;
	}


	final public function require_loaded() : void { // TODO check
		if(!$this->is_loaded()){
			throw new Exception('attribute must be loaded');
		}
	}


	final protected function set_dirty() : void {
		$this->is_dirty = true;
	}


	final public function set_clean() : void {
		$this->is_dirty = false;
	}


	public function is_pullable() : bool {
		return false;
	}


	public function is_joinable() : bool {
		return false;
	}


	final public function get_name() : string {
		return $this->name;
	}


	final public function get_db_table() : string {
		return $this->parent->get_db_table();
	}


	final public function get_prefixed_db_table() : string {
		return $this->parent->get_prefixed_db_table();
	}


	final public function get_value(bool $quiet = false) : mixed {
		if(!$this->is_loaded()){
			if($quiet){
				return null;
			} else {
				throw new AttributeNotLoadedException($this);
			}
		}

		return $this->value;
	}


	final public function get_old_value(bool $quiet = false) : mixed {
		if(!$this->is_dirty()){
			return $this->get_value($quiet);
		} else {
			return $this->old_value;
		}
	}


	public function equals(mixed $value) : bool {
		return $this->value === $value;
	}


	public function is_empty() : bool {
		return $this->value === null;
	}


	abstract public function resolve_pull_condition(mixed $option) : ?Condition;


	abstract public function arrayify() : null|string|int|float|bool|array;

}
?>
