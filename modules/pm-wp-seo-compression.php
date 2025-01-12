<?php
/**
 * Module for compression.
 */
require_once(__DIR__.'/../pm-wp-seo.php');
 
if (!defined('ABSPATH')) return;

require_once(ABSPATH . 'wp-admin/includes/file.php');

class PoorMansWordPressSEOCompression {

	const HTACCESS_FILENAME = '.htaccess';

	public function toggle_gzip_compression() {
		$compression_enabled_initially = get_option('pm_wp_seo_gzip_compression') === PoorMansWordPressSEO::OPTION_ON;
		$result = FALSE;
		
		if ($compression_enabled_initially) {
			$result = $this->remove_gzip_compression_from_htaccess();
		}
		else {			
			$result = $this->add_gzip_compression_to_htaccess();
		}
				
		if ($result === FALSE) {
			update_option('pm_wp_seo_htaccess_save', PoorMansWordPressSEO::STATUS_ERROR);
			$this->redirect_to_settings_page();
		}
		else {
			$working = $this->is_gzip_compression_working_test();
			
			if (!$compression_enabled_initially && !$working) {
				$this->remove_gzip_compression_from_htaccess();
				update_option('pm_wp_seo_gzip_test_result', PoorMansWordPressSEO::STATUS_ERROR);
				$this->redirect_to_settings_page();
			} else {
				update_option('pm_wp_seo_gzip_test_result', PoorMansWordPressSEO::STATUS_OK);
			}
			
			if ($compression_enabled_initially) {
				update_option('pm_wp_seo_gzip_compression', PoorMansWordPressSEO::OPTION_OFF);				
			}
			else {
				update_option('pm_wp_seo_gzip_compression', PoorMansWordPressSEO::OPTION_ON);
			}
			update_option('pm_wp_seo_htaccess_save', PoorMansWordPressSEO::STATUS_OK);
		}
		
		$this->redirect_to_settings_page();
	}

	public function add_gzip_compression_to_htaccess() {
		$file = get_home_path() . self::HTACCESS_FILENAME;
		
		$lines = array();
		$lines[] = '<IfModule mod_deflate.c>';
		$lines[] = 'AddOutputFilterByType DEFLATE text/text text/html text/plain text/xml text/css application/x-javascript application/javascript';
		$lines[] = '</IfModule>';
		
		return insert_with_markers($file, PoorMansWordPressSEO::PLUGIN_NAME, $lines);
	}
	
	public function remove_gzip_compression_from_htaccess() {
		$file = get_home_path() . self::HTACCESS_FILENAME;
		return insert_with_markers($file, PoorMansWordPressSEO::PLUGIN_NAME, array());
	}
	
	private function is_gzip_compression_working_test() {
		$arguments = array(
			'headers' => array(
				'Content-Encoding' => 'gzip'
			)
		);
		
		$response = wp_remote_get(get_site_url(), $arguments);
		return strpos($response['headers']['content-encoding'], 'gzip') !== FALSE;
	}
	
	private function redirect_to_settings_page() {
		header('Location: ' . get_admin_url() . PoorMansWordPressSEO::ADMIN_SETTINGS_URL);
		exit();
	}
	
}