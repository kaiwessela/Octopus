<?php
namespace Octopus\Modules\Astronauth;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Attributes\EntityAttribute;
use Octopus\Modules\Identifiers\ID;
use Octopus\Modules\Primitives\Enumy;
use Octopus\Modules\Astronauth\Account;
use Octopus\Modules\Primitives\Stringy;
use Octopus\Modules\Timestamp\TimestampAttribute;

class Login extends Entity {
	protected ID $id;
	protected EntityAttribute $account;
	protected Stringy $key;
	protected TimestampAttribute $last_refresh;
	protected Enumy $validity; // session | persistent | revoked

	protected const DB_TABLE = 'logins';
	protected const LIST_CLASS = LoginList::class;


	protected static function define_attributes() : array {
		return [
			'id' => ID::define(length:12),
			'account' => EntityAttribute::define(class:Account::class, identify_by:'username', is_required:true, is_editable:false),
			'key' => Stringy::define(is_editable:false),
			'last_refresh' => TimestampAttribute::define(is_editable:false, is_required:true),
			'validity' => Enumy::define(is_editable:false, is_required:true, options:['session', 'persistent', 'revoked'])
		];
	}


	public function initialize(Account $account, string $validity) : void {
		$this->create();
		$this->account->edit($account);
		$this->last_refresh->edit(time());
		$this->validity->edit($validity);
	}


	public function generate_key() : string {
		$key = base64_encode(random_bytes(60));

		$this->key->edit(password_hash($key, \PASSWORD_DEFAULT));
		$this->last_refresh->edit(time());

		return $key;
	}


	public function verify(string $key) : bool {
		return password_verify($key, $this->key);
	}


	public function is_valid() : bool {
		return !$this->is_revoked() && !$this->is_expired();
	}


	public function is_expired() : bool {
		if($this->is_session()){
			return $this->last_refreshed->to_unix() > time() + static::get_session_duration();
		} else if($this->is_persistent()){
			return $this->last_refreshed->to_unix() > time() + static::get_persistent_duration();
		} else {
			return false;
		}
	}


	public function is_session() : bool {
		return $this->validity === 'session';
	}


	public function is_persistent() : bool {
		return $this->validity === 'persistent';
	}


	public function is_revoked() : bool {
		return $this->validity === 'revoked';
	}


	public static function get_session_duration() : int {
		return (int) ini_get('session.gc_maxlifetime');
	}


	public static function get_persistent_duration() : int {
		return Config::get('persistent_login_duration') ?? 2592000; # 30 days
	}
}
?>