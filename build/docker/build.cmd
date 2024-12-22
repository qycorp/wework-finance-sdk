@echo off

:: 检查 Docker 是否已安装
docker --version >nul 2>&1
if %errorlevel% equ 0 (
    echo Docker has been installed.
) else (
    echo Docker is not installed, installation is in progress

    :: 下载 Docker Desktop 安装程序
    set DOWNLOAD_URL=https://desktop.docker.com/win/stable/Docker%20Desktop%20Installer.exe
    set INSTALLER_PATH=%TEMP%\DockerDesktopInstaller.exe

    echo Downloading Docker installation program...
    powershell -Command "(New-Object Net.WebClient).DownloadFile('%DOWNLOAD_URL%', '%INSTALLER_PATH%')"

    :: 安装 Docker
    echo Installing Docker...
    start /wait %INSTALLER_PATH%

    :: 清理安装文件
    del /f /q %INSTALLER_PATH%

    echo Docker Installed.
)

:: 检查参数
if "%~1"=="" (
    echo Usage: %0 {install^|uninstall}
    exit /b 1
)

set image_name=weworkmsg:latest
set container_name=weworkmsg
set action=%1

if /I "%action%"=="install" (
    echo Building Docker images...
    docker build -t %image_name% .
    echo Starting services...
    docker run -itd --restart=always --privileged=true -h %container_name% --name=%container_name% -p 7149:7149 %image_name%
    echo Installation complete!
) else if /I "%action%"=="uninstall" (
    echo Stopping and removing services...
    docker stop %container_name% >nul 2>&1
    docker rm %container_name% >nul 2>&1
    echo Removing Docker images...
    docker rmi %image_name%
    echo Uninstallation complete!
) else (
    echo Invalid option: %1
    echo Usage: %0 {install^|uninstall}
    exit /b 1
)