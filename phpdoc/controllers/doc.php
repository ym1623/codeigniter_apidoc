<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Doc extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library('phpdoc/apidoc');
	}

	function build(){
		$this->apidoc->build_doc();
	}

}