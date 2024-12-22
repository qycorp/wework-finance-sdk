#!/bin/sh

VERSION=$1
FILENAME=php-$VERSION.tar.bz2

if [ -z "$VERSION" ]; then
    echo "请输入你要安装的PHP版本号"
    exit 1
fi 

# 检测wget是否已经安装
if command -v wget >/dev/null 2>&1; then
    echo "wget 已经安装."
else
    echo "wget 没有安装，正在进行安装..."

    # 获取当前的操作系统类型
    if [ -f /etc/debian_version ]; then
        # Debian/Ubuntu 系列
        apt update
        apt install -y wget
    elif [ -f /etc/redhat-release ]; then
        # RedHat/CentOS 系列
        yum install -y wget
    elif [ -f /etc/fedora-release ]; then
        # Fedora 系列
        dnf install -y wget
    elif [ -f /etc/arch-release ]; then
        # Arch Linux
        pacman -S --noconfirm wget
    elif [ -f /etc/alpine-release ]; then
        # Alpine Linux
        apk add --no-cache wget
    elif command -v zypper >/dev/null 2>&1; then
        # openSUSE 系列
        zypper install -y wget
    else
        echo "无法识别当前系统，无法安装 wget。"
        exit 1
    fi
    echo "wget 安装完成."
fi

echo "检查 phpize 是否可用..."

if ! command -v phpize >/dev/null 2>&1; then
    echo "phpize 没有安装，请确保 PHP 已安装并包含开发文件。"
    exit 1
fi

wget https://www.php.net/distributions/$FILENAME
tar -xvjf $FILENAME
cd php-$VERSION/ext/ffi && $(which phpize) && ./configure --enable-ffi && make && make install

cd ../../../ && rm -rf php*

echo "extension=ffi" >> /usr/local/etc/php/conf.d/ffi.ini