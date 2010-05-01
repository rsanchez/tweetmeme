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
Extension Usage:
This addon can adds a tag to your weblog entries loop which displays the tweetmeme retweet button.

{exp:weblog:entries weblog="weblog"}
{tweetmeme}
{/exp:weblog:entries}

Your default parameters are stored in the extension settings. You may override your set defaults with tag parameters, like so:
{exp:weblog:entries weblog="weblog"}
{tweetmeme type="compact" style="border:10px solid red;"}
{/exp:weblog:entries}

In the extension settings, you can specify a "site path" for each of your weblogs, which is a path to your single-entry template. This will enable the extension to "know" the entry's URL.

If you don't specify a site path for your weblog in the extension settings, you must specify one of the "path" parameters (either url, entry_id_path, url_title_path, title_permalink, or path) in the tag parameters. Also, if you do not specify a site path for your weblog, you will not be able to track your stats in the control panel.

{exp:weblog:entries weblog="weblog"}
{tweetmeme entry_id_path="site/view"}
{/exp:weblog:entries}


Plugin Usage:
You can also use as a standalone plugin, outside of a weblog:entries loop like this:
{exp:tweetmeme path="site/index"}

If you are using the standalone plugin, you must specify an entry_id if you want to be able to use the entry_id_path, url_title_path or title_permalink
parameters.
{exp:tweetmeme entry_id="{segment_3}" url_title_path="site/view"}


Parameters:
path - a site path, eg {exp:tweetmeme path="site/about"}
entry_id_path - a path to be appended by the entry_id, e.g. {tweetmeme entry_id_path="site/view" entry_id="{segment_3}"}
url_title_path - a path to be appended by the url_title, e.g. {tweetmeme url_title_path="site/view" entry_id="{segment_3}"}
title_permalink - an alias of url_title_path
url - a full url, e.g. {exp:tweetmeme url="http://whatevs.com"}
style (optional) - style for the button-containing div
type (optional) - 'compact' or 'large', default is compact
source (optional) - your twitter username, will show up as RT @username
url_shortener (optional) - choose a url shortening service
api_key (optional) - awe.sm requires an api key
spaces (optional) - spaces to leave at end of tweet
hashtags (optional) - comma separated list of topics to add to the tweet, you can use a custom field here, e.g. {tweetmeme hashtags="ee,tweetmeme"} or {tweetmeme hashtags="{my_tags}"}
<?php
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}
}

/* End of file pi.tweetmeme.php */
/* Location: ./system/plugins/pi.tweetmeme.php */