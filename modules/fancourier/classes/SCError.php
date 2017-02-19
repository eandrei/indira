<?php

require_once(dirname(__FILE__).'/../fancourier.php');

// Inherit of FANCourier to have acces to the module method and objet model method
class SCError extends FANCourier
{
	// Const for better understanding
	const WARNING = 0;
	const REQUIRED = 1;

	// Available error list
	private $errors_list = array();

	public function __construct()
	{
		// Get the parent stuff with Backward Compatibility
		parent::__construct();

		$this->errors_list = array(
			// Error code returned by the ECHEC URL request (Required)
			SCError::REQUIRED => array(
				'001' => $this->l('FO id missing'),
				'002' => $this->l('Wrong FO id'),
				'003' => $this->l('Client access denied'),
				'004' => $this->l('Required fields missing'),
				'006' => $this->l('Missing signature'),
				'007' => $this->l('Wrong sign or number version'),
				'008' => $this->l('Wrong zip code'),
				'009' => $this->l('Wrong format of the Validation back url'),
				'010' => $this->l('Wrong format of the Failed back url'),
				'011' => $this->l('Invalid transaction number'),
				'012' => $this->l('Wrong format of the fees'),
				'015' => $this->l('App server unavailable'),
				'016' => $this->l('SGBD unavailable')
			),
			// Error code returned bu the Validation URL request (Warning)
			SCError::WARNING => array(
				'501' => $this->l('Mail field too long, trunked'),
				'502' => $this->l('Phone field too long, trunked'),
				'503' => $this->l('Name field too long, trunked'),
				'504' => $this->l('First name field too long, trunked'),
				'505' => $this->l('Social reason field too long, trunked'),
				'506' => $this->l('Floor field too long, trunked'),
				'507' => $this->l('Hall field too long, trunked'),
				'508' => $this->l('Locality field too long'),
				'509' => $this->l('Number and wording access field too long, trunked'),
				'510' => $this->l('Town field too long, trunked'),
				'511' => $this->l('Intercom field too long, trunked'),
				'512' => $this->l('Further Information field too long, trunked'),
				'513' => $this->l('Door code field too long, trunked'),
				'514' => $this->l('Door code field too long, trunked'),
				'515' => $this->l('Customer number too long, trunked'),
				'516' => $this->l('Transaction order too long, trunked'),
				'517' => $this->l('ParamPlus field too long, trunked'),

				'131' => $this->l('Invalid civility, field ignored'),
				'132' => $this->l('Delay preparation is invalid, ignored'),
				'133' => $this->l('Invalid weight field, ignored'),

				// Keep from previous dev (Personal error)
				'998' => $this->l('Invalid regenerated sign'),
				'999' => $this->l('Error occurred during shipping step.'),
			)
		);
	}

	/**
	 * Return error type
	 *
	 * @param $number (integer or string)
	 * @param bool $type (SCError::REQUIRED or SCError::WARNING)
	 * @return mixed string|bool
	 */
	public function getError($number, $type = false)
	{
		$number = (string)trim($number);

		if ($type === false || !isset($this->errors_list[$type]))
			$tab = $this->errors_list[SCError::REQUIRED] + $this->errors_list[SCError::WARNING];
		else
			$tab = $this->errors_list[$type];

		return isset($tab[$number]) ? $tab[$number] : false;
	}

	/**
	 * Check the errors list.
	 *
	 * @param $errors
	 * @param bool $type
	 * @return bool
	 */
	public function checkErrors($errors, $type = false)
	{
		foreach($errors as $num)
			if (($str = $this->getError($num, $type)))
				return $str;
		return false;
	}
}