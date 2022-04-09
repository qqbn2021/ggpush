=== 果果推送 ===
Contributors: wyzda2021
Donate link: https://www.ggdoc.cn
Tags:baidu, Bing, bing站长, 百度, 百度站长平台, baiduseo, 百度普通收录, 百度快速收录, 推送, 主动提交, Bing链接提交, IndexNow链接提交, 定时提交链接
Requires at least: 5.0
Requires PHP:5.4
Tested up to: 5.9
Stable tag: 0.0.1
License: GNU General Public License v2.0 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

支持百度搜索引擎的普通、快速收录、微软Bing搜索引擎、以及IndexNow方式的Api提交链接功能，同时还支持定时提交链接功能。

== Description ==

支持百度搜索引擎、微软Bing搜索引擎以及IndexNow方式的Api提交链接功能，让搜索引擎更快的发现您网站上的新内容链接。

1、发布新文章时，自动向搜索引擎提交链接。

2、使用定时任务可以向搜索引擎提交最新、随机数量的文章链接。

3、可自由设置提交的搜索引擎、定时提交周期时间、提交随机还是最新的文章链接、提交文章链接的数量。

4、可以查看推送记录，例如：推送链接明细、推送结果、推送是否成功等其它推送内容。

== Installation ==

1. 进入WordPress网站后台，找到“插件-安装插件”菜单；
2. 点击界面左上方的“上传插件”按钮，选择本地提前下载好的插件压缩包文件（zip格式），点击“立即安装”；
3. 安装完成后，启用 “果果推送” 插件；
4. 通过“设置”链接进入插件设置界面；
5. 完成设置后，插件就安装完毕了。

<a href="https://www.ggdoc.cn/chapter/pnzztu9qisfvyybg2e8nmeucuwkftefu.html" rel="friend">为了能够定时推送文章链接，请点击此链接进行WP-Cron配置</a>

== Frequently Asked Questions ==

= 百度搜索引擎的准入密钥在哪里获取？ =
打开百度站长平台的普通收录页面，找到API提交的接口调用地址。
例如：接口调用地址为http://data.zz.baidu.com/urls?site=您的网站主页网址&token=准入密钥
则准入密钥为token=后面的字符串。

= 必应搜索引擎的API密钥在哪里获取？ =
可以在必应的站长平台右上方的设置按钮中找到。

= IndexNow的密钥在哪里获取？ =
不需要获取，只需要在插件设置那里填写32位随机字符串，插件会自动生成密钥验证文件。

== Screenshots ==

1. 百度搜索引擎普通收录Api提交设置界面
2. 百度搜索引擎快速收录Api提交设置界面
3. 微软必应搜索引擎Api提交设置界面
4. IndexNow Api提交设置界面
5. 推送记录列表页面
6. 推送记录详情页面

== Upgrade Notice ==

= 0.0.1 =
参考Changelog说明

== Changelog ==

= 0.0.1 =
* 新增百度搜索引擎普通、快速收录功能
* 新增微软Bing搜索引擎Api推送文章链接功能
* 新增IndexNow推送文章链接功能
* 新增发布新文章时推送文章链接功能
* 新增文章链接推送记录功能