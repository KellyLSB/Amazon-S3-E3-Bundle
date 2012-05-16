<?php

namespace Bundles\AmazonS3;
use Exception;
use e;

class Bundle {

	private $handle;
	private $enabled = false;

	public function _on_framework_loaded() {
		e::$environment->requireVar('aws.access.key');
		e::$environment->requireVar('aws.secret.key');
		e::$environment->requireVar('aws.s3.bucket');
		$this->enabled = e::$environment->requireVar('aws.s3.enabled', 'true | false');

		e::configure('autoload')->activeAddKey('special', 'Bundles\\AmazonS3\\s3_handle', __DIR__ . '/library/s3.php');
	}

	public function __initBundle() {
		if(!$this->enabled) return 'Disabled';
		$this->handle = new s3_handle;
	}

	public function __call($func, $args) {
		if(!$this->enabled) return 'Disabled';
		return call_user_func_array(array($this->handle, $func), $args);
	}

	public function _on_uploadFile($file, $filename) {
		if(!$this->enabled) return array();
		$this->__initBundle();

		$return = $this->handle->upload($file, $filename);
		if($return) return array('filename' => 'aws-s3|'.$filename);

		return array();
	}

	public function _on_loadFile($filename) {
		if(!$this->enabled) return array();
		$this->__initBundle();

		$files = explode(':', $filename);

		foreach($files as $file) {
			if(strpos($file, '|') === FALSE)
				continue;

			list($v1, $v2) = explode('|', $file);
			if($v1 !== 'aws-s3') continue;

			return $this->handle->info($v2);
		}

		return array();
	}
	
}