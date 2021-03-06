<?php



class SessionUserController extends BaseResourceController {

	public function deleteOne () {
		$id = $this->user->current('id');
		$this->user->logout();

		if ($id) {
			$this->response->status(200);
		} else {
			$this->response->status(410);
		}
	}


	public function createOne () {
		$password = $this->user->cryptPassword($this->input('password'));

		$user = $this->user->read(array(
			'username' => $this->input("username"),
			'password' => $password)
		);

		if (empty($user[0])) {
			$user = $this->user->read(array(
				'email' => $this->input("username"),
				'password' => $password)
			);
		}

		if (empty($user[0])) {
			$this->fieldError('username', 'login_incorrect');
			$this->fieldError('password', 'login_incorrect');

			return null;
		} else {
			$this->user->login($user[0]);
		}

		return $this->getOne();
	}


	public function getOne () {
		return $this->user->current();
	}
}