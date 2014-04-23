## 文件配置
### 配置说明（`config/apidocs.php`）
---
|字段|说明|例子|
|:---|:---|:---|
|title|项目文档标题|云南七彩高原接口文档|
|logo|在生成的文档首页中的logo|https://git.oschina.net/7cgy/api_7cgy/raw/master/banner_4.jpg|
|allowed_file|允许生成注释文档的后缀名称|单个后缀：`.php`多个后缀:（`.php,.js`）|
|output_format|该项目所支持的接口输出类型|如: ___json, xml, html, jsonp, php, serialized, csv...___|
|build_path|编译文件夹|如：***`controllers, models`***|
|level|编译目录的层级（默认在你项目的index.php的层级）|`*/`代表着第二层级，即`application, system...`下的层级, 如果你想编译整个即`application`文件夹就直修改`build_path="application", level=""`, 如果你想编译第三层级，即`controllers`里面还有个叫做`admin`的文件夹，你可以修改`level="*/*/", build_path="admin"`|
|template|使用模板|官方提供两个模板：（`default`[***markdown***], `html`）, 参考文件夹下的`libraries/phpdoc/template`|
|template_ext|模板后缀名称|如：___.md, .html...___|
|footer|生成后文档首页的footer|如：欢迎使用api doc 文档生成工具|
|rule|方法注释规则|对应着方法中所注释的名称，通过修改该名称可自定义注释标签|
