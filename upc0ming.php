<?php
/*
Plugin Name: upc0ming
Plugin URI: http://wordpress.org/#
Description: list upcoming events via a publicly accessible google calendar
Author: Oliver C Dodd
Version: 1.0.0
Author URI: http://01001111.net
  
  Copyright (c) 2009 Oliver C Dodd - http://01001111.net
  
  Much of the functionality is taken from the free 01001111 library
  
  *NOTE: your calendar must be publicly viewable
  
  Permission is hereby granted,free of charge,to any person obtaining a 
  copy of this software and associated documentation files (the "Software"),
  to deal in the Software without restriction,including without limitation
  the rights to use,copy,modify,merge,publish,distribute,sublicense,
  and/or sell copies of the Software,and to permit persons to whom the 
  Software is furnished to do so,subject to the following conditions:
  
  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.
  
  THE SOFTWARE IS PROVIDED "AS IS",WITHOUT WARRANTY OF ANY KIND,EXPRESS OR
  IMPLIED,INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,DAMAGES OR OTHER
  LIABILITY,WHETHER IN AN ACTION OF CONTRACT,TORT OR OTHERWISE,ARISING
  FROM,OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
  DEALINGS IN THE SOFTWARE.
*/
class upc0ming
{
	/*-VARIABLES----------------------------------------------------------*/
	private $user;
	public $title;
	private $divid;
	
	/*-CONSTRUCT----------------------------------------------------------*/
	//public function __construct($u,$t="upc0ming",$d="")
	public function upc0ming($u,$t="upc0ming",$d="")
	{
		$this->user	= $u;
		$this->title	= $s;
		$this->divid	= $d;
	}
	
	private function url()
	{
		return "http://www.google.com/calendar/feeds/$this->user".
			"/public/full/?futureevents=true";
	}
	
	private function get($url)
	{
		return @file_get_contents($url);
	}
	
	private function queryString($args)
	{
		if (!is_array($args))
			return $args;
		$pairs = "";
		foreach ($args as $k => $v)
			$pairs[] = "$k=$v";
		return implode('&',$pairs);
	}
	
	/*-EVENTS-------------------------------------------------------------*/
	public function getEvents($futureOnly=true)
	{
		$xml = $this->get($this->url());
		$doc = new DOMDocument();
		if (!$xml||!$doc->loadXML($xml)) return array();
		
		$events = array();
		$entries = $doc->getElementsByTagName('entry');
		foreach ($entries as $event) {
			$author = $event->getElementsByTagName('author')->item(0);
			$when = $event->getElementsByTagName('when')->item(0);
			$start = $when->getAttribute('startTime');
			$events[] = array(
				'who'		=> self::tagValue($author,'name'),
				'what'		=> self::tagValue($event,'title'),
				'info'		=> self::tagValue($event,'content'),
				'where'		=> self::attributeValue($event,
							'where','valueString'),
				'when'		=> $start,
				'start'		=> $start,
				'end'		=> $when->getAttribute('endTime'),
				'timestamp'	=> strtotime($start),
				'link'		=> self::attributeValue($event,
							'link','href',
							array('rel'=>'alternate'))
			);
			
		}
		return $events;
	}
	
	/*-XML PARSING SPECIFICS----------------------------------------------*/
	public static function tagValue($node,$tag,$requiredAttributes=array(),$valueIfNoChild=false)
	{
		if (!$requiredAttributes) {
			$children = $node->getElementsByTagName($tag);
			return $children->length
				? $children->item(0)->nodeValue
				: ($valueIfNoChild ? $node->nodeValue : "");
		}
		//get tags
		$tags = $node->getElementsByTagName($tag);
		//check attributes
		$element = false;
		for ($i = 0; $i < $tags->length; $i++) {
			$found = true;
			foreach ($requiredAttributes as $k => $v)
				$found &= strcasecmp($tags->item($i)->getAttribute($k),$v) == 0;
			if ($found) {
				$element = $tags->item($i);
				break;
			}
		}
		return $element ? $element->nodeValue : "";
	}
	
	private static function attributeValue($node,$tag,$attribute,$requiredAttributes=array())
	{
		if (!$requiredAttributes) {
			$children = $node->getElementsByTagName($tag);
			return $children->length
				? $children->item(0)->getAttribute($attribute)
				: "";
		}
		//get tags
		$tags = $node->getElementsByTagName($tag);
		//check attributes
		$element = false;
		for ($i = 0; $i < $tags->length; $i++) {
			$found = true;
			foreach ($requiredAttributes as $k => $v)
				$found &= strcasecmp($tags->item($i)->getAttribute($k),$v) == 0;
			if ($found) {
				$element = $tags->item($i);
				break;
			}
		}
		return $element ? $element->getAttribute($attribute) : "";
	}
	
	/*-GET OPTIONS--------------------------------------------------------*/
	public static function getOptions()
	{
		return !($options = get_option('upc0ming'))
			? $options = array(
				'user'		=> "",
				'title'		=> "upc0ming",
				'divid'		=> "")
			: $options;
	}
	
	/*-MAKE OPTIONS-------------------------------------------------------*/
	public static function makeOptions($a,$s="")
	{
		$options = "";
		foreach ($a as $o) {
			$sel = $o == $s ? " selected='selected' " : "";
			$options .= "<option$sel>$o</o>";
		}
		return $options;
	}
	
	/*-OUTPUT EVENTS------------------------------------------------------*/
	public static function outputEvents()
	{
		$events = $this->getEvents();
		$html = "";
		foreach ($events as $event) {
			$t = $event['timestamp'];
			$y = date('Y',$t);
			$m = strtoupper(date('M',$t));
			$d = date('j',$t);
			$ts = date('g:i a',$t);
			
			$html .= "
			<div class='upc0mingEvent'>
				<div class='upc0mingEntry'>
					<div class='upc0mingYear'>$y</div>
					<div class='upc0mingMonth'>$m</div>
					<div class='upc0mingDay'>$d</div>
					<div class='upc0mingTime'>$ts</div>
				</div>
				<div class='event'>
					<div class='eventWhat'>{$event['what']}</div>
					<div class='eventWhere'>{$event['where']}</div>
				</div>
			</div>";
		}
	}
}
/*-OPTIONS--------------------------------------------------------------------*/
function widget_upc0ming_options()
{
	$options = upc0ming::getOptions();
	if($_POST['upc0ming-submit'])
	{
		$options = array(	'user'		=> $_POST['upc0ming-user'],
					'title'		=> $_POST['upc0ming-title'],
					'divid'		=> $_POST['upc0ming-divid']);
		update_option('upc0ming',$options);
	}
	?>
	<p>	Google Calendar User / ID:
		<input	type="text"
			name="upc0ming-user"
			id="upc0ming-user"
			value="<?php echo $options['user']; ?>"  />
	</p>
	<p>	Title:
		<input	type="text"
			name="upc0ming-title"
			id="upc0ming-title"
			value="<?php echo $options['title']; ?>"  />
	</p>
	<p>	Wrapper Div ID (blank for no div):
		<input	type="text"
			name="upc0ming-divid"
			id="upc0ming-divid"
			value="<?php echo $options['divid']; ?>"  />
	</p>
	<input type="hidden" id="upc0ming-submit" name="upc0ming-submit" value="1" />
	<?php
}
/*-WIDGETIZE------------------------------------------------------------------*/
function widget_upc0ming_init()
{
	if (!function_exists('register_sidebar_widget')) { return; }
	function widget_upc0ming($args)
	{
		extract($args);
		$options = upc0ming::getOptions();
		$u = new upc0ming(	$options['user'],
					$options['title'],
					$options['divid']);
		echo "	$before_widget
			$before_title $u->title $after_title
				{$u->outputEvents()}
			$after_widget
		";
	}
	register_sidebar_widget('upc0ming','widget_upc0ming');
	register_widget_control('upc0ming','widget_upc0ming_options');
}
add_action('plugins_loaded', 'widget_upc0ming_init');
?>
