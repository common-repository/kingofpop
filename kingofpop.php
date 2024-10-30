<?php
/*
Plugin Name: KingOfPop
Plugin URI: https://wordpress.org/plugins/kingofpop
Description: Displays a Micheal Jackson badge in the sidebar of your blog via widget interface or anywhere else via function call. Make the people remember the King of Pop.
Version: 0.3
Author: tom
Author URI: https://profiles.wordpress.org/tomknows/
*/
 
/**
 * v0.3 2014-10-15 updated to wp 4x
 * v0.2 2009-06-27 small css fix makes micheal looks better
 * v0.1 2009-06-26 initial release
 */

class KingOfPop {
  var $id;
  var $title;
  var $plugin_url;
  var $version;
  var $name;
  var $url;
  var $options;
  var $locale;

  function KingOfPop() {
    $this->id         = 'kingofpop';
    $this->title      = 'KingOfPop';
    $this->version    = '0.3';
    $this->plugin_url = 'https://wordpress.org/plugins/kingofpop';
    $this->name       = 'KingOfPop v'. $this->version;
    $this->url        = get_bloginfo('wpurl'). '/wp-content/plugins/' . $this->id;
	  $this->locale     = get_locale();
    $this->path       = dirname(__FILE__);

	  if(empty($this->locale)) {
		  $this->locale = 'en_US';
    }

    load_textdomain($this->id, sprintf('%s/%s.mo', $this->path, $this->locale));

    $this->loadOptions();

    if(!is_admin()) {
      add_filter('wp_head', array(&$this, 'blogHeader'));
    }
    else {
      add_action('admin_menu', array( &$this, 'optionMenu')); 
    }

    add_action('widgets_init', array( &$this, 'initWidget')); 
  }

  function optionMenu() {
    add_options_page($this->title, $this->title, 8, __FILE__, array(&$this, 'optionMenuPage'));
  }

  function optionMenuPage() {
?>
<div class="wrap">
<h2><?=$this->title?></h2>
<div align="center"><p><?=$this->name?> <a href="<?php print( $this->plugin_url ); ?>" target="_blank">Plugin Homepage</a></p></div> 
<?php
  if(isset($_POST[ $this->id ])) {
    /**
     * nasty checkbox handling
     */
    foreach(array('show_wikipedia', 'show_youtube') as $field ) {
      if(!isset($_POST[$this->id][$field])) {
        $_POST[$this->id][$field] = '0';
      }
    }

    $this->updateOptions( $_POST[ $this->id ] );

    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Settings saved!', $this->id ) . '</strong></p></div>'; 
  }
?>
<form method="post" action="options-general.php?page=<?=$this->id?>/<?=$this->id?>.php">

<table class="form-table">

<tr valign="top">
  <th scope="row"><?php _e('Title', $this->id); ?></th>
  <td colspan="3"><input name="kingofpop[title]" type="text" id="" class="code" value="<?=$this->options['title']?>" /><br /><?php _e('Title is shown above the Widget. If left empty can break your layout in widget mode!', $this->id); ?></td>
</tr>

<tr>
<th scope="row" colspan="4" class="th-full">
<label for="">
<input name="kingofpop[show_wikipedia]" type="checkbox" id="" value="1" <?php echo $this->options['show_wikipedia']=='1'?'checked="checked"':''; ?> />
<?php _e('Show a link to wikipedia according to the current country?', $this->id); ?></label>
</th>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Wikipedia link', $this->id); ?></th>
  <td colspan="3"><input name="kingofpop[wikipedia_url]" type="text" id="" class="code" value="<?=$this->options['wikipedia_url']?>" /></td>
</tr>

<tr>
<th scope="row" colspan="4" class="th-full">
<label for="">
<input name="kingofpop[show_youtube]" type="checkbox" id="" value="1" <?php echo $this->options['show_youtube']=='1'?'checked="checked"':''; ?> />
<?php _e('Show a link to Youtube Micheal Jackson Videos?', $this->id); ?></label>
</th>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Youtube link', $this->id); ?></th>
  <td colspan="3"><input name="kingofpop[youtube_url]" type="text" id="" class="code" value="<?=$this->options['youtube_url']?>" /></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Border', $this->id); ?></th>
  <td colspan="3"><input name="kingofpop[border]" type="text" id="" class="code" value="<?=$this->options['border']?>" />
  <br /><?php _e('Border width in pixel. Set to 0 to hide border.', $this->id); ?></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Border color', $this->id); ?></th>
  <td colspan="3"><input name="kingofpop[border_color]" type="text" id="" class="code" value="<?=$this->options['border_color']?>" /></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Background color', $this->id); ?></th>
  <td colspan="3"><input name="kingofpop[background_color]" type="text" id="" class="code" value="<?=$this->options['background_color']?>" /></td>
</tr>

</table>

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('save', $this->id); ?>" class="button" />
</p>
</form>

</div>
<?php
  }

  function updateOptions($options) {

    foreach($this->options as $k => $v) {
      if(array_key_exists( $k, $options)) {
        $this->options[ $k ] = trim($options[ $k ]);
      }
    }
        
		update_option($this->id, $this->options);
	}
  
  function loadOptions() {
    $this->options = get_option($this->id);

    if( !$this->options ) {
      $this->options = array(
        'installed' => time(),
        'border' => 1,
        'border_color' => 'cccccc',
        'background_color' => 'f7f7f7',
        'show_wikipedia' => 0,
        'show_youtube' => 0,
        'wikipedia_url' => 'http://en.wikipedia.org/wiki/Michael_Jackson',
        'youtube_url' => 'http://www.youtube.com/results?search_query=micheal+jackson',
        'title' => 'KingOfPop'
			);

      add_option($this->id, $this->options, $this->name, 'yes');
      
      if(is_admin()) {
        add_filter('admin_footer', array(&$this, 'addAdminFooter'));
      }
    }

  }

  function initWidget() {
    if(function_exists('register_sidebar_widget')) {
      register_sidebar_widget($this->title . ' Widget', array($this, 'showWidget'), null, 'widget_kingofpop');
    }
  }

  function showWidget( $args ) {
    extract($args);
    printf( '%s%s%s%s%s%s', $before_widget, $before_title, $this->options['title'], $after_title, $this->getCode(), $after_widget );
  }

  function blogHeader() {
    printf('<meta name="%s" content="%s/%s" />' . "\n", $this->id, $this->id, $this->version);
    printf('<link rel="stylesheet" href="%s/styles/%s.css" type="text/css" media="screen" />'. "\n", $this->url, $this->id);
    
    if(intval($this->options['border']) > 0 || !empty($this->options['background_color'])) {
      printf('<style type="text/css">#kingofpop {border: %dpx solid #%s; background-color: #%s;}</style>'. "\n", $this->options['border'], $this->options['border_color'], $this->options['background_color']);
    }
 
  }

  function getCode() {
      $data = '';
      
      if(intval($this->options['show_wikipedia']) == 1 || intval($this->options['show_youtube']) == 1) {
        $data .= '<br />';
        
        if(intval($this->options['show_youtube']) == 1) {
          $data .= sprintf('<a href="%s">Youtube</a>', $this->options['youtube_url']);
        }
        if(intval($this->options['show_wikipedia']) == 1) {
          $data .= sprintf(' <a href="%s">Wikipedia</a>', $this->options['wikipedia_url']);
        }
      }

      return sprintf('<div id="%s"><img src="%s/mj_%s.jpg" border="0" />%s<br /><small><a href="https://wordpress.org/plugins/kingofpop/" target="_blank">Plugin by Tom</a></small></div>', $this->id, $this->url, $this->locale, $data);
  }
}

function kingofpop_display() {

  global $KingOfPop;

  if($KingOfPop) {
    echo $KingOfPop->getcode();
  }
}

add_action( 'plugins_loaded', create_function( '$KingOfPop_al223', 'global $KingOfPop; $KingOfPop = new KingOfPop();' ) );

?>