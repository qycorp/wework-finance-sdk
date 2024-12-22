#!/bin/sh

# 检测wget是否已经安装
if command -v git >/dev/null 2>&1; then
    echo "git 已经安装."
else
    echo "git 没有安装，正在进行安装..."

    # 获取当前的操作系统类型
    if [ -f /etc/debian_version ]; then
        # Debian/Ubuntu 系列
        apt update
        apt install -y git
    elif [ -f /etc/redhat-release ]; then
        # RedHat/CentOS 系列
        yum install -y git
    elif [ -f /etc/fedora-release ]; then
        # Fedora 系列
        dnf install -y git
    elif [ -f /etc/arch-release ]; then
        # Arch Linux
        pacman -S --noconfirm git
    elif [ -f /etc/alpine-release ]; then
        # Alpine Linux
        apk add --no-cache git
    elif command -v zypper >/dev/null 2>&1; then
        # openSUSE 系列
        zypper install -y git
    else
        echo "无法识别当前系统，无法安装 git"
        exit 1
    fi
    echo "git 安装完成."
fi

echo "检查 phpize 是否可用..."

if ! command -v phpize >/dev/null 2>&1; then
    echo "phpize 没有安装，请确保 PHP 已安装并包含开发文件。"
    exit 1
fi

if ! command -v php-config >/dev/null 2>&1; then
    echo "php-config 没有安装，请确保 PHP 已安装并包含开发文件。"
    exit 1
fi

# 检查目录是否存在
if [ ! -d "wxwork-finance-sdk" ]; then
    git clone https://github.com/pangdahua/php7-wxwork-finance-sdk.git wxwork-finance-sdk
fi

TARGET_LIB_DIR=$(realpath ../lib/)

cd wxwork-finance-sdk && $(which phpize) && ./configure --with-php-config=$(which php-config) --with-wxwork-finance-sdk=$TARGET_LIB_DIR && make && make install && cd .. && rm -rf wxwork-*

echo "extension=wxwork_finance_sdk" >> /usr/local/etc/php/conf.d/wxwork_finance_sdk.ini