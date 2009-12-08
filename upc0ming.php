<?php
/*
Plugin Name: upc0ming
Plugin URI: http://wordpress.org/#
Description: list upcoming events on a publicly accessible google calendar
Author: Oliver C Dodd
Version: 1.1.1
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
	private $limit;
	private $linkTo;
	private $divid;
	private $css;
	
	const DEFAULT_CSS = '
#upc0ming {
	padding:5px 20px 5px 20px;
}
.upc0mingEvent {
	font-family:Times New Roman;
	min-height:80px;
	height:auto !important;
	height:80px;
}
.upc0mingEvent:after {
	display: block;
	height:0px;
	clear:right;
	content:" ";
}
.upc0mingEvent .calEntry {
	position:relative;
	float:left;
	width:50px;
	text-align:center;
	border:solid black 2px;
	margin:3px 3px 10px 10px;
	pading-right:10px;
}
.upc0mingEvent .calEntry .calYear {
	color:#FFFFFF;
	background-color:#000000;
	font-size:8px;
	line-height:8px;
	padding-left:7px;
	letter-spacing:7px;
}
.upc0mingEvent .calEntry .calMonth {
	color:#000000;
	background-color:#b0b0b0;
	font-size:11px;
	line-height:12px;
	letter-spacing:5px;
	padding-left:5px;
	font-weight:bold;
}
.upc0mingEvent .calEntry .calDay {
	color:#000000;
	background-color:#f0f0f0;
	font-size:24px;
	line-height:24px;
	font-weight:bold;
}
.upc0mingEvent .calEntry .calTime {
	color:#000000;
	background-color:#ffffff;
	font-size:9px;
	line-height:10px;
}
.upc0mingEvent .event {
	position:relative;
	clear:right;
	padding:10px 0 0 10px;
 }
.upc0mingEvent .event .eventWhat {
	font-size:1.2em;
	font-weight:bold;
}
.upc0mingEvent .event .eventWhere {
	color:#404040;
	font-size:1em;
	font-style:italic;
}';
	
	/*-CONSTRUCT----------------------------------------------------------*/
	//public function __construct($u,$t="upc0ming",$d="")
	public function upc0ming($options)
	{
		foreach($options as $k => $v)
			@($this->$k = $v);
	}
	
	private function url($futureOnly)
	{
		return "http://www.google.com/calendar/feeds/".
			urlencode($this->user).
			"/public/full/".
			($futureOnly ? "?futureevents=true" : "");
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
		$xml = $this->get($this->url($futureOnly));
		$doc = new DOMDocument();
		if (!$xml||!$doc->loadXML($xml)) return array();
		
		$events = array();
		$entries = $doc->getElementsByTagName('entry');
		$i = 0;
		foreach ($entries as $event) {
			if (++$i && $this->limit > $this->limit)
				break;
			$author = $event->getElementsByTagName('author')->item(0);
			$when = $event->getElementsByTagName('when')->item(0);
			$start = $when->getAttribute('startTime');
			$timestamp = strtotime($start);
			$events["$timestamp.$i"] = array(
				'who'		=> $this->tagValue($author,'name'),
				'what'		=> $this->tagValue($event,'title'),
				'info'		=> $this->tagValue($event,'content'),
				'where'		=> $this->attributeValue($event,
							'where','valueString'),
				'when'		=> $start,
				'start'		=> $start,
				'end'		=> $when->getAttribute('endTime'),
				'timestamp'	=> strtotime($start),
				'link'		=> $this->attributeValue($event,
							'link','href',
							array('rel'=>'alternate'))
			);
		}
		ksort($events);
		return $events;
	}
	
	/*-XML PARSING SPECIFICS----------------------------------------------*/
	public function tagValue($node,$tag,$requiredAttributes=array(),$valueIfNoChild=false)
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
	
	private function attributeValue($node,$tag,$attribute,$requiredAttributes=array())
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
				'limit'		=> "",
				'linkTo'	=> false,
				'divid'		=> "",
				'css'		=> self::DEFAULT_CSS)
			: $options;
	}
	
	/*-MAKE OPTIONS-------------------------------------------------------*/
	public function makeOptions($a,$s="")
	{
		$options = "";
		foreach ($a as $o) {
			$sel = $o == $s ? " selected='selected' " : "";
			$options .= "<option$sel>$o</o>";
		}
		return $options;
	}
	
	/*-OUTPUT EVENTS------------------------------------------------------*/
	public function outputEvents()
	{
		$events = $this->getEvents();
		$html = "<style type='text/css'>$this->css</style>";
		foreach ($events as $event) {
			$t = $event['timestamp'];
			$y = date('Y',$t);
			$m = strtoupper(date('M',$t));
			$d = date('j',$t);
			$ts = date('g:i a',$t);
			
			$eventName = "
			<div class='event'>
				<div class='eventWhat'>{$event['what']}</div>
				<div class='eventWhere'>{$event['where']}</div>
			</div>";
			$eventName = $this->linkTo
				? "<a href='{$event['link']}' target='_blank'>$eventName</a>"
				: $eventName;
			
			$html .= "
			<div class='upc0mingEvent'>
				<div class='calEntry'>
					<div class='calYear'>$y</div>
					<div class='calMonth'>$m</div>
					<div class='calDay'>$d</div>
					<div class='calTime'>$ts</div>
				</div>
				$eventName
			</div>";
		}
		return $html;
	}
}
/*-OPTIONS--------------------------------------------------------------------*/
function widget_upc0ming_options()
{
	$options = upc0ming::getOptions();
	if($_POST['upc0ming-submit'])
	{
		$options = array(	'user'	=> $_POST['upc0ming-user'],
					'title'	=> $_POST['upc0ming-title'],
					'linkTo'=> $_POST['upc0ming-linkTo'],
					'limit'	=> $_POST['upc0ming-limit'],
					'divid'	=> $_POST['upc0ming-divid'],
					'css'	=> $_POST['upc0ming-css']);
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
	<p>	Link To Event?
		<input	type="checkbox"
			name="upc0ming-linkTo"
			id="upc0ming-linkTo"
			<?php echo $options['linkTo'] ? 'checked="checked"' : ""; ?>
			value="1"  />
	</p>
	<p>	Limit Events (blank for no limit):
		<input	type="text"
			name="upc0ming-limit"
			id="upc0ming-limit"
			value="<?php echo $options['limit']; ?>"  />
	</p>
	<p>	Wrapper Div ID (blank for no div):
		<input	type="text"
			name="upc0ming-divid"
			id="upc0ming-divid"
			value="<?php echo $options['divid']; ?>"  />
	</p>
	<p>	CSS (blank if you want to add it to your theme css, defaults provided):
		<br />
		<textarea
			name="upc0ming-css"
			id="upc0ming-css"
			cols="40"
			rows="20"><?php echo $options['css']; ?></textarea>
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
		$u = new upc0ming($options);
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
