## 文件注释说明文档
### 文件注释规则：

```php
/**
 * @author ym
 * @group(name="docgroup", description="文档模块")
 */
class Doc extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library('phpdoc/apidoc');
	}

	/**
     * @ApiDescription(section="User", method="get", description="Get information about user")
     * @ApiNotice(description="登录后，客户端要保存服务器种下的cookie来维护session")
     * @ApiSuccess(value="{'firstname' : 'ym', 'lastname'  : '1623', 'lastLogin' : '2014-04-21'}")
     * @ApiExample(value="{'username' : 'ym', 'password'  : '123456'}")
     * @ApiAccess(login="true")
     * @ApiParams(name="id", type="integer", is_selected=true, description="User id")
     * @ApiParams(name="sort", type="enum[asc,desc]", description="User data")
     * @ApiReturn(name="id", type="integer", description="User id")
     * @ApiReturn(name="sort", type="enum[asc,desc]", description="sort data")
     * @ApiReturn(name="page", type="integer", description="data of page")
     * @ApiReturn(name="count", type="integer", description="data of page")
     */
	function build(){
		$this->apidoc->build_doc();
	}

}
```
### 如上面的代码所示：
## class注释规则：
1. 在`class`上方必须注释***`group`***， 代表该文件所属群组

## 方法注释规则:
- ***ApiDescription***（***`必填`***）
  - ***section***（`生成该方法的文档标题`）
  - ***method***（**方法的请求类型如：**`get/post`）
  - ***description*** （`文档首页中群组下的方法描述`）
- ***ApiNotice***（***`可选`*** [`注意事项`]）
  - ***description***（***string***）
- ***ApiSuccess***（***`可选`*** [`返回结果`]）
  - ***value***（***object***）
- ***ApiExample***（***`可选`*** [`请求示例`]）
  - ***value***（***object***）
- ***ApiAccess*** （***`可选`*** [**是否需要登录，默认**`false`]）
  - ***login***（***bool***）
- ***ApiParams***（***`可选`*** [`请求参数`]）
  - ***name***（`参数名称`）
  - ***type***（`参数类型`）
  - ***is_selected***（`是否必填`）
  - ***description***（`参数描述`）
- ***ApiReturn***（***`可选`*** [`返回字段说明`]）
  - ***name***（`参数名称`）
  - ***type***（`参数类型`）
  - ***description***（`参数描述`）
