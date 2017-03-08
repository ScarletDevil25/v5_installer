<?php
namespace View;
abstract class Base {
	
    public $data = array();
	public $JS = [ ];
    public $modules = [];
	protected $title = [];

	/**
     * create and return response content
     * @return mixed
     */
	public function __construct() {
		$f3 = \Base::instance();
		$UI_BASE = $f3->get('UI');
		$UI = "";

		// get a layout to be used
		if ( isset($_SESSION['layout']) AND in_array($_SESSION['layout'], \Config::getPublic("layout_available")) AND FALSE!==\Config::getPublic("layout_forced") )
			$tpl = $_SESSION['layout'];
		else
			$tpl = \Config::getPublic("layout_default");
		
		if ( !file_exists( $UI_BASE.$tpl ) ) $tpl = "default";

		/*
			This so needs to be reworked!
		*/
		if($tpl!="default")
		{
			$f3->set('CSS_UI', "{$UI_BASE}{$tpl}/");
			$UI = "{$UI_BASE}{$tpl}/,";
		}
		else $f3->set('CSS_UI', "{$UI_BASE}default/");
		$UI .= "{$UI_BASE}default/";
		$f3->set('UI', $UI);
		
		$f3->set('SELF', rawurlencode($_SERVER["QUERY_STRING"]));

		\View\Base::javascript('body', TRUE, 'global.js' );
		\View\Base::javascript('body', FALSE, "var base='{$f3->get('BASE')}'" );
	}
	
	public function javascript($location, $file=FALSE, $string)
	{
		$f3 = \Base::instance();
		if($file)
		{
			$this->JS[$location][] = (strpos($string,"//")===0)
																? "<script src=\"{$string}\"></script>"
																: "<script src=\"".$f3->get('BASE')."/app/js/{$string}\"></script>";
		}
		else $this->JS[$location][] = "<script type=\"text/javascript\">{$string}</script>";
	}
	
	public function addTitle($string)
	{
		 $this->title[] = $string;
	}
	
	public static function render($file,$mime='text/html',array $hive=NULL,$ttl=0)
	{
		return "<!-- FILE: {$file} -->".\Template::instance()->render($file,$mime,$hive,$ttl)."<!-- END: {$file} -->";
	}
	
	public static function stub($text="")
	{
		return \Template::instance()->render('stub.html');
	}
	
}

class Iconset extends \DB\Jig\Mapper {
	
	public function __construct()
	{
		$db = new \DB\Jig('tmp/');
		parent::__construct($db,"iconset.{$_SESSION['tpl'][1]}.json");
		$this->load();
	}
	
	static public function instance()
	{
		if (\Registry::exists('ICONSET'))
			return \Registry::get('ICONSET');
		else
		{
			$icon = new self;
			if ( empty($icon->_name) ) $icon = self::rebuild($icon);
			\Registry::set('ICONSET',$icon);
			return $icon;
		}
	}
	
	static public function parse($key)
	{
		list(, $label, $visibility, $text) = $key;
		if (empty($label)) return NULL;
		return str_replace("@T@", $text, self::instance()->{$label});
	}
	
	static protected function rebuild($icon)
	{
		$set = $_SESSION['tpl'][1];
		$sql = "SELECT `name`, `value` FROM `tbl_iconsets` WHERE `set_id` = {$set}";
		$db = \Model\Base::instance();
		$data = $db->exec($sql);
		foreach ( $data as $item )
		{
			if(strpos($item["name"],"#")===0)
			{
				if ( $item["name"]=="#pattern" && $item['value']!=NULL )
					$pattern = $item["value"];
				elseif ( $item["name"]=="#directory" && $item['value']!=NULL )
					$pattern = "<img src=\"{$BASE}/template/iconset/{$item['value']}/@1@\" >";
				if ( $item["name"]=="#name" )
					$icon->_name = $item['value'];
			}
			else
			{
				if(strpos($item["name"],",")!==0)
				{
					$names = explode(",",$item['name']);
					foreach ( $names as $name )
						$icon->{$name} = str_replace("@1@",$item["value"],$pattern);
				}
				else
					$icon->{$item['name']} = str_replace("@1@",$item["value"],$pattern);
			}
		}
		$icon->save();
		return $icon;
	}
}
