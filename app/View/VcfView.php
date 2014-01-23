<?php
/**
 * View class for VCF response
 */
App::uses('View', 'View');
App::uses('HtmlHelper', 'View/Helper');
/**
 * View class that is used for VCF response.
 * @author Alexander M. Lang <alexander.m.lang@gmail.com>
 * @copyright (c) 2013, Alexander M. Lang
 * @version 1.0.0
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @package View
 */
class VcfView extends View {

	/**
	 * VCF Versions
	 */
	const
		VCF_V40 = 4.0,
		VCF_V30 = 3.0,
		VCF_V21 = 2.1;
	
	public
		$subDir = 'vcf',
		$helper = array('Html'),
		$vcard = '';
	protected
		/**
		 * All vCard Elements
		 */
		$_elements = array(
			'name' => 'N:$familyName;$givenName;$middleName;$honorificPrefix;$honorificSuffix',
			'nickname' => 'NICKNAME:$nickname',
			'fullName' => 'FN:$honorificPrefix $givenName $middleName $familyName $honorificSuffix',
			'birthday' => 'BDAY:$birthday',
			'anniversary' => 'ANNIVERSARY:$birthday',
			/**
			 * Defines a language that the person speaks.
			 * language: according to ISO 639-1
			 * options:
			 * [type	=	work|home ] where the person speaks as described,
			 * [pref = 1 | 2| 3| ...] preferential language the person speak
			 */
			'language' => 'LANG;$options:$language',
			/**
			  2.1: PHOTO;JPEG:http://example.com/photo.jpg
			  2.1: PHOTO;JPEG;ENCODING=BASE64:[base64-data]
			  3.0: PHOTO;TYPE=JPEG:http://example.com/photo.jpg
			  3.0: PHOTO;TYPE=JPEG;ENCODING=B:[base64-data]
			  4.0: PHOTO;MEDIATYPE=image/jpeg:http://example.com/photo.jpg
			  4.0: PHOTO:data:image/jpeg;base64,[base64-data]
			 */
			'photo' => array(
				self::VCF_V21 => 'PHOTO;ENCODING=BASE64;TYPE=JPEG:$photo', //v2.1
				self::VCF_V30 => 'PHOTO;VALUE=URL;TYPE=JPEG:$photo', //v3
				self::VCF_V40 => 'PHOTO;VALUE=uri:$photo', //v4
			),
			/**
			  2.1: LOGO;PNG:http://example.com/logo.png
			  2.1: LOGO;PNG;ENCODING=BASE64:[base64-data]
			  3.0: LOGO;TYPE=PNG:http://example.com/logo.png
			  3.0: PHOTO;TYPE=PNG;ENCODING=B:[base64-data]
			  4.0: LOGO;MEDIATYPE=image/png:http://example.com/logo.png
			  4.0: PHOTO:data:image/png;base64,[base64-data]
			 */
			'logo' => 'LOGO;VALUE=uri:$logoUri',
			'organization' => 'ORG:$legalName,$department',
			'jobTitle' => 'TITLE:$jobTitle',
			'role' => 'ROLE:$role',
			/**
			 * An URI to use for sending a scheduling request to the person's calendar.
			 * @rfc6350 To specify the calendar user address [RFC5545] to which a
			  scheduling request [RFC5546] should be sent for the object
			  represented by the vCard.
			 * @since VCF v4.0
			 */
			'calendar' => 'CALADRURI:$calenderuri',
			/**
			 * An URI to the person's calendar.
			 * @rfc6350 To specify the URI for a calendar associated with the
			  object represented by the vCard.
			 * @since VCF v4.0
			 */
			'calendar' => 'CALADRURI:$calenderuri',
			'categories' => 'CATEGORIES:$categories',
			/**
			 * Describes the sensitivity of the information in the vCard.
			 * 
			 * @deprecated since version VCF v4.0
			 * @see RFC6350 Appendix A.2 Removed Features
			 */
			'sensitivity'=>'CLASS:$sensitivity',
			/**
			 * Type of email program used.
			 * 
			 * @deprecated since version VCF v4.0
			 * @see RFC6350 Appendix A.2 Removed Features
			 */
			'mailer'=>'MAILER:$mailer',
			'gender'=>'GENDER:$gender',
			'geo'=>array(
				self::VCF_V21 => 'GEO:$latitude;$longitude',
				self::VCF_V30 => 'GEO:$latitude;$longitude',
				self::VCF_V40 => 'GEO:geo:$latitude,$longitude',
			),
			/**
			 * A structured representation of the physical delivery address for the vCard object.
			 * If self::VCF_VERSION >= 4.0 LABEL property will inserted automaticly
			 * deprecated types since VCF v4.0
			 * The "intl", "dom", "postal", and "parcel" TYPE parameter values
			 * for the ADR property have been removed.
			 * @see RFC6350 Appendix A.2 Removed Features
			 */
			'address' => 'ADR;TYPE=$type$label:$postOfficeBoxNumber;$extendedAddress;$streetAddress;$addressLocality;$addressRegion;$postalCode;$addressCountry;',
			/**
			 * @deprecated since version VCF v4.0
			 * @see RFC6350 Appendix A.2 Removed Features
			 */
			'label' => array(
				self::VCF_V21 => 'LABEL;$type;ENCODING=QUOTED-PRINTABLE:$streetAddress=0D=0A$addressLocality $addressRegion postalCode=0D=0A$addressCountry;',
				self::VCF_V30 => 'LABEL;TYPE=$type:$streetAddress\n$addressLocality $addressRegion postalCode\n$addressCountry;'//v3
			
			),
			/**
			 * To specify the kind of object the vCard represents.
			 * values: "individual", "group", "org", "location"
			 */
			'kind'=>'KIND:$kind',
			'telephone' => 'TEL;TYPE=$type:$number',
			'email' => 'EMAIL;TYPE=internet:$email',
			'timezone' => 'TZ:$timezone',
			'url' => 'URL:$uri',
			'uid' => 'UID:urn:uuid:$uuid',
			//Specifies supplemental information or a comment that is associated with the vCard.
			'note'=> 'NOTE:$note',
			'vcard_producer' => 'PRODID:-//$producer//VcfView v1.0.0//EN'
			),
		$_name = '';
	
	private
		$_eol = "\r\n",
		$_seperator = ':',
		$_terminator = ';',
		$_vcf_version = self::VCF_V40;

	public function __construct(Controller $controller = null) {
		parent::__construct($controller);
		if (strcmp($this->_eol, chr(0x00D) . chr(0x00A)) != 0) {
			$this->_eol = chr(0x00D) . chr(0x00A);
		}
		if (isset($controller->response) && $controller->response instanceof CakeResponse) {
			$controller->response->type('vcf');
		}
	}

	public function loadHelpers() {
		if (isset($this->viewVars['_serialize'])) {
			return;
		}
		parent::loadHelpers();
	}

	public function __call($method, $params) {
		array_unshift($params, $method);
		if (method_exists($this, $method) && $method != '_element') {
			return $this->dispatchMethod($method, $params);
		}
		elseif (isset($this->_elements[$method])) {
			return $this->dispatchMethod('_element', $params);
		}
	}

	public function start($version=self::VCF_V40) {
		$this->_setVcfVersion($version);
		$out = 'BEGIN:VCARD' . $this->_eol;
		$out .= 'VERSION:' . $this->_getVcfVersion() . $this->_eol;
		$out .= 'PROFILE:VCARD' . $this->_eol;
		$out .= 'REV:' . date('ymd\THis\Z') . $this->_eol;
		$out .= 'SOURCE:' . $this->Html->url('/'.$this->request->url,true) . $this->_eol;
		return $out;
	}

	public function name($values) {
		if (Hash::check($values, 'name.legalName')) {
			return $this->_element('organization', $values['name']['legalName']);
		}
		if (Hash::check($values, 'name.nickname')) {
			if (is_array($values['name']['nickname'])) {
				$values['name']['nickname'] = String::toList($values['name']['nickname'], ', ');
				return $this->_element('nickname', $values['name']['nickname']);
			}
		}
		$N = $this->_element(__FUNCTION__, $values);
		$FN = $this->_element('fullName', $values);
		$this->_fullName = substr($FN, 3);
		return $N . $FN;
	}

	public function birthday($values) {
		App::uses('CakeTime', 'Utility');
		if (!is_array($values)) {
			$values = array('birthday' => $values);
		}
		$birthday = date('Y-m-d', strtotime($values['birthday']));
		return $this->_element('birthday', compact('birthday'));
	}

	public function photo($values) {
		//@todo if value is file and image then base64 encode,
		//else if value is base64 encoded then set value ($isEnconded = base64_decode("asd",true);)
		//else if value isn't a file do nothing
	}
	
	public function uid() {
		$uuid = String::uuid();
		return $this->_element(__FUNCTION__, compact('uuid'));
	}

	public function address($values) {
		$label = ';LABEL="%s"';
		$_default = array(
			'postOfficeBoxNumber' => '',
			'extendedAddress' => '',
			'streetAddress' => '',
			'addressLocality' => '',
			'addressRegion' => '',
			'postalCode' => '',
			'addressCountry' => '',
			'type' => array('intl', 'postal', 'parcel', 'work'),
			'format' => '$fullName\n$streetAddress\n$addressLocality, $addressRegion $postalCode\n$addressCountry'
		);

		if (!is_array($values)) {
			return false;
		}
		$values = Hash::merge($_default, $values);

		if ($this->_getVcfVersion() >= 4.0) {
			$values['label'] = ($values['format'] !== '') ? String::insert(sprintf($label, $values['format']), $values, array('clean' => true, 'before' => '$')) : '';
			$values['type'] = String::toList($values['type'], ', ');
			$values['fullName'] = $this->_fullName;
			return $this->_element(__FUNCTION__, $values);
		}
		else {
			$values['type'] = 'WORK';
			$address = $this->_element(__FUNCTION__, $values);
			$label = $this->_element('label', $values);
			return $address . $label;
		}
	}

	/**
	 * 
	 * TYPE =	work, msg, work, pref,
	 * 			voice, cell, fax, video,
	 * 			pager, bbs, modem, car, isdn and pcs
	 * 
	 * @param type $values
	 * @return type
	 */
	public function telephone($values) {
		$_default = array('number' => '', 'type' => '');
		if (!is_array($values)) {
			$values = Hash::merge($_default, array('number' => $values, 'type' => 'voice'));
		}
		$values = Hash::merge($_default, $values);
		if (is_array($values['type'])) {
			$values['type'] = String::toList($values['type'], ', ');
		}
		return $this->_element(__FUNCTION__, $values);
	}

	protected function _element($type, $values) {
		if (is_array($this->_elements[$type])) {
			$this->_elements[$type] = $this->_elements[$type][$this->_getVcfVersion()];
		}
		if(!is_array($values)){
			$values = array($type => $values);
		}
		return String::insert($this->_elements[$type], $this->_escape($values), array('clean' => true, 'before' => '$')) . $this->_eol;
	}
	
	public function end() {
		return "END:VCARD" . $this->_eol;
	}

	public function render($view = null, $layout = null) {
		$return = null;
		if (isset($this->viewVars['_serialize'])) {
			$return = $this->_serialize($this->viewVars['_serialize']);
		}
		elseif ($view !== false && $this->_getViewFileName($view)) {
			$return = parent::render($view, false);
		}
		return $return;
	}

	protected function _serialize($serialize) {
		$vcf = $this->start($this->_getVcfVersion());
		foreach ($this->viewVars[$serialize] as $method => $values) {
			$vcf .= $this->{$method}($values);
		}
		$vcf .= $this->end();
		return $vcf;
	}

	/**
	 * Escape values for vcard (',' ';' '\' ':' )
	 * @see RFC 6350
	 * 
	 * @param mixed $values Values either string or array.
	 * @return string Escaped string
	 * */
	protected function _escape($values) {
		$find = array($this->_seperator, $this->_terminator,);
		$replace = array('\:', '\;', '\,');
		return is_array($values) ? array_map(array($this, '_escape'), $values) : str_replace($find, $replace, $values);
	}
	
	/**
	 * Fnc compares given $version and setted VCF Version
	 * @param int|float $version
	 * @return bool
	 */
	private function _compareVcfVersion($version) {
		return (boolean) ($this->_getVcfVersion() === $version);
	}

	private function _setVcfVersion($version) {
		if (is_string($version)) {
			if (defined('VcfView::' . $version)) {
				$this->_vcf_version = constant('VcfView::' . $version);
			}
		}
		else {
			$this->_vcf_version = $version;
		}
	}

	private function _getVcfVersion() {
		return $this->_vcf_version;
	}
}
