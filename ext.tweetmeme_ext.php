<?php

class Tweetmeme_ext
{
	var $name = 'TweetMeme Retweet Button';
	var $version = '1.0.0';
	var $description = 'Adds a button which easily lets you retweet your blog posts. A port of the official TweetMeme Wordpress plugin.';
	var $settings_exist = 'y';
	var $docs_url = '';
	var $classname = 'Tweetmeme_ext';
	var $settings = array();
	var $button_params = array();
	var $entry_data = array();
	
	function Tweetmeme_ext($settings='')
	{
		global $LANG;
		
		$this->settings = $settings;
		
		if ( ! $this->settings)
		{
			$this->load_settings();
		}
		
		$LANG->fetch_language_file('tweetmeme_ext');
		
		$this->button_params = array(
			'style' => $this->setting('tm_style'),
			'type' => $this->setting('tm_version'),
			'source' => $this->setting('tm_source'),
			'url_shortener' => $this->setting('tm_url_shortener'),
			'api_key' => $this->setting('tm_api_key'),
			'spaces' => $this->setting('tm_space'),
			'hashtags' => $this->setting('tm_hashtags')
		);
	}

/*
 * Core Extension Functions
 */
	
	function activate_extension()
	{
		global $DB;
		
		$DB->query($DB->insert_string(
			'exp_extensions',
			array(
				'class' => $this->classname,
				'method' => 'weblog_entries_tagdata',
				'hook' => 'weblog_entries_tagdata',
				'priority' => 10,
				'version' => $this->version,
				'enabled' => 'y'
			)
		));
		
		$DB->query($DB->insert_string('exp_extensions',
			array(
				'class' => $this->classname,
				'method' => 'edit_entries_additional_celldata',
				'hook' => 'edit_entries_additional_celldata',
				'priority' => 1,
				'version' => $this->version,
				'enabled' => 'y'
			)
		));
		
		$DB->query($DB->insert_string('exp_extensions',
			array(
				'class' => $this->classname,
				'method' => 'edit_entries_additional_tableheader',
				'hook' => 'edit_entries_additional_tableheader',
				'priority' => 1,
				'version' => $this->version,
				'enabled' => 'y'
			)
		));
		
		$DB->query($DB->insert_string('exp_extensions',
			array(
				'class' => $this->classname,
				'method' => 'edit_entries_extra_actions',
				'hook' => 'edit_entries_extra_actions',
				'priority' => 1,
				'version' => $this->version,
				'enabled' => 'y'
			)
		));
		
		$DB->query($DB->insert_string(
			'exp_extensions',
			array(
				'class' => $this->classname,
				'method' => 'submit_new_entry_absolute_end',
				'hook' => 'submit_new_entry_absolute_end',
				'settings' => '',
				'priority' => 10,
				'version' => $this->version,
				'enabled' => 'y'
			)
		));
	}
	
	function update_extension($current = '')
	{
		global $DB;
		
		if ($current == '' || $current == $this->version)
		{
			return FALSE;
		}
		
		$DB->query($DB->update_string(
			'exp_extensions',
			array('version' => $this->version),
			array('class' => $this->classname)
		));
	}
	
	function disable_extension()
	{
		global $DB;
	
		$DB->query("DELETE FROM exp_extensions WHERE class = '".$this->classname."'");
	}
	
	function settings()
	{
		global $DB, $PREFS, $SESS;
		
		$settings = array(
			'tm_style' => 'float: right; margin-left: 10px;',
			'tm_version' => array(
				'r',
				array(
					'large' => 'tm_large',
					'compact' => 'tm_compact'
				),
				'large'
			),
			'tm_source' => '',
			'tm_ping' => array(
				'r',
				array(
					'1' => 'tm_yes',
					'0' => 'tm_no'
				),
				'1'
			),
			/*
			'tm_hashtags' => array(
				'r',
				array(
					'1' => 'tm_use_hash_tags',
					'0' => 'tm_dont_use_hash_tags'
				),
				'0'
			),
			*/
			//'tm_hashtags_field' => '',
			'tm_hashtags' => '',
			'tm_url_shortner' => array( 
				's',
				array(
					'0' => 'Default',
					'bit.ly' => 'bit.ly',
					'awe.sm' => 'awe.sm',
					'cli.gs' => 'cli.gs',
					'digg.com' => 'digg.com',
					'is.gd' => 'is.gd',
					'TinyURL.com' => 'TinyURL',
					'ow.ly' => 'ow.ly',
					'retwt.me' => 'retwt.me'
				),
				'0'
			),
			'tm_api_key' => '',
			'tm_space' => ''
		);
		
		if ( ! isset($SESS->cache['tweetmeme']['templates']))
		{
			$query = $DB->query("SELECT t.template_name, g.group_name
					    FROM exp_templates t
					    JOIN exp_template_groups g
					    ON g.group_id = t.group_id
					    WHERE t.site_id = '".$PREFS->ini('site_id')."'");
		
			$SESS->cache['tweetmeme']['templates'] = $query->result;
		}
		
		if ( ! isset($SESS->cache['tweetmeme']['weblogs']))
		{
			$query = $DB->query("SELECT weblog_id, blog_title FROM exp_weblogs WHERE site_id = '".$PREFS->ini('site_id')."'");
		
			$SESS->cache['tweetmeme']['weblogs'] = $query->result;
		}
		
		foreach ($SESS->cache['tweetmeme']['weblogs'] as $row)
		{
			//$settings['tm_path_'.$row['weblog_id']] = '';
			
			$settings['tm_path_'.$row['weblog_id']] = array(
				's',
				array(
					0 => '--'
				),
				0
			);
			
			foreach ($SESS->cache['tweetmeme']['templates'] as $template)
			{
				$settings['tm_path_'.$row['weblog_id']][1][$template['group_name'].'/'.$template['template_name']] = $template['group_name'].'/'.$template['template_name'];
			}
			
			$settings['tm_use_url_title_'.$row['weblog_id']] = array(
				'r',
				array(
					'0' => 'tm_entry_id',
					'1' => 'tm_url_title'
				),
				'0'
			);
		}
		
		return $settings;
	}
	
	function save_settings()
	{
		global $IN, $DB;
		
		$insert = array();
		
		foreach($this->settings() as $key => $value)
		{
			if ( ! is_array($value))
			{
				$insert[$key] = ($IN->GBL($key, 'POST') !== FALSE) ? $IN->GBL($key, 'POST') : $value;
			}
			elseif (is_array($value) && isset($value['1']) && is_array($value['1']))
			{
				if(is_array($IN->GBL($key, 'POST')) OR $value[0] == 'ms')
				{
					$data = (is_array($IN->GBL($key, 'POST'))) ? $IN->GBL($key, 'POST') : array();
					
					$data = array_intersect($data, array_keys($value['1']));
				}
				else
				{
					if ($IN->GBL($key, 'POST') === FALSE)
					{
						$data = ( ! isset($value['2'])) ? '' : $value['2'];
					}
					else
					{
						$data = $IN->GBL($key, 'POST');
					}
				}
				
				$insert[$key] = $data;
			}
			else
			{
				$insert[$key] = ($IN->GBL($key, 'POST') !== FALSE) ? $IN->GBL($key, 'POST') : '';
			}
		}
		
		$DB->query("UPDATE exp_extensions SET settings = '".addslashes(serialize($insert))."' WHERE class = '".$DB->escape_str($IN->GBL('name'))."'");
	}
	
	function settings_form($current = FALSE)
	{
		global $DB, $DSP, $FNS, $IN, $LANG, $PREFS, $SESS;
		
		if ($IN->GBL('tm_analytics'))
		{
			exit($this->ajax_elev_lookup());
		}
		
		if ( ! class_exists('Utilities'))
		{
			require_once(PATH_CORE.'core.utilities'.EXT);
		}
		
		$DSP->crumbline = TRUE;
		
		$DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));
		
		$name = $LANG->line('tm_name');
    	
		$r = $DSP->table('', '', '', '100%')
		.$DSP->tr()
		.$DSP->td('default', '', '', '', 'top')
		.$DSP->heading($LANG->line('extension_settings'));
             
		$qm		= ($PREFS->ini('force_query_string') == 'y') ? '' : '?';

		$r .= $DSP->td_c()
			.$DSP->td('default', '', '', '', 'middle')
			.$DSP->qdiv('defaultRight', '<strong>'.'</strong>'.NBS.NBS)
			.$DSP->td_c()
			.$DSP->tr_c()
			.$DSP->tr()
			.$DSP->td('default', '100%', '2', '', 'top');
		
		if ( ! isset($SESS->cache['tweetmeme']['weblogs']))
		{
			$query = $DB->query("SELECT weblog_id, blog_title FROM exp_weblogs WHERE site_id = '".$PREFS->ini('site_id')."'");
		
			$SESS->cache['tweetmeme']['weblogs'] = $query->result;
		}
		
		foreach ($SESS->cache['tweetmeme']['weblogs'] as $row)
		{
			$LANG->language['tm_path_'.$row['weblog_id']] = $row['blog_title'].$LANG->line('tm_path');
			$LANG->language['tm_use_url_title_'.$row['weblog_id']] = $row['blog_title'].$LANG->line('tm_path_type');
		}
             
		$r .= Utilities::extension_settings_form($name, $IN->GBL('name'), $this->settings(), $current);
             
		$r .=  $DSP->td_c()
			.$DSP->tr_c()
			.$DSP->table_c()
			.$DSP->td_c()
			.$DSP->tr_c()
			.$DSP->table_c();
			  
		$DSP->title  = $LANG->line('extension_settings');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
		$DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));
		$DSP->crumb .= $DSP->crumb_item($name);
		
		$DSP->body = $r;
	}

/**
* Private Utilities
*/
	function load_settings()
	{
		global $EXT;
		
		if (isset($EXT->s_cache['Tweetmeme_ext']))
		{
			$this->settings = $EXT->s_cache['Tweetmeme_ext'];
		}
	}
	
	function setting($key)
	{
		return (isset($this->settings[$key])) ? $this->settings[$key] : FALSE;
	}
	
	function entry_data($key)
	{
		return (isset($this->entry_data[$key])) ? $this->entry_data[$key] : FALSE;
	}
	
	function button_param($key)
	{
		return (isset($this->button_params[$key])) ? $this->button_params[$key] : FALSE;
	}

/**
* TweetMeme Utilities
*/
	/**
	* Get the stats for a URL
	*/
	function ajax_elev_lookup()
	{
		global $IN;
		// read submitted information
		$url = $IN->GBL('url');
		// fetch the data from tweetmeme
		$json = $this->get_tweets($url);
		// get the data
		$data = $json['data'];
		// build up the output
		ob_start();
		?>
		<table cellpadding="0" cellspacing="0" style="width:100%;">
			<tr>
				<th>Tweets in 1 Hour</th>
				<th>Tweets in 24 Hours</th>
				<th>Top Sources in 1 Hour</th>
			</tr>
			<tr>
				<td>
					<img src="<?php echo $data['hourChart']; ?>" alt="*" />
				</td>
				<td>
					<img src="<?php echo $data['dayChart']; ?>" alt="*" />
				</td>
				<td>
					<img src="<?php echo $data['sources']; ?>" alt="*" />
				</td>
			</tr>
		</table>
		<?php if (count($data['users']) > 0) { ?>
			<table cellpadding="0" cellspacing="0" style="width:100%;">
				<tr>
					<th>Tweeter</th>
					<th>Retweet Of</th>
					<th></th>
				</tr>
				<?php foreach ($data['users'] as $user) { ?>
					<tr>
						<td><a href="http://tweetmeme.com/user/<?php echo $user['tweeter'] ?>" target="_blank"><?php echo $user['tweeter'] ?></a></td>
						<td><?php if ($user['isRT']) { ?>
							<a href="http://tweetmeme.com/user/<?php echo $user['RTUser'] ?>" target="_blank"><?php echo $user['RTUser'] ?></a>
						<?php } ?></td>
						<td><a href="http://twitter.com/<?php echo $user['tweeter'] ?>/status/<?php echo $user['tweetid'] ?>" target="_blank">View</a></td>
					</tr>
				<?php } ?>
			</table>
		<?php }  ?>
			<a href="javascript:void(0);" onclick="tm_hide_analytics();" style="float:right;">Hide Stats</a>
			<?php
	
		return ob_get_clean();
	}
	
	/**
	* Build up all the params for the button
	*/
	function build_button_options()
	{
		$button = '?url='.urlencode($this->get_url());
	
		// now build up the params, start with the source
		if ($this->setting('tm_source'))
		{
			$button .= '&amp;source='.urlencode($this->setting('tm_source'));
		}
	
		// which style
		if ($this->setting('tm_version') == 'compact')
		{
			$button .= '&amp;style=compact';
		}
		else
		{
			$button .= '&amp;style=normal';
		}
	
		// what shortner to use
		if ($this->setting('tm_url_shortner'))
		{
			$button .= '&amp;service='.urlencode($this->setting('tm_url_shortner'));
		}
	
		// does the shortner have an API key
		if ($this->setting('tm_api_key'))
		{
			$button .= '&amp;service_api='.urlencode($this->setting('tm_api_key'));
		}
	
		// how many spaces do we want to leave at the end
		if ($this->setting('tm_space'))
		{
			$button .= '&amp;space='.$this->setting('tm_space');
		}
	
		// append the hashtags
		if ($this->button_param('hashtags'))
		{
			// first split them out
			$hashtags = explode(',', $this->button_param('hashtags'));
			// go through and urlencode
			foreach($hashtags as $row => $tag) {
				$hashtags[$row] = urlencode(trim($tag));
			}
			// add them all back together
			$button .= '&amp;hashtags='.implode(',', $hashtags);
		}
		// return all the params
		return $button;
	}
	
	/**
	* Generate the iFrame render of the button
	*/
	function generate_button() {
		// build up the outer style
		$button = '<div class="tweetmeme_button" style="'.$this->setting('tm_style').'">';
		$button .= '<iframe src="http://api.tweetmeme.com/button.js'.$this->build_button_options().'" ';
	
		// give it a height, dependant on style
		if ($this->setting('tm_version') == 'compact')
		{
			$button .= 'height="20" width="90"';
		}
		else
		{
			$button .= 'height="61" width="50"';
		}
		// close off the iframe
		$button .= ' frameborder="0" scrolling="no"></iframe></div>';
		// return the iframe code
		return $button;
	}
	
	/**
	* Generates the image button
	*/
	function generate_static_button()
	{
		return
			'<div class="tweetmeme_button" style="'.$this->setting('tm_style').'">
				<a href="http://api.tweetmeme.com/share?url='.urlencode($this->get_url()).'">
					<img src="http://api.tweetmeme.com/imagebutton.gif'.$this->build_button_options().'" height="61" width="50" />
				</a>
			</div>';
	}
	
	/**
	* Get the URL for the current button
	*/
	function get_url($row = NULL)
	{
		global $FNS;
		
		$this->load_settings();
		
		if ( ! empty($row['weblog_id']))
		{
			if ( ! empty($this->settings['tm_path_'.$row['weblog_id']]))
			{
				if ( ! empty($this->settings['tm_use_url_title_'.$row['weblog_id']]) && isset($row['url_title']))
				{
					return $FNS->create_url($this->settings['tm_path_'.$row['weblog_id']].'/'.$row['url_title']);
				}
				elseif (isset($row['entry_id']))
				{
					return $FNS->create_url($this->settings['tm_path_'.$row['weblog_id']].'/'.$row['entry_id']);
				}
			}
			
			return FALSE;
		}
		
		if ( ! empty($this->button_params['url']))
		{
			return $this->button_params['url'];
		}
		
		if ( ! empty($this->button_params['entry_id_path']) && isset($this->entry_data['entry_id']))
		{
			return $FNS->create_url($this->button_params['entry_id_path'].'/'.$this->entry_data['entry_id']);
		}
		
		if ( ! empty($this->button_params['url_title_path']) && isset($this->entry_data['url_title']))
		{
			return $FNS->create_url($this->button_params['url_title_path'].'/'.$this->entry_data['url_title']);
		}
		
		if ( ! empty($this->button_params['title_permalink']) && isset($this->entry_data['url_title']))
		{
			return $FNS->create_url($this->button_params['title_permalink'].'/'.$this->entry_data['url_title']);
		}
		
		if ( ! empty($this->button_params['path']))
		{
			return $FNS->create_url($this->button_params['path']);
		}
		
		return $FNS->fetch_current_uri();
	}
	
	/**
	* Get the button
	*/
	function get_button()
	{
		return ($this->is_rss()) ? $this->generate_static_button() : $this->generate_button();
	}

	/**
	* Load the tweets for a URL
	*/
	function get_tweets($url)
	{
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, 'http://api.tweetmeme.com/analytics/free.json?url='.urlencode($url));
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			// grab URL and pass it to the browser
			$data = curl_exec($ch);
			// close cURL resource, and free up system resources
			curl_close($ch);
	
			$data = json_decode($data, TRUE);
	
			if (@$data['status'] != 'success')
			{
				return array(
					'success'=>'false',
					'reason'=>@$data['comment']
				);
			}
	
			return array(
				'success'=>'true',
				'data' => $data
			);
		}
		
		return FALSE;
	}
	
	/**
	* Is this an RSS template?
	*/
	function is_rss()
	{
		global $TMPL;
		
		return ( ! empty($TMPL->template_type) && $TMPL->template_type == 'rss');
	}

/**
 * Extension Hooks
 */

	/**
	 * Add "View Stats" button to CP edit entries page
	 */
	function edit_entries_additional_celldata($row)
	{
		global $DB, $DSP, $EXT, $LANG, $LOC, $PREFS, $SESS;
		
		$return_data = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';
		
		$SESS->cache['tweetmeme']['row_count'] = (isset($SESS->cache['tweetmeme']['row_count'])) ? $SESS->cache['tweetmeme']['row_count'] : 0;
			
		$style = ($SESS->cache['tweetmeme']['row_count']++ % 2) ? 'tableCellOne' : 'tableCellTwo';
		
		$text = ($url = $this->get_url($row)) ? '<a href="javascript:void(0);" onclick="tm_load_analytics(\''.$url.'\', this)">'.$LANG->line('tm_view_stats').'</a>' : '<span style="color:#666;">'.$LANG->line('tm_not_active').'</span>';
		
		$return_data .= $DSP->table_qcell($style, $DSP->qdiv('smallNoWrap', $text));
		
		return $return_data;
	}
	
	/**
	 * Add "TweetMeme" table header to CP edit entries page
	 */
	function edit_entries_additional_tableheader($row)
	{
		global $DSP, $EXT, $LANG;
		
		$return_data = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';
		
		$return_data .= $DSP->table_qcell('tableHeadingAlt', $LANG->line('tm_tableheader'));
		
		return $return_data;
	}
	
	/**
	 * Add ajax JS for retrieving stats to CP edit entries page
	 */
	function edit_entries_extra_actions()
	{
		global $EXT, $SESS;
		
		$SESS->cache['tweetmeme']['row_count'] = 0;
		
		$return_data = $EXT->last_call;
		
		$return_data .= '
		<script type="text/javascript">
		function tm_load_analytics(url, el)
		{
			if ( ! url)
			{
				return;
			}
			
			tm_hide_analytics();
			
			jQuery.get(
				"'.BASE.'",
				{
					C: "admin",
					M: "utilities",
					P: "extension_settings",
					name: "tweetmeme_ext",
					tm_analytics: 1,
					url: url
				},
				function(data){
					var tr = jQuery(el).parent().parent().parent();
					var colspan = tr.children("td").length;
					jQuery(\'<tr class="tm_analytics" />\').insertAfter(tr).append("<td />").children("td").addClass("tableCellTwo").eq(0).hide().html(data).attr("colspan", String(colspan)).fadeIn();
				},
				"html"
			)
		}
		function tm_hide_analytics()
		{
			jQuery(".tm_analytics").fadeOut("normal", function(){ jQuery(this).remove(); })
		}
		</script>
		';
		
		return $return_data;
	}
	
	/**
	* Ping when tweetmeme when a post is updated, this makes sure the titles/desc are correct on tweetmeme
	*/
	function submit_new_entry_absolute_end($entry_id, $data)
	{
		if ($this->setting('tm_ping') && function_exists('curl_init'))
		{
			$data['entry_id'] = $entry_id;
			
			if ($url = $this->get_url($data))
			{
				// create a new cURL resource
				$ch = curl_init();
				// set URL and other appropriate options
				curl_setopt($ch, CURLOPT_URL, 'http://api.tweetmeme.com/ping.php?url='.urlencode($url));
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
				curl_setopt($ch, CURLOPT_TIMEOUT, 3);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				// grab URL and pass it to the browser
				curl_exec($ch);
				// close cURL resource, and free up system resources
				curl_close($ch);
			}
		}
	}
	
	/**
	 * Add {tweetmeme} tag to weblog:entries
	 */
	function weblog_entries_tagdata($tagdata, $row, &$WEBLOG)
	{
		global $EXT, $TMPL;
		
		$tagdata = ($EXT->last_call !== FALSE) ? $EXT->last_call : $tagdata;
		
		if ( ! is_object($TMPL))
		{
			return $tagdata;
		}
		
		$this->entry_data = $row;
		
		$this->load_settings();
		
		if (isset($this->settings['tm_path_'.$row['weblog_id']]))
		{
			if ( ! empty($this->settings['use_url_title_'.$row['weblog_id']]) && isset($row['url_title']))
			{
				$this->button_params['url_title_path'] = $this->settings['tm_path_'.$row['weblog_id']];
			}
			elseif (isset($row['entry_id']))
			{
				$this->button_params['entry_id_path'] = $this->settings['tm_path_'.$row['weblog_id']];
			}
		}
		
		if (preg_match_all("/".LD."tweetmeme(.+)?".RD."/", $tagdata, $matches))
		{
			foreach ($matches[0] as $index => $full_match)
			{
				$var_string = (isset($matches[1][$index])) ? $matches[1][$index] : '';
				
				if ($var_string && preg_match_all("/([a-zA-Z0-9_]+)\s*=\s*[\"\'](.+?)[\"\']/is", $var_string, $var_string_matches))
				{	
					foreach ($var_string_matches[1] as $index => $name)
					{
						if (preg_match("/".LD."(.+)".RD."/", $var_string_matches[2][$index], $entry_var))
						{
							$entry_var = $entry_var[1];
							
							if (isset($row[$entry_var]))
							{
								$var_string_matches[2][$index] = $TMPL->swap_var_single($entry_var, $row[$entry_var], $var_string_matches[2][$index]);
							}
							else
							{
								foreach ($WEBLOG->cfields as $cfield_row)
								{
									foreach ($cfield_row as $cfield_key => $cfield_value)
									{
										if ($entry_var == $cfield_key && isset($row['field_id_'.$cfield_value]))
										{
											$var_string_matches[2][$index] = $TMPL->swap_var_single($entry_var, $row['field_id_'.$cfield_value], $var_string_matches[2][$index]);
											break(2);
										}
									}
								}
							}
						}
						
						$this->button_params[$name] = (strpos($var_string_matches[2][$index], '&#47;') !== FALSE) ? str_replace('&#47;', '/', $var_string_matches[2][$index]) : $var_string_matches[2][$index];
					}
				}
				
				$tagdata = str_replace($full_match, $this->get_button(), $tagdata);
			}
		}
		
		return $tagdata;
	}
}

/* End of file ext.tweetmeme_ext.php */
/* Location: ./system/extensions/ext.tweetmeme_ext.php */