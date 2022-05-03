=== 果果推送 ===
Contributors: wyzda2021
Donate link: https://dev.ggdoc.cn
Tags:baidu, Bing, bing站长, 百度, 百度站长平台, baiduseo, 百度普通收录, 百度快速收录, 推送, 主动提交, Bing链接提交, IndexNow链接提交, 定时提交链接
Requires at least: 5.0
Requires PHP:5.3
Tested up to: 5.9
Stable tag: 0.0.2
License: GNU General Public License v2.0 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

支持百度搜索引擎的普通、快速收录、微软Bing搜索引擎、以及IndexNow方式的Api提交链接功能，同时还支持定时提交链接功能。

== Description ==

支持百度搜索引擎、微软Bing搜索引擎以及IndexNow方式的Api提交链接功能，让搜索引擎更快的发现您网站上的新内容链接。

1、发布新文章时，自动向搜索引擎提交链接。

2、使用定时任务可以向搜索引擎周期性提交最新、随机数量的文章链接。

3、可自由设置提交的搜索引擎、定时提交周期时间、提交随机还是最新的文章链接、提交文章链接的数量。

4、可以查看推送记录，例如：推送链接明细、推送结果、推送是否成功等其它推送内容。

== Installation ==

1. 进入WordPress网站后台，找到“插件-安装插件”菜单；
2. 点击界面左上方的“上传插件”按钮，选择本地提前下载好的插件压缩包文件（zip格式），点击“立即安装”；
3. 安装完成后，启用 “果果推送” 插件；
4. 通过“设置”链接进入插件设置界面；
5. 完成设置后，插件就安装完毕了。

<a href="https://dev.ggdoc.cn/article/ycszj8si0agu8g9fvjxz98kccrvtqpb5.html" rel="friend">为了能够定时推送文章链接，请点击此链接了解WP-Cron配置</a>

== Frequently Asked Questions ==

= 百度搜索引擎的准入密钥在哪里获取？ =
打开百度站长平台的普通收录页面，找到API提交的接口调用地址。
例如：接口调用地址为http://data.zz.baidu.com/urls?site=您的网站主页网址&token=准入密钥
则准入密钥为token=后面的字符串。

= 必应搜索引擎的API密钥在哪里获取？ =
可以在必应的站长平台右上方的设置按钮中找到。

= IndexNow的密钥在哪里获取？ =
不需要获取，只需要在插件设置那里填写32位随机字符串，插件会自动生成密钥验证文件。

= 为什么发布文章后变慢了？ =
这是因为您设置了“发布文章后推送”这个功能，如果同时开启的推送平台比较多，就会造成明显的卡顿效果。建议只开启必要的推送平台，例如：百度平台的快速收录。

= 为什么网站后台变慢了？ =
因为插件需要定时向搜索平台推送链接，而默认的WordPress执行定时任务的条件是用户访问网站时才会执行。<a href="https://dev.ggdoc.cn/article/ycszj8si0agu8g9fvjxz98kccrvtqpb5.html" rel="friend">您可以参考这篇文章配置定时任务执行方式</a>

= 为什么推送结果状态码中有的数值为0？ =
这很有可能是您的服务器不能访问推送平台。

= 商业版有哪些功能？ =
商业版新增了神马搜索引擎推送、自动收录、谷歌搜索引擎推送、Sitemap推送。

== Screenshots ==

1. 推送记录列表页面
2. 推送记录详情页面
3. 定时任务运行状态页面
4. 基本设置页面
5. 百度推送普通收录设置页面
6. 百度推送快速收录设置页面
7. 必应推送设置页面
8. IndexNow推送设置页面
9. 插件菜单页面

== Upgrade Notice ==

= 0.0.2 =
解决了清空记录bug

= 0.0.1 =
参考Changelog说明

== Changelog ==

= 0.0.2 =
* 新增插件菜单显示位置以及请求超时时间设置
* 将多个平台的配置分到单独的页面设置
* 新增定时任务运行查看页面

= 0.0.1 =
* 新增百度搜索引擎普通、快速收录功能
* 新增微软Bing搜索引擎Api推送文章链接功能
* 新增IndexNow推送文章链接功能
* 新增发布新文章时推送文章链接功能
* 新增文章链接推送记录功能