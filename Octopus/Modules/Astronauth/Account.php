<?php
namespace Octopus\Modules\Astronauth;
use Octopus\Core\Model\Entity;
use Octopus\Modules\Primitives\EmailAddress;
use Octopus\Modules\Identifiers\StringIdentifier;
use Octopus\Modules\Astronauth\Attributes\PasswordHash;

class Account extends Entity {
	protected StringIdentifier $username;
	protected EmailAddress $email;
	protected PasswordHash $password;

	protected const DB_TABLE = 'accounts';
	protected const LIST_CLASS = AccountList::class;


	protected static function define_attributes() : array {
		return [
			'username' => StringIdentifier::define(),
			'email' => EmailAddress::define(),
			'password' => PasswordHash::define()
		];
	}


	public function verify_password(mixed $password) : bool {
		return $this->password->verify($password);
	}
}
?>