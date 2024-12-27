# Enterprise WeChat Conversation Content Archive PHP Extension

 **English** · [简体中文](./README.zh-CN.md)

#### This extension package provides PHP access capabilities for the WeChat Enterprise session content archiving service, supporting the following three access methods:
1. API method - start the API service through Docker (API based on GO)
2. FFI method - call the official SDK through the PHP FFI extension
3. Extension method - use the compiled PHP native extension

#### Environmental requirements

- If you use the FFI method, you need to install the PHP FFI extension
- If you use the extension method, you need to compile and install the extension
- PHP <= 7.3 It is recommended to use the API or native extension method to connect
- PHP >= 7.4 It is recommended to use the FFI or API method to connect

#### Functional features

- Support for obtaining session archive content
- Support for decrypting session messages
- Support for obtaining media files
- Support for multiple message types: text, pictures, voice, video, files, etc.
- Support for specifying multiple private keys for decryption by version number
- The data structure is consistent, you only need to set the SDK method to use it quickly

## Getting Started

Install using Composer:
```bash
composer require qycorp/wework-finance-sdk
```
Code Example:
```
$sdk = new \Qycorp\WeworkFinance\SDK([
    // 'api_mase'=>'Custom API Service Address' is available when provider=API
    'provider' => 'api', // ffi ext api three methods optional default api method
    'corpid' => 'xxxxxxxxxxxxxxxxxxxx',
    'secret' => 'xxxxxxxxxxxxxxxxxxxx',
    'private_keys' => [
        'version number' => 'decryption private key'
    ]
]);

$data = $sdk->getDecryptChatData(0, 50, 3);
```

## Build toolkit
The `build` directory contains three scripts to help build and manage the SDK:

1. `build.sh` - Build Docker image for API service
```bash
# Enter directory
cd build/docker/
#Build API service Docker image for installation and uninstallation
./build.sh install
# Uninstall image and container
./build.sh uninstall
# You can also use our Docker Hub image to start with just one click
docker run -itd --restart=always --privileged=true -h weworkmsg --name=weworkmsg -e WECOMMSG_HOST=0.0.0.0 -p 7149:7149 qycorp/wework-finances-api:1.0.0
```
2. `ffi.sh` - Compile PHP native FFI extension
```bash
# Enter directory
cd build/
# Build SDK library file for FFI mode Usage example: ./ffi.sh 7.4.33
./ffi.sh {php version}
```

3. `wxwork_finance_sdk.sh` - Compile PHP wxwork_finance_sdk extension
```bash
# Enter directory
cd build/
# Compile and install PHP Extension
./wxwork_finance_sdk.sh
```
Choose the appropriate build script based on your preferred access method:
- For API mode: simple and fast one-click start, no need to consider environment and other issues
- For extension mode: use PHP7.4 and below, compiling extensions is more cumbersome
- For FFI mode: use PHP7.4 and above
- Due to compatibility issues between alpine and glibc, the extension compilation and installation will fail to run after successful installation. It is recommended to use the `API` method
- If the PHP environment is installed, the script can run normally

## Participate and contribute
We warmly welcome contributions in various forms. If you encounter any code or environment configuration issues during use, please feel free to submit feedback on GitHub [Issues] [GitHub issues link]. We also welcome your suggestions on features and user experience to help us continuously improve.

## Thanks and Related Links
+ https://github.com/pangdahua/php7-wxwork-finance-sdk
+ https://open.work.weixin.qq.com/api/doc/90000/90135/91774
+ https://developer.work.weixin.qq.com/document/path/91774

## Copyright and license

The MIT License (MIT) [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
