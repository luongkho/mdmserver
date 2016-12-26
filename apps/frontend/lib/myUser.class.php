<?php
use Gcs\Decorator\UserDecorator;
class myUser extends sfBasicSecurityUser
{
	private $decor;

	public function __construct(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
	{
		parent::__construct($dispatcher, $storage, $options );
		$this->decor = new UserDecorator;
		$this->decor->setUser($this->getAttribute('userinfo'));
	}

	public function getDecorator()
	{
		return $this->decor;		
	}

}
