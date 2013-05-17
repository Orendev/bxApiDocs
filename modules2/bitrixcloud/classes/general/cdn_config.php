<?
IncludeModuleLangFile(__FILE__);
class CBitrixCloudCDNConfig
{
	private static $instance = /*.(CBitrixCloudCDNConfig).*/ null;
	private $active = 0;
	private $expires = 0; //timestamp
	private $domain = "";
	private $kernel_rewrite = /*.(bool).*/null;
	private $content_rewrite = /*.(bool).*/null;
	private $sites = /*.(array[string]string).*/ array();
	/** @var CBitrixCloudCDNQuota $quota */
	private $quota = /*.(CBitrixCloudCDNQuota).*/ null;
	/** @var CBitrixCloudCDNClasses $classes */
	private $classes = /*.(CBitrixCloudCDNClasses).*/ null;
	/** @var CBitrixCloudCDNServerGroups $server_groups */
	private $server_groups = /*.(CBitrixCloudCDNServerGroups).*/ null;
	/** @var CBitrixCloudCDNLocations $locations */
	private $locations = /*.(CBitrixCloudCDNLocations).*/ null;
	private $debug = false;
	/**
	 *
	 *
	 */
	private function __construct()
	{
	}
	/**
	 * Returns proxy class instance (singleton pattern)
	 *
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new CBitrixCloudCDNConfig;

		return self::$instance;
	}
	/**
	 *
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function updateQuota() /*. throws CBitrixCloudException .*/
	{
		$web_service = new CBitrixCloudCDNWebService($this->domain);
		$obXML = $web_service->actionQuota();
		$node = $obXML->SelectNodes("/control/quota");
		if (is_object($node))
			$this->quota = CBitrixCloudCDNQuota::fromXMLNode($node);
		else
			throw new CBitrixCloudException(GetMessage("BCL_CDN_CONFIG_XML_PARSE", array(
				"#CODE#" => "6",
			)));

		$this->quota->saveOption(CBitrixCloudOption::getOption("cdn_config_quota"));
		return $this;
	}
	/**
	 * Loads and parses xml
	 *
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function loadRemoteXML() /*. throws CBitrixCloudException .*/
	{
		//Get configuration from remote service
		$this->sites = CBitrixCloudOption::getOption("cdn_config_site")->getArrayValue();
		$this->domain = CBitrixCloudOption::getOption("cdn_config_domain")->getStringValue();

		$web_service = new CBitrixCloudCDNWebService($this->domain);
		$web_service->setDebug($this->debug);
		$obXML = $web_service->actionGetConfig();
		//
		// Parse it
		//
		$node = $obXML->SelectNodes("/control");
		if (is_object($node))
		{
			$this->active = intval($node->getAttribute("active"));
			$this->expires = strtotime($node->getAttribute("expires"));
		}
		else
		{
			$this->active = 0;
			$this->expires = 0;
		}

		$node = $obXML->SelectNodes("/control/quota");
		if (is_object($node))
			$this->quota = CBitrixCloudCDNQuota::fromXMLNode($node);
		else
			throw new CBitrixCloudException(GetMessage("BCL_CDN_CONFIG_XML_PARSE", array(
				"#CODE#" => "2",
			)));

		$node = $obXML->SelectNodes("/control/classes");
		if (is_object($node))
			$this->classes = CBitrixCloudCDNClasses::fromXMLNode($node);
		else
			throw new CBitrixCloudException(GetMessage("BCL_CDN_CONFIG_XML_PARSE", array(
				"#CODE#" => "3",
			)));

		$node = $obXML->SelectNodes("/control/servergroups");
		if (is_object($node))
			$this->server_groups = CBitrixCloudCDNServerGroups::fromXMLNode($node);
		else
			throw new CBitrixCloudException(GetMessage("BCL_CDN_CONFIG_XML_PARSE", array(
				"#CODE#" => "4",
			)));

		$node = $obXML->SelectNodes("/control/locations");
		if (is_object($node))
			$this->locations = CBitrixCloudCDNLocations::fromXMLNode($node, $this);
		else
			throw new CBitrixCloudException(GetMessage("BCL_CDN_CONFIG_XML_PARSE", array(
				"#CODE#" => "5",
			)));

		return $this;
	}
	/**
	 * Checks if it is active in webservice
	 *
	 * @return bool
	 *
	 */
	static public function isActive()
	{
		return ($this->active > 0);
	}
	/**
	 * Checks if it is time to update policy
	 *
	 * @return bool
	 *
	 */
	static public function isExpired()
	{
		return ($this->expires < time());
	}
	/**
	 * Sets the time to update policy
	 *
	 * @param int $time
	 * @return void
	 *
	 */
	static public function setExpired($time)
	{
		$this->expires = $time;
		CBitrixCloudOption::getOption("cdn_config_expire_time")->setStringValue((string)$this->expires);
	}
	/**
	 * Returns resources domain name
	 *
	 * @return string
	 *
	 */
	static public function getDomain()
	{
		return $this->domain;
	}
	/**
	 * Sets resources domain name
	 *
	 * @param string $domain
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function setDomain($domain)
	{
		$this->domain = $domain;
		return $this;
	}
	/**
	 * Returns flag of the kernel links (/bitrix/ or other) rewrite
	 *
	 * @param string $prefix
	 * @return bool
	 *
	 */
	static public function isKernelPrefix($prefix)
	{
		return preg_match("#^/bitrix/#", $prefix) > 0;
	}
	/**
	 * Returns flag of the kernel links (/bitrix/ or other) rewrite
	 *
	 * @return bool
	 *
	 */
	static public function isKernelRewriteEnabled()
	{
		//It is true by default
		if(!isset($this->kernel_rewrite))
			$this->kernel_rewrite = (CBitrixCloudOption::getOption("cdn_config_rewrite_kernel")->getStringValue() !== "false");
		return $this->kernel_rewrite;
	}
	/**
	 * Sets flag of the kernel links (/bitrix/ or other) rewrite
	 *
	 * @param bool $rewrite
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function setKernelRewrite($rewrite = true)
	{
		$this->kernel_rewrite = ($rewrite != false);
		return $this;
	}
	/**
	 * Returns flag of the content links (not kernel) rewrite
	 *
	 * @return bool
	 *
	 */
	static public function isContentRewriteEnabled()
	{
		//It is false by default
		if(!isset($this->content_rewrite))
			$this->content_rewrite = (CBitrixCloudOption::getOption("cdn_config_content_rewrite")->getStringValue() === "true");
		return $this->content_rewrite;
	}
	/**
	 * Sets flag of the content links (not kernel) rewrite
	 *
	 * @param bool $rewrite
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function setContentRewrite($rewrite = true)
	{
		$this->content_rewrite = ($rewrite == true);
		return $this;
	}
	/**
	 * Returns array of sites
	 *
	 * @return array[string]string
	 *
	 */
	static public function getSites()
	{
		return $this->sites;
	}
	/**
	 * Sets array of sites to enable CDN
	 *
	 * @param array[string]string $sites
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function setSites($sites)
	{
		$this->sites = /*.(array[string]string).*/ array();
		if (is_array($sites))
		{
			foreach ($sites as $site_id)
				$this->sites[$site_id] = $site_id;
		}
		return $this;
	}
	/**
	 * Returns quota object
	 *
	 * @return CBitrixCloudCDNQuota
	 *
	 */
	static public function getQuota()
	{
		return $this->quota;
	}
	/**
	 * Returns file class object by it's name
	 *
	 * @param string $class_name
	 * @return CBitrixCloudCDNClass
	 *
	 */
	static public function getClassByName($class_name)
	{
		return $this->classes->getClass($class_name);
	}
	/**
	 * Returns server group object by it's name
	 *
	 * @param string $server_group_name
	 * @return CBitrixCloudCDNServerGroup
	 *
	 *
	 */
	static public function getServerGroupByName($server_group_name)
	{
		return $this->server_groups->getGroup($server_group_name);
	}
	/**
	 * Returns configured locations
	 *
	 * @return CBitrixCloudCDNLocations
	 *
	 */
	static public function getLocations()
	{
		return $this->locations;
	}
	/**
	 * Returns unique array of all prefixes across all locations
	 *
	 * @param bool $bKernel
	 * @param bool $bContent
	 * @return array[int]string
	 *
	 */
	static public function getLocationsPrefixes($bKernel = true, $bContent = false)
	{
		$arPrefixes = /*.(array[int]string).*/array();
		/** @var CBitrixCloudCDNLocation $location */
		$location = /*.(CBitrixCloudCDNLocation).*/ null;
		foreach ($this->locations as $location)
		{
			$arPrefixes = array_merge($arPrefixes, $location->getPrefixes());
		}

		foreach ($arPrefixes as $i => $prefix)
		{
			if ($this->isKernelPrefix($prefix))
			{
				if (!$bKernel)
					unset($arPrefixes[$i]);
			}
			else
			{
				if (!$bContent)
					unset($arPrefixes[$i]);
			}
		}

		return array_unique($arPrefixes);
	}
	/**
	 * Returns unique array of all extensions across all locations
	 *
	 * @return array[int]string
	 *
	 */
	static public function getLocationsExtensions()
	{
		$arExtensions = array();
		/** @var CBitrixCloudCDNLocation $location */
		$location = /*.(CBitrixCloudCDNLocation).*/ null;
		foreach ($this->locations as $location)
		{
			foreach ($location->getClasses() as $file_class)
			{
				/** @var CBitrixCloudCDNClass $file_class */
				$arExtensions = array_merge($arExtensions, $file_class->getExtensions());
			}
		}
		return array_unique($arExtensions);
	}
	/**
	 * Saves configuration into CBitrixCloudOption
	 *
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function saveToOptions()
	{
		CBitrixCloudOption::getOption("cdn_config_active")->setStringValue((string)$this->active);
		CBitrixCloudOption::getOption("cdn_config_expire_time")->setStringValue((string)$this->expires);
		CBitrixCloudOption::getOption("cdn_config_domain")->setStringValue($this->domain);
		CBitrixCloudOption::getOption("cdn_config_site")->setArrayValue($this->sites);
		CBitrixCloudOption::getOption("cdn_config_content_rewrite")->setStringValue($this->content_rewrite? "true": "false");
		CBitrixCloudOption::getOption("cdn_config_rewrite_kernel")->setStringValue($this->kernel_rewrite? "true": "false");
		$this->quota->saveOption(CBitrixCloudOption::getOption("cdn_config_quota"));
		$this->classes->saveOption(CBitrixCloudOption::getOption("cdn_class"));
		$this->server_groups->saveOption(CBitrixCloudOption::getOption("cdn_server_group"));
		$this->locations->saveOption(CBitrixCloudOption::getOption("cdn_location"));
		return $this;
	}
	/**
	 * Loads configuration from CBitrixCloudOption
	 *
	 * @return CBitrixCloudCDNConfig
	 *
	 */
	static public function loadFromOptions()
	{
		$this->active = intval(CBitrixCloudOption::getOption("cdn_config_active")->getStringValue());
		$this->expires = intval(CBitrixCloudOption::getOption("cdn_config_expire_time")->getStringValue());
		$this->domain = CBitrixCloudOption::getOption("cdn_config_domain")->getStringValue();
		$this->sites = CBitrixCloudOption::getOption("cdn_config_site")->getArrayValue();
		$this->quota = CBitrixCloudCDNQuota::fromOption(CBitrixCloudOption::getOption("cdn_config_quota"));
		$this->classes = CBitrixCloudCDNClasses::fromOption(CBitrixCloudOption::getOption("cdn_class"));
		$this->server_groups = CBitrixCloudCDNServerGroups::fromOption(CBitrixCloudOption::getOption("cdn_server_group"));
		$this->locations = CBitrixCloudCDNLocations::fromOption(CBitrixCloudOption::getOption("cdn_location"), $this);
		return $this;
	}
	/**
	 * @return bool
	 *
	 */
	static public function lock()
	{
		return CBitrixCloudOption::lock();
	}
	/**
	 * @return void
	 *
	 */
	static public function unlock()
	{
		CBitrixCloudOption::unlock();
	}
	/**
	 *
	 * @param bool $bActive
	 * @return void
	 *
	 */
	static public function setDebug($bActive)
	{
		$this->debug = $bActive === true;
	}
}
?>