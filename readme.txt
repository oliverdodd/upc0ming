=== upc0ming ===
Contributors: 01001111
Tags: widget, calendar, google, events, social
Requires at least: 2.0.2
Tested up to: 2.7
Stable tag: trunk

Display the upcoming events on a publicly accessible google calendar.

== Description ==

The upc0ming widget/plugin to show all the upcoming events booked on a Google Calendar that is publicly readable.  This doesn't need to be your default Google Calendar, you can create new calendars in which you can share only a subset of your information.

== Installation ==

1. Upload 'upc0ming.php' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the widget in the widget section.

To use as a plugin in your theme, include the following:
`<php	$u = new upc0ming($user,$title,$divid);
	echo $u->outputEvents(); ?>`

And set the appropriate variables or leave blank for defaults ($user, the calendar id, is required).

The configuration parameters are:

* Google Calendar User / ID:  The calendar id or your usename / email adress.
* Title: The title of this section.
* Wrapper Div ID: The id for the widget's wrapper div for your CSS styling convenience.  Leave blank to omit the div wrapper entirely.
* CSS: Inline CSS for convenience.  Default CSS can be found in the plugin code or in the FAQ.


== Frequently Asked Questions ==

= I created a new Google Calendar and set it to public, how can I find the user name / id? =

Go to the Calendar Settings section of Google Calendar, select the calendar you created, and find the Calendar ID (should look something like: tjrr8m4e623kue9om8f31e9408@group.calendar.google.com) under the Calendar Address section on the Calendar Details Tab.

= How can I configure the way the dates and event information is rendered? =

I haven't built in any sophisticated configuration of the visual elements as of yet but you can do a couple things to tweak the output:

* Alter the CSS either in the plugin configuration or in your theme.  A simple viewing of the source should be enough to show you how the output is structured.

* Alter the source code of the plugin itself.

I may get around to enabling a template for the output and some date configuration.

= The output looks awful, what's going on? =

You probably don't have the default CSS included, see below.

= I irrevocably ruined my CSS, how can I get the default back? =

It's embedded in the code or you can copy/paste from below:

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
}

== Screenshots ==

None at the moment.
