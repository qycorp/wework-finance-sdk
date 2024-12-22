#!/bin/sh

IMAGE_NAME=weworkmsg:latest
CONTAINER_NAME=weworkmsg

# 检测docker是否已经安装
if command -v docker >/dev/null 2>&1; then
    echo "Docker 已经安装."
else
    echo "Docker 没有安装，正在进行安装..."

    # 获取当前的操作系统类型
    if [ -f /etc/debian_version ]; then
        # Debian/Ubuntu 系列
        apt update
        apt install -y apt-transport-https ca-certificates curl software-properties-common
        curl -fsSL https://get.docker.com -o get-docker.sh
        sh get-docker.sh
    elif [ -f /etc/redhat-release ]; then
        # RedHat/CentOS 系列
        yum install -y yum-utils
        yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
        yum install -y docker-ce docker-ce-cli containerd.io
    elif [ -f /etc/fedora-release ]; then
        # Fedora 系列
        dnf install -y dnf-plugins-core
        dnf config-manager --add-repo https://download.docker.com/linux/fedora/docker-ce.repo
        dnf install -y docker-ce docker-ce-cli containerd.io
    elif [ -f /etc/arch-release ]; then
        # Arch Linux
        pacman -Syu --noconfirm docker
    elif [ -f /etc/alpine-release ]; then
        # Alpine Linux
        apk add --no-cache docker
    elif command -v zypper >/dev/null 2>&1; then
        # openSUSE 系列
        zypper install -y docker
    else
        echo "无法识别当前系统，无法安装 Docker。"
        exit 1
    fi

    # 启动并启用Docker服务
    systemctl start docker
    systemctl enable docker

    echo "Docker 安装完成."
fi

# 检查参数
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 {install|uninstall}"
    exit 1
fi

case $1 in
    install)
        echo "Building Docker images..."
        docker build -t $IMAGE_NAME .
        echo "Starting services..."
        docker run -itd --restart=always --privileged=true -h $CONTAINER_NAME --name=$CONTAINER_NAME -p 7149:7149 $IMAGE_NAME
        echo "Installation complete!"
        ;;
    uninstall)
        echo "Stopping and removing services..."
        docker stop $CONTAINER_NAME >nul 2>&1
        docker rm $CONTAINER_NAME >nul 2>&1
        # 请根据你的镜像名称替换 myapp:latest
        echo "Removing Docker images..."
        docker rmi $IMAGE_NAME
        echo "Uninstallation complete!"
        ;;
    *)
        echo "Invalid option: $1"
        echo "Usage: $0 {install|uninstall}"
        exit 1
        ;;
esac
