<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author ym
 * @group(name="docgroup", description="文档模块")
 */

class Usr extends CI_Controller {

	function __construct() {
		parent::__construct();
	}

	/**
     * @ApiDescription(section="User", method="get", description="Get information about user")
     * @ApiRoute(name="/user/get/{id}")
     * @ApiParams(name="id", type="integer", is_selected=true, description="User id")
     * @ApiParams(name="sort", type="enum[asc,desc]", description="User data")
     * @ApiReturn(name="id", type="integer", description="User id")
     * @ApiReturn(name="sort", type="enum[asc,desc]", description="sort data")
     * @ApiReturn(name="page", type="integer", description="data of page")
     * @ApiReturn(name="count", type="integer", description="data of page")
     */
	function test(){
		echo 'hello world';
	}

}