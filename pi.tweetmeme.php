<?php

$plugin_info = array(
	'pi_name' => 'TweetMeme Retweet Button',
	'pi_version' => '1.0.0',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'http://barrettnewton.com/',
	'pi_description' => 'Adds a button which easily lets you retweet your blog posts.',
	'pi_usage' => Tweetmeme::usage()
);

class Tweetmeme
{
	var $return_data = '';

	function Tweetmeme()
	{
		global $TMPL, $DB;
		
		if ( ! class_exists('Tweetmeme_ext'))
		{
			require_once(PATH_EXT.'tweetmeme_ext'.EXT);
		}
		
		$this->ext = new Tweetmeme_ext();
		
		$this->ext->button_params = array_merge($this->ext->button_params, $TMPL->tagparams);
		
		if ($TMPL->fetch_param('entry_id'))
		{
			$query = $DB->query("SELECT * FROM exp_weblog_titles WHERE entry_id = '".$DB->escape_str($TMPL->fetch_param('entry_id'))."' LIMIT 1");
			
			$this->ext->entry_data = $query->row;
		}
		
		$this->return_data = $this->ext->_get_button();
		
		unset($this->ext);
        str_repla

	}

	function usage()
	{
		ob_start(); 
?>
This addon can be used as a standalone plugin, like this:
{exp:tweetmeme url="{path=site/index}"}

or within an entries loop as a single tag, like this:
{exp:weblog:entries weblog="blog"}
{tweetmeme hashtags="my,topics"}
{/exp:weblog:entries}

Your default parameters are stored in the extension settings. You may override your set defaults with tag parameters.

For each of your weblogs/channels, you can specify a site path . If you don't specify a site path for your weblog/channel in the extension settings, you must specify a path (either url, entry_id_path, url_title_path, title_permalink, or path) in the tag parameters. Also, if you do not specify a site path for your weblog/channel, you will not be able to track your stats in the control panel.

If you are using the standalone plugin, you must specify an entry_id if you want to be able to use the entry_id_path, url_title_path or title_permalink parameters.
{exp:tweetmeme entry_id="{segment_3} url_title_path="site/view"}

If you are using the single tag within a weblog/channel entries loop and you have specified a site path in the extension settings, you DO NOT need to specify any kind of url/path in the tag.
{exp:weblog:entries weblog="blog"}
{tweetmeme}
{/exp:weblog:entries}

Parameters:
url - a full url, e.g. {exp:tweetmeme url="{path=site/index}"}
entry_id
entry_id_path - a path to be appended by the entry_id, e.g. {tweetmeme entry_id_path="site/view"}
url_title_path - a path to be appended by the url_title, e.g. {tweetmeme url_title_path="site/view"}
title_permalink - an alias of url_title_path
path - a site path, eg {exp:tweetmeme path="site/about"}
style - style for the button-containing div
type - 'compact' or 'large', default is compact
source - your twitter username, will show up as RT @username
url_shortener - choose a url shortening service
api_key - awe.sm requires an api key
spaces - spaces to leave at end of tweet
hashtags - comma separated list of topics to add to the tweet, you can use a custom field here, e.g. {tweetmeme hashtags="ee,tweetmeme"} or {tweetmeme hashtags="{my_tags}"}
<?php
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}
}

/* End of file pi.tweetmeme.php */
/* Location: ./system/plugins/pi.tweetmeme.php */