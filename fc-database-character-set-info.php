<?php
/*
Plugin Name: FC Database character set info
Plugin URI: http://www.fanaticalcode.com/
Description: Database helper plugin for Wordpress administrators. Check your database, table and column character set. Avaliable by "Tools" > "DB Character Set Info" in admin panel.
Version: 0.1.0
Author: Fanatical Code - Kamil Skrzypiński
Author URI: http://www.fanaticalcode.com
License: GPLv2
*/

if ( ! is_admin() ) return false;

define( 'FCDBCSI_VERSION', '0.1.0' );
define( 'FCDBCSI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FCDBCSI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

function fcdbcsi_init()
{
	wp_register_style( 'fcdbcsi.css', FCDBCSI_PLUGIN_URL . 'fcdbcsi.css', false, FCDBCSI_VERSION.'-'.time() );
	wp_enqueue_style( 'fcdbcsi.css' );
}
add_action('init', 'fcdbcsi_init');

function fcdbcsi_create_menu() {
	add_submenu_page( 'tools.php', __('FC Database SQL Settings', 'fcdbcsi'), __('DB Character Set Info', 'fcdbcsi'), 'administrator', 'fcdbcsi', 'fcdbcsi_settings_page' );
}
add_action( 'admin_menu', 'fcdbcsi_create_menu' );

function fcdbcsi_settings_page()
{
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2><?php _e('Database character set info', 'fcdbcsi'); ?></h2>
		<?php
		global $wpdb;
		
		// SQL based on http://stackoverflow.com/questions/1049728/how-do-i-see-what-character-set-a-database-table-column-is-in-mysql/4805964#4805964 by Eric
		$temp = $wpdb->query("SELECT TABLE_SCHEMA,
		       TABLE_NAME,
		       CCSA.CHARACTER_SET_NAME AS DEFAULT_CHAR_SET,
		       COLUMN_NAME,
		       COLUMN_TYPE,
		       C.CHARACTER_SET_NAME
		  FROM information_schema.TABLES AS T
		  JOIN information_schema.COLUMNS AS C USING (TABLE_SCHEMA, TABLE_NAME)
		  JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS CCSA
		       ON (T.TABLE_COLLATION = CCSA.COLLATION_NAME)
		 WHERE TABLE_SCHEMA=SCHEMA()
		   AND C.DATA_TYPE IN ('enum', 'varchar', 'char', 'text', 'mediumtext', 'longtext')
		 ORDER BY TABLE_SCHEMA,
		          TABLE_NAME,
		          COLUMN_NAME");
		
		$result = $wpdb->last_result;
		
		if (is_array($result))
		{
			echo '<table class="fcdbcsi">'.
				'<thead><tr>'.
				'<th>'. __('Schema', 'fcdbcsi') .'</th>'.
				'<th>'. __('Table name', 'fcdbcsi') .'</th>'.
				'<th>'. __('Table charset', 'fcdbcsi') .'</th>'.
				'<th>'. __('Column name', 'fcdbcsi') .'</th>'.
				'<th>'. __('Column type', 'fcdbcsi') .'</th>'.
				'<th>'. __('Column charset', 'fcdbcsi') .'</th>'.
				'</tr></thead><tbody>';
				
			foreach ($result as $t)
			{
				echo '<tr>'.
					'<td>'. $t->TABLE_SCHEMA .'</td>'.
					'<td>'. $t->TABLE_NAME .'</td>'.
					'<td>'. $t->DEFAULT_CHAR_SET .'</td>'.
					'<td>'. $t->COLUMN_NAME .'</td>'.
					'<td>'. $t->COLUMN_TYPE .'</td>'.
					'<td'.($t->CHARACTER_SET_NAME !== $t->DEFAULT_CHAR_SET ? ' class="notify"': '' ).'>'. $t->CHARACTER_SET_NAME .'</td>'.
					'</tr>';
			}
			echo '</tbody></table>';
		}
		?>
	</div>
	
<?php
}

