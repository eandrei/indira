<?php


class AwoCouponMail {

	public static function sendMail($from, $from_name, $to, $subject, $body, $mode = 0, $bcc = null, $attachments = null, $is_embed = false, $string_attachments = null) {
		return
			version_compare(_PS_VERSION_, '1.6.1.5', '>=')
				? self::sendMail2($from, $from_name, $to, $subject, $body, $mode, $bcc, $attachments, $is_embed, $string_attachments)
				: self::sendMail1($from, $from_name, $to, $subject, $body, $mode, $bcc, $attachments, $is_embed, $string_attachments)
			;
	}

	private static function sendMail2($from, $from_name, $to, $subject, $body, $mode = 0, $bcc = null, $attachments = null, $is_embed = false, $string_attachments = null) {
		include_once(_PS_SWIFT_DIR_.'swift_required.php');
		
		$configuration = Configuration::getMultiple(array(
			'PS_SHOP_EMAIL',
			'PS_MAIL_METHOD',
			'PS_MAIL_SERVER',
			'PS_MAIL_USER',
			'PS_MAIL_PASSWD',
			'PS_SHOP_NAME',
			'PS_MAIL_SMTP_ENCRYPTION',
			'PS_MAIL_SMTP_PORT',
			'PS_MAIL_TYPE'
		));
		$die = false;
		
	   // Returns immediatly if emails are deactivated
		if ($configuration['PS_MAIL_METHOD'] == 3) return true;

        if (!isset($configuration['PS_MAIL_SMTP_ENCRYPTION']) || Tools::strtolower($configuration['PS_MAIL_SMTP_ENCRYPTION']) === 'off') $configuration['PS_MAIL_SMTP_ENCRYPTION'] = false;
		if (!isset($configuration['PS_MAIL_SMTP_PORT'])) $configuration['PS_MAIL_SMTP_PORT'] = 'default';

		if (!isset($from) || !Validate::isEmail($from)) $from = $configuration['PS_SHOP_EMAIL'];
		if (!Validate::isEmail($from)) $from = null;

		if (!isset($from_name) || !Validate::isMailName($from_name)) $from_name = $configuration['PS_SHOP_NAME'];
		if (!Validate::isMailName($from_name)) $from_name = null;

		// It would be difficult to send an e-mail if the e-mail is not valid, so this time we can die if there is a problem
		if (!Validate::isEmail($to))
		{
			Tools::dieOrLog(Tools::displayError('Error: parameter "to" is corrupted'), $die);
			return false;
		}

		$to_name = null;

		if (!Validate::isMailSubject($subject))
		{
			Tools::dieOrLog(Tools::displayError('Error: invalid e-mail subject'), $die);
			return false;
		}

		/* Construct multiple recipients list if needed */
		$message = Swift_Message::newInstance();
		/* Simple recipient, one address */
		$to_name = '';
		$message->addTo($to, $to_name);

		if (isset($bcc)) $message->addBcc($bcc);
		
		try {
			/* Connect with the appropriate configuration */
			if ($configuration['PS_MAIL_METHOD'] == 2) {
				if (empty($configuration['PS_MAIL_SERVER']) || empty($configuration['PS_MAIL_SMTP_PORT'])) {
					Tools::dieOrLog(Tools::displayError('Error: invalid SMTP server or SMTP port'), $die);
					return false;
				}

				$connection = Swift_SmtpTransport::newInstance($configuration['PS_MAIL_SERVER'], $configuration['PS_MAIL_SMTP_PORT'], $configuration['PS_MAIL_SMTP_ENCRYPTION'])
					->setUsername($configuration['PS_MAIL_USER'])
					->setPassword($configuration['PS_MAIL_PASSWD']);

			} else {
				$connection = Swift_MailTransport::newInstance();
			}
			
			if (!$connection) return false;


			$swift = Swift_Mailer::newInstance($connection);

			/* Create mail and attach differents parts */
			$message->setSubject($subject);

			$message->setCharset('utf-8');

			/* Set Message-ID - getmypid() is blocked on some hosting */
			$message->setId(self::generateId2());
			

			if (!empty($attachments) && is_array($attachments)) {
				$i = 0;
				foreach ($attachments as $attachment)
				{
					$image_part = pathinfo($attachment);
					$mime = $image_part['extension'] == 'png' ? 'image/png' : 'image/jpeg';
					$i++;
					$file_name = 'voucher'.($i).'.'.$image_part['extension'];
					
					if ($is_embed) 	{
						$cid = $message->embed(Swift_Image::fromPath($attachment));
						$body = str_replace('cid:couponimageembed'.$i, $cid, $body);
					}
					else $message->attach(Swift_Attachment::fromPath($attachment, $mime)->setFilename($file_name));

				}
			}
			if (!empty($string_attachments) && is_array($string_attachments)) {
				$i = 0;
				foreach ($string_attachments as $name => $attachment) $message->attach(Swift_Attachment::newInstance()->setFilename($name)->setBody($attachment));
			}
			
			$message->addPart($body, $mode==true ? 'text/html' : 'text/plain', 'utf-8');


			/* Send mail */
			$message->setFrom(array($from => $from_name));
			$send = $swift->send($message);

			if (version_compare(_PS_VERSION_, '1.5', '>=')) ShopUrl::resetMainDomainCache();
			

		} catch (Swift_SwiftException $e) {
			return false;
		}
		
		return true;


	}


	private static function sendMail1($from, $from_name, $to, $subject, $body, $mode = 0, $bcc = null, $attachments = null, $is_embed = false, $string_attachments = null) {
		include_once(_PS_SWIFT_DIR_.'Swift.php');
		include_once(_PS_SWIFT_DIR_.'Swift/Connection/SMTP.php');
		include_once(_PS_SWIFT_DIR_.'Swift/Connection/NativeMail.php');
		include_once(_PS_SWIFT_DIR_.'Swift/Plugin/Decorator.php');
	
		$configuration = Configuration::getMultiple(array(
			'PS_SHOP_EMAIL',
			'PS_MAIL_METHOD',
			'PS_MAIL_SERVER',
			'PS_MAIL_USER',
			'PS_MAIL_PASSWD',
			'PS_SHOP_NAME',
			'PS_MAIL_SMTP_ENCRYPTION',
			'PS_MAIL_SMTP_PORT',
			'PS_MAIL_TYPE'
		));
		$die = false;

		// Returns immediatly if emails are deactivated
		if ($configuration['PS_MAIL_METHOD'] == 3) return true;

		if (!isset($configuration['PS_MAIL_SMTP_ENCRYPTION'])) $configuration['PS_MAIL_SMTP_ENCRYPTION'] = 'off';
		if (!isset($configuration['PS_MAIL_SMTP_PORT'])) $configuration['PS_MAIL_SMTP_PORT'] = 'default';

		if (!isset($from) || !Validate::isEmail($from)) $from = $configuration['PS_SHOP_EMAIL'];
		if (!Validate::isEmail($from)) $from = null;

		if (!isset($from_name) || !Validate::isMailName($from_name)) $from_name = $configuration['PS_SHOP_NAME'];
		if (!Validate::isMailName($from_name)) $from_name = null;

		// It would be difficult to send an e-mail if the e-mail is not valid, so this time we can die if there is a problem
		if (!Validate::isEmail($to))
		{
			Tools::dieOrLog(Tools::displayError('Error: parameter "to" is corrupted'), $die);
			return false;
		}

		$to_name = null;

		if (!Validate::isMailSubject($subject))
		{
			Tools::dieOrLog(Tools::displayError('Error: invalid e-mail subject'), $die);
			return false;
		}

		/* Construct multiple recipients list if needed */
		$to_list = new Swift_RecipientList();
		/* Simple recipient, one address */
		$to_name = '';
		$to_list->addTo($to, $to_name);

		if (isset($bcc)) $to_list->addBcc($bcc);

		$to = $to_list;
		try {
			/* Connect with the appropriate configuration */
			if ($configuration['PS_MAIL_METHOD'] == 2)
			{
				if (empty($configuration['PS_MAIL_SERVER']) || empty($configuration['PS_MAIL_SMTP_PORT']))
				{
					Tools::dieOrLog(Tools::displayError('Error: invalid SMTP server or SMTP port'), $die);
					return false;
				}
				$connection = new Swift_Connection_SMTP($configuration['PS_MAIL_SERVER'], $configuration['PS_MAIL_SMTP_PORT'],
					($configuration['PS_MAIL_SMTP_ENCRYPTION'] == 'ssl') ? Swift_Connection_SMTP::ENC_SSL :
					(($configuration['PS_MAIL_SMTP_ENCRYPTION'] == 'tls') ? Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_OFF));
				$connection->setTimeout(4);
				if (!$connection) return false;
				if (!empty($configuration['PS_MAIL_USER'])) $connection->setUsername($configuration['PS_MAIL_USER']);
				if (!empty($configuration['PS_MAIL_PASSWD'])) $connection->setPassword($configuration['PS_MAIL_PASSWD']);
			}
			else $connection = new Swift_Connection_NativeMail();

			if (!$connection) return false;
			$swift = new Swift($connection, Configuration::get('PS_MAIL_DOMAIN'));

			/* Create mail and attach differents parts */
			$message = new Swift_Message('['.Configuration::get('PS_SHOP_NAME').'] '.$subject);

			$message->setCharset('utf-8');

			/* Set Message-ID - getmypid() is blocked on some hosting */
			$message->setId(self::generateId1());

			$message->headers->setEncoding('Q');

			if ($mode == true) $message->attach(new Swift_Message_Part($body, 'text/html', '8bit', 'utf-8'));
			else $message->attach(new Swift_Message_Part($body, 'text/plain', '8bit', 'utf-8'));

			if (!empty($attachments) && is_array($attachments))
			{
				$i = 0;
				foreach ($attachments as $attachment)
				{
					$image_part = pathinfo($attachment);
					$mime = $image_part['extension'] == 'png' ? 'image/png' : 'image/jpeg';
					$i++;
					$file_name = 'voucher'.($i).'.'.$image_part['extension'];
					if ($is_embed) $message->attach(new Swift_Message_EmbeddedFile(new Swift_File($attachment), $file_name, $mime, 'couponimageembed'.$i));
					else $message->attach(new Swift_Message_Attachment(new Swift_File($attachment), $file_name, $mime));
				}
			}
			if (!empty($string_attachments) && is_array($string_attachments))
			{
				$i = 0;
				foreach ($string_attachments as $name => $attachment) $message->attach(new Swift_Message_Attachment($attachment, $name));
			}

			/* Send mail */
			$send = $swift->send($message, $to, new Swift_Address($from, $from_name));
			$swift->disconnect();

			if (version_compare(_PS_VERSION_, '1.5', '>=')) ShopUrl::resetMainDomainCache();

		}
		catch (Swift_Exception $e) {
			return false;
		}

		return true;

	}

	/* Rewrite of Swift_Message::generateId() without getmypid() */
	protected static function generateId1($idstring = null)
	{
		$midparams = array(
			'utctime' => gmstrftime('%Y%m%d%H%M%S'),
			'randint' => mt_rand(),
			'customstr' => (preg_match('/^(?<!\\.)[a-z0-9\\.]+(?!\\.)\$/iD', $idstring) ? $idstring : 'swift') ,
			'hostname' => (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : php_uname('n')),
		);
		return vsprintf('<%s.%d.%s@%s>', $midparams);
	}

    protected static function generateId2($idstring = null)
    {
        $midparams = array(
            'utctime' => gmstrftime('%Y%m%d%H%M%S'),
            'randint' => mt_rand(),
            'customstr' => (preg_match("/^(?<!\\.)[a-z0-9\\.]+(?!\\.)\$/iD", $idstring) ? $idstring : "swift") ,
            'hostname' => (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : php_uname('n')),
        );
        return vsprintf("%s.%d.%s@%s", $midparams);
    }

}
