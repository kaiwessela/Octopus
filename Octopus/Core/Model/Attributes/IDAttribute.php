<?php
namespace Octopus\Core\Model\Attributes;

class IDAttribute extends IdentifierAttribute {
	# inherited from Attribute
	# protected string $name;
	# protected mixed $value;


	public function edit(mixed $input) : void {
		throw new AttributeNotAlterableException(); // TODO
	}


	# Generate a random value that is set as the entitys id upon creation
	# The id is 8 characters long and consists of these characters: 0123456789abcdef (hexadecimal/base16)
	public function generate() : void {
		if(isset($this->value)){ # if the id has already been generated, throw an exception
			throw new AttributeNotAlterableException(); // TODO
		}

		$this->value = bin2hex(random_bytes(4)); # generate 4 random bytes, then turn them into a hexadecimal string
	}
}
?>
