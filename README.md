ThinkApiAdmin for PHP
--
* ThinkApiAdmin 是一个基于 Thinkphp 5.0.x 对[ThinkAdmin](https://gitee.com/zoujingli/Think.Admin)V2版本和[ApiAdmin](https://gitee.com/apiadmin/ApiAdmin)V2版本整合开发的后台管理系统，集成后台系统常用功能和API接口管理。
* 如需使用前后端分离接口管理请使用ApiAdmin V3版本
* 项目安装及二次开发请参考 ThinkPHP 官方文档及下面的服务环境说明，数据库 sql 文件存放于项目根目录下。
* 整合v4.0版下载地址: https://gitee.com/gitzwt/ThinkApiAdmin/repository/archive/v4.0.zip
> 注意：项目测试请另行搭建环境并创建数据库（修改数据库配置 application/database.php.dev => database.php）, 切勿直接使用测试环境数据！
> * 测试Demo地址: http://demo.zwt520.com
> * 后台账号密码: admin admin
> * 接口文档地址: http://demo.zwt520.com/wiki.html
> * 文档访问密钥: demo666

Repositorie
--
 ThinkApiAdmi 为开源项目，允许把它用于任何地方，不受任何约束，欢迎 fork 项目。
>* 码云托管地址：https://gitee.com/gitzwt/ThinkApiAdmin
>* GitHub地址: https://github.com/gitzwt/ThinkApiAdmin

## ThinkAdmin 原始功能模块

Module
--
* 简易`RBAC`权限管理（用户、权限、节点、菜单控制）
* ThinkAdmin秒传文件上载组件（本地存储、七牛云存储，阿里云OSS存储）
* 基站数据服务组件（唯一随机序号、表单更新）
* `Http`服务组件（原生`CURL`封装，兼容PHP多版本）
* 微信公众号服务组件（基于[wechat-php-sdk](https://github.com/zoujingli/wechat-php-sdk)，微信网页授权获取用户信息、已关注粉丝管理、自定义菜单管理等等）
* 微信商户支付服务组件（基于[wechat-php-sdk](https://github.com/zoujingli/wechat-php-sdk)，支持JSAPI支付、扫码模式一支付、扫码模式二支付）
* 更多组件开发中...
![微信管理](https://gitee.com/uploads/images/2018/0411/165535_04341af9_991419.png "微信管理")

## ApiAdmin 原始功能模块

Module
--
 1. 接口文档自动生成,接口分组
 2. 接口输入参数自动检查
 3. 接口输出参数数据类型自动规整
 4. 灵活的参数规则设定
 5. 接口在线测试
 6. 基于哈希值的接口请求地址
 7. 更多接口功能开发中...
![接口文档首页](https://gitee.com/uploads/images/2018/0411/165622_a6c172a3_991419.png "接口文档首页")
![文档详情页](https://gitee.com/uploads/images/2018/0411/165656_774a4ab4_991419.png "文档详情页")
![接口请求模拟](https://gitee.com/uploads/images/2018/0411/165720_31eeb665_991419.png "接口请求模拟")
 
# ThinkApiAdmin 整合后台模块

Module
--
* 接口文档分组
* 新增接口
* 接口参数添加编辑开发中... 
![接口管理](https://gitee.com/uploads/images/2018/0411/165742_33eb22a3_991419.png "后台接口管理")


Environment
---
>1. PHP 版本不低于 PHP5.6，推荐使用 PHP7 以达到最优效果；
>2. 需开启 PATHINFO，不再支持 ThinkPHP 的 URL 兼容模式运行（源于如何优雅的展示）。

* Apache

```xml
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```

* Nginx

```
server {
	listen 80;
	server_name wealth.demo.cuci.cc;
	root /home/wwwroot/ThinkAdmin;
	index index.php index.html index.htm;
	
	add_header X-Powered-Host $hostname;
	fastcgi_hide_header X-Powered-By;
	
	if (!-e $request_filename) {
		rewrite  ^/(.+?\.php)/?(.*)$  /$1/$2  last;
		rewrite  ^/(.*)$  /index.php/$1  last;
	}
	
	location ~ \.php($|/){
		fastcgi_index   index.php;
		fastcgi_pass    127.0.0.1:9000;
		include         fastcgi_params;
		set $real_script_name $fastcgi_script_name;
		if ($real_script_name ~ "^(.+?\.php)(/.+)$") {
			set $real_script_name $1;
		}
		fastcgi_split_path_info ^(.+?\.php)(/.*)$;
		fastcgi_param   PATH_INFO               $fastcgi_path_info;
		fastcgi_param   SCRIPT_NAME             $real_script_name;
		fastcgi_param   SCRIPT_FILENAME         $document_root$real_script_name;
		fastcgi_param   PHP_VALUE               open_basedir=$document_root:/tmp/:/proc/;
		access_log      /home/wwwlog/domain_access.log    access;
		error_log       /home/wwwlog/domain_error.log     error;
	}
	
	location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {
		access_log  off;
		error_log   off;
		expires     30d;
	}
	
	location ~ .*\.(js|css)?$ {
		access_log   off;
		error_log    off;
		expires      12h;
	}
}
```

Copyright
--
* ThinkApiAdmin 基于`MIT`协议发布，任何人可以用在任何地方，不受约束
* ThinkApiAdmin 代码来自互联网开源项目，若有异议，可以联系作者进行删除

鸣谢
--
* [ThinkAdmin](https://gitee.com/zoujingli/Think.Admin)
* [ApiAdmin](https://gitee.com/apiadmin/ApiAdmin)