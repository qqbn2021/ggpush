# 果果推送（WordPress插件）

支持百度搜索引擎、微软Bing搜索引擎以及IndexNow方式的Api提交链接功能，让搜索引擎更快的发现您网站上的新内容链接。

1、发布新文章时，自动向搜索引擎提交链接。

2、使用定时任务可以向搜索引擎提交最新、随机数量的文章链接。

3、可自由设置提交的搜索引擎、定时提交周期时间、提交随机还是最新的文章链接、提交文章链接的数量。

4、可以查看推送记录，例如：推送链接明细、推送结果、推送是否成功等其它推送内容。

**安装方法**

方式一：

① 【开发中代码】从 https://github.com/qqbn2021/ggpush 下载压缩包源码（点击`Code` - `Download ZIP`）。解压成功后将`ggpush-main`文件夹名称改为`ggpush`文件夹名称。

② 【开发中代码】从 https://github.com/qqbn2021/ggpush 克隆代码（git clone https://github.com/qqbn2021/ggpush.git），这个时候，文件夹的名称默认就是`ggpush`，就不需要改文件夹名称。

③ 【已发布稳定代码】【推荐】从 https://github.com/qqbn2021/ggpush/tags 页面下载压缩包源码，解压后将文件夹名称（原文件夹名称为：`ggpush-版本号`）改为`ggpush`。

如果想要从网站后台直接安装插件，需要将`ggpush`文件夹（不是对`ggpush`文件夹内的文件压缩，是对`ggpush`文件夹压缩）压缩成zip格式的压缩包（打开压缩包只能看到`ggpush`文件夹则证明压缩正确），即可直接在后台安装。

如果拥有代码上传的权限，可以将`ggpush`文件夹和其文件夹下的所有文件上传至WordPress项目的`wp-content/plugins`文件夹下，之后刷新插件列表页面即可。

**插件设置**

一、百度搜索引擎的准入密钥为：

打开百度站长平台的普通收录页面，找到API提交的接口调用地址。

例如：接口调用地址为http://data.zz.baidu.com/urls?site=您的网站主页网址&token=准入密钥

则准入密钥为`token=`后面的字符串。

二、必应搜索引擎的API密钥可以在必应的站长平台右上方的设置按钮中找到。

三、IndexNow的密钥为随机32位字符串，程序在您未设置或设置为空的时候自动生成一个32位字符串，您可以根据情况自行设置，点击保存更改按钮后，插件会在您的网站根目录下创建一个类似于ggpush_`32位字符串`.txt的密钥文件，用于indexnow推送链接验证。

其它的设置就不介绍了，您应该看得懂。

**定时推送文章链接设置**

需要定时访问：https://您的网站域名/wp-cron.php 这个链接，达到定时推送文章链接的作用。

详细设置可以参考：https://www.ggdoc.cn/chapter/pnzztu9qisfvyybg2e8nmeucuwkftefu.html

**IndexNow推送状态码**

推送状态码返回200，代表推送成功。

推送状态码返回202，代表链接提交成功了，但是，IndexNow需要验证密钥文件。

具体可以参考：https://www.indexnow.org/zh_cn/documentation

**二次开发以及bug**

如果想要对插件进行二次开发，或者插件有bug，可以点击下方链接联系我。

https://www.ggdoc.cn/contact.html

**果果插件会做哪些事？**

当您安装插件后：会将插件代码（`ggpush`文件夹）全部上传至`wp-content/plugins`目录。

当您启用插件后：会在数据库创建一张表（`表前缀_ggpush_records`），用来存放推送记录。

当您点击设置保存按钮后：会在您的网站根目录下创建IndexNow推送密钥文本文件，例如：`ggpush_【IndexNow 32位推送密钥字符串】.txt`，同时根据设置中启用的搜索引擎创建计划任务（最多4个计划任务，可以在推送记录页面下方看到）。

当您禁用插件时：会移除计划任务。

当您删除插件时：会删除创建的表（`表前缀_ggpush_records`），会移除计划任务，会删除配置数据。注意：网站根目录下创建的IndexNow推送密钥文本文件可能需要您自行删除。

