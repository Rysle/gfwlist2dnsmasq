# gfwlist2dnsmasq
---
### 这是什么？
gfwlist2dnsmasq是一个简单的php程序，用于将gfwlist转换成dnsmasq的配置文件。
另外也它也带有一个简单的shell脚本，可以运行在openwrt路由器上，自动下载最新的dnsmasq配置文件并应用。

它有以下功能：

* 下载 [gfwlist](https://autoproxy-gfwlist.googlecode.com/svn/trunk/gfwlist.txt)，提取其中的域名（当然URL是可以自定义的）
* 根据配置的dns服务器、ipset名称，生成dnsmasq.conf
* 可以配置最小更新间隔，在间隔时间内只会更新一次gfwlist
* 可以定义自己的extra域名列表，每次生成dnsmasq.conf和gfwlist中的域名合并输出
* 可以按日期记录当天的gfwlist域名列表，并与前一天的列表对比生成diff
* 可以将diff转换成图片，发送到配置的邮箱中，通知你有什么更新
* 可以生成当前dnsmasq.conf的md5，便于客户端进行校验

### 运行要求
#### 服务器端（以Linux环境为例）
* curl扩展
* gd2扩展（可选）
* sendmail（可选）

#### 客户端（OpenWRT）
* wget
* base64
* md5sum

### 如何使用？
#### 服务器端配置
1. 确保服务器已经启用了curl扩展
2. clone分支
3. 修改config.php
4. 配置data/gfwlist\_domain_extra.txt（可选）

访问http://xxx/?get=1，如果看到base64编码输出，则说明已经配置成功。

如果需要debug信息，则访问http://xxx/?debug=1，更详细的debug开关可以参考config.php中的说明。

#### 客户端配置
1. 确保已经有wget、base64、md5sum命令
2. 将updategfwlist.sh上传到路由器，并赋予执行权限
3. 将updategfwlist.sh添加到定时任务中，根据自己的需要确定时间间隔，一般6小时执行一次即可
4. 手动执行一遍updategfwlist.sh，如果看到下面的提示，则说明更新成功

> == md5 checked: OK


### History
* 2014.8.3 实现gfwlist的下载和域名提取。
* 2014.8.4 实现dnsmasq.conf的生成；支持更新时间限制
* 2014.8.5 实现按日期存储dnsmasq.conf，生成diff
* 2014.8.6 实现将diff转成图片，并通过邮件发现更新提醒
* 2014.8.8 重构，将通用方法、gfwlist相关逻辑、配置文件分别抽离
* 2014.8.10 updategfwlist.sh增加对dnsmasq.conf的md5校验；邮件编码逻辑优化。

### Todo
1. 为diff、diff2image、sendmail等功能加上开关
2. updategfwlist.sh逻辑优化，如果正在使用的dnsmasq.conf和新下载的md5一致，则忽略本次更新
