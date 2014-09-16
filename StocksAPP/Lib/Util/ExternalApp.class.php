<?php
import("@.Util.Tools");
class ExternalApp extends Base 
{
	 /**
     +----------------------------------------------------------
     * 架构函数
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function __construct()
    {  
    }
    
    
	
	/**
	 * 获取外部插件状态
	 *
	 * @return Array
	 */
	protected function getExternalApps() 
	{

		$arr = array();

		//figure out which of our external progs exist
		if (Tools::checkAppAvail("gocr")) $arr["gocr"] = 1;
		if (Tools::checkAppAvail("wget")) $arr["wget"] = 1;
		if (Tools::checkAppAvail("zip")) $arr["zip"] = 1;

		if (Tools::checkAppAvail("mogrify")) $arr["mogrify"] = 1;
		if (Tools::checkAppAvail("convert")) $arr["convert"] = 1;
		if (Tools::checkAppAvail("montage")) $arr["montage"] = 1;
		if ($arr["mogrify"] && $arr["convert"] && $arr["montage"]) $arr["imagemagick"] = 1;

		if (Tools::checkAppAvail("pdftotext")) $arr["pdftotext"] = 1;
		if (Tools::checkAppAvail("pdfimages")) $arr["pdfimages"] = 1;
		if (Tools::checkAppAvail("pdftoppm")) $arr["pdftoppm"] = 1;
		//  if ($arr["pdftotext"] && $arr["pdfimages"] && $arr["pdftoppm"]) $arr["xpdf"] = 1;
		
		if ($this->$arr["pdftotext"]) $arr["xpdf"] = 1;

		if (Tools::checkAppAvail("gs")) $arr["gs"] = 1;

		if (Tools::checkAppAvail("tiffinfo")) $arr["tiffinfo"] = 1;
		if (Tools::checkAppAvail("tiffsplit")) $arr["tiffsplit"] = 1;
		if ($arr["tiffinfo"] && $arr["tiffsplit"]) $arr["libtiff"] = 1;

		if (function_exists("imap_open")) $arr["php_imap"] = 1;
		if (Tools::checkAppAvail("sendmail")) $arr["sendmail"] = 1;
		if (Tools::checkAppAvail("mimencode")) $arr["mimencode"] = 1;

		
		//if (($arr["sendmail"] && $arr["mimencode"]) || $arr["php_imap"]) $arr["email"] = 1;
		if (($arr["sendmail"] ) || $arr["php_imap"]) $arr["email"] = 1;

		if (Tools::checkAppAvail("enscript")) $arr["enscript"] = 1;

		if (Tools::checkAppAvail("antiword")) $arr["antiword"] = 1;

		if (Tools::checkAppAvail("clamscan")) $arr["clamscan"] = 1;

		if (Tools::checkAppAvail("iconv") || function_exists("iconv")) $arr["iconv"] = 1;

		return $arr;

	}
	
	/**
	 * 运行插件定义
	 *
	 * @return Boolean
	 */
	public function run()
	{
		if (!Session::is_set('setApps'))
		{
			Tools::checkRequiredApp("tr");
			Tools::checkRequiredApp("ps");
			Tools::checkRequiredApp("cat");
			$arr = $this->getExternalApps();
			Session::set('setApps',$arr);
		}
		
		$appinfo =  Session::get('setApps');
		//url download support
		if ($appinfo["wget"]) 
			Session::set("URL_SUPPORT",true);
		else 
			Session::set("URL_SUPPORT",null);

		//zip archive support
		if ($appinfo["zip"]) 
			Session::set("ZIP_SUPPORT",true);
		else 
			Session::set("ZIP_SUPPORT",null);

		//advanced word support
		if ($appinfo["antiword"]) 
			Session::set("DOC_SUPPORT",true);
		else 
			Session::set("DOC_SUPPORT",null);	

		//ocr support
		if (!defined("DISABLE_OCR") && ($appinfo["gocr"] &&	$appinfo["libtiff"] && $appinfo["imagemagick"]))  
			Session::set("OCR_SUPPORT",true);
		else 
			Session::set("OCR_SUPPORT",null);	

		//pdf support
		if (!defined("DISABLE_PDF")) {

			if ($appinfo["xpdf"]) 
				Session::set("XPDF_SUPPORT",true);
			else if ($appinfo["gs"]) 
				Session::set("GS_SUPPORT",true);
				
			
			if ($appinfo["xpdf"] || $appinfo["gs"]) Session::set("PDF_SUPPORT",true);

		}else {
			Session::set("PDF_SUPPORT",null);
		}

		//thumbnail support
		if (!defined("DISABLE_THUMB") && $appinfo["imagemagick"]) 
			Session::set("THUMB_SUPPORT",true);
		else 
			Session::set("THUMB_SUPPORT",null);	

		//txt thumb support
		if (defined("THUMB_SUPPORT") && $appinfo["enscript"]) 
			Session::set("ENSCRIPT_SUPPORT",true);
		else 
			Session::set("ENSCRIPT_SUPPORT",null);	

		//email support
		if (!defined("DISABLE_EMAIL") && $appinfo["email"]) {

			Session::set("EMAIL_SUPPORT",true);

			//set php_imap and sendmail availability
			if ($appinfo["php_imap"]) 
				Session::set("PHP_IMAP_SUPPORT",true);
			else 
				Session::set("PHP_IMAP_SUPPORT",null);
					
			if ($appinfo["sendmail"]) 
				Session::set("SENDMAIL_SUPPORT",true);
			else 
				Session::set("SENDMAIL_SUPPORT",null);

		}else {
			Session::set("EMAIL_SUPPORT",null);
		}

		//antivirus support
		if (!defined("DISABLE_CLAMAV") && $appinfo["clamscan"]) 
			Session::set("CLAMAV_SUPPORT",true);
		else 
			Session::set("CLAMAV_SUPPORT",null);	

		//iconv support
		if ($appinfo["iconv"]) 
			Session::set("ICONV_SUPPORT",true);
		else 
			Session::set("ICONV_SUPPORT",null);	
		
		return true;
	}

}

?>