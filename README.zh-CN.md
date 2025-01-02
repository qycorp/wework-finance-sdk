# 企业微信会话内容存档PHP扩展

[English](./README.md) · **简体中文**

#### 本扩展包提供了企业微信会话内容存档服务的PHP接入能力，支持以下三种接入方式：
1. API方式 - 通过Docker启动API服务（基于GO实现的API）
2. FFI方式 - 通过PHP FFI扩展调用官方SDK
3. 扩展方式 - 使用编译好的PHP原生扩展

#### 环境要求

- 如使用FFI方式需安装PHP FFI扩展
- 如使用扩展方式需编译安装扩展
- PHP <= 7.3 建议使用API或原生扩展方式对接
- PHP >= 7.4 建议使用FFI或API方式对接

#### 功能特性

- 支持获取会话存档内容
- 支持解密会话消息
- 支持获取媒体文件
- 支持多种消息类型:文本、图片、语音、视频、文件等
- 支持通过版本号指定多个私钥进行解密
- 数据结构一致，您只需要设置SDK的方式即可快速使用

## 快速使用

使用 Composer 安装：
```bash
composer require x2nx/wework-finance-sdk
```
代码示例
```
$sdk = new \X2nx\WeworkFinance\SDK([
    // 'api_base' => '自定义API服务地址' provider = api 时可用
    'provider'  => 'api', // ffi ext api 三种方式可选 默认api方式
    'corpid'    => 'xxxxxxxxxxxxxxxxxxxx',
    'secret'    => 'xxxxxxxxxxxxxxxxxxxx',
    'private_keys' => [
        '版本号' => '解密私钥'
    ]
]);

$data = $sdk->getDecryptChatData(0, 50, 3);
```

## 构建工具包
`build` 目录包含三个脚本，用于帮助构建和管理 SDK：

1. `build.sh` - 为API服务构建 Docker 镜像
```bash
# 进入目录
cd build/docker/
#构建API服务Docker镜像进行安装及卸载
./build.sh install
# 卸载镜像及容器
./build.sh uninstall
# 你也可以使用我们制作的docker Hub镜像一键启动
docker run -itd --restart=always --privileged=true -h weworkmsg --name=weworkmsg -e WECOMMSG_HOST=0.0.0.0 -p 7149:7149 x2nx/wework-finances-api:1.0.0
```
2. `ffi.sh` - 编译PHP原生FFI扩展
```bash
# 进入目录
cd build/
# 为 FFI 模式构建 SDK 库文件 使用示例：./ffi.sh 7.4.33
./ffi.sh {php version}
```

3. `wxwork_finance_sdk.sh` - 编译 PHP wxwork_finance_sdk扩展
```bash
# 进入目录
cd build/
# 编译并安装 PHP 扩展
./wxwork_finance_sdk.sh
```
根据您的首选访问方式选择适当的构建脚本：
- 对于API模式：简单快捷一键启动，不需要考虑环境等问题
- 对于扩展模式：PHP7.4一下使用，编译扩展较为繁琐
- 对于FFI模式：PHP7.4以上使用
- 由于alpine对glibc存在兼容性问题，所以扩展编译安装会出现安装成功无法运行的问题，推荐使用`API`方式使用
- 在已安装PHP环境的情况下，脚本可正常运行

## 参与贡献
我们非常欢迎各种形式的贡献。如果您在使用过程中发现任何代码问题或环境配置问题，欢迎在 GitHub [Issues][github-issues-link] 中提交反馈。同时也欢迎您对功能和使用体验提出建议，帮助我们不断改进。

## 感谢及相关链接
+ https://github.com/pangdahua/php7-wxwork-finance-sdk
+ https://open.work.weixin.qq.com/api/doc/90000/90135/91774
+ https://developer.work.weixin.qq.com/document/path/91774

## Copyright and license

The MIT License (MIT) [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
