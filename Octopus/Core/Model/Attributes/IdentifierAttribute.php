<?php
namespace Octopus\Core\Model\Attributes;

class IdentifierAttribute extends Attribute {
	# inherited from Attribute
	# protected string $name;
	# protected mixed $value;


	public function load(mixed $value) : void {
		$this->value = $value;
	}


	public function edit(mixed $input) : void {
		if($input === $this->value){ # if the value does not change, do nothing
			return;
		}

		if(empty($input)){ # if the input is empty but the attribute is required to be set, throw an error
			if($this->is_required()){
				throw new MissingValueException($this);
			} else { # otherwise just set it to null
				$this->value = null;
			}
		}

		# check whether the attribute is tried to be altered despite not being alterable and not being local
		# (check is_local also because on local objects, even not-alterable attributes must be settable)
		if(!$this->is_alterable() && !$this->parent->db->is_local()){
			throw new AttributeNotAlterableException($this, $this->parent, $input);
		}

		// TODO
		# check whether the input matches the defined constraints given
		$definition->validate_input($input); # throws an IllegalValueException if failing

		# check whether the input is already set as an identifier on another object
		# this does not invoke on relationships because they can only have an id and no additional identifiers
		try {
			# to do that, try to pull an object of the same class using the input as identifier
			$double = new $this->parent;
			$double->pull($input, identify_by:$name);
			throw new IdentifierCollisionException($this, $double); # worked -> identifier is already used
		} catch(EmptyResultException $e){
			# it didn't work -> identifier is not used on another object
			$this->value = $input; # set the new attribute value
		}

		$this->parent->db->set_altered();
	}
}
?>
