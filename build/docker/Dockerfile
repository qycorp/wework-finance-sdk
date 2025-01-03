# 使用更小的镜像（alpine）
FROM alpine:latest
# 设置时区
ENV TZ=Asia/Shanghai
# 设置应用监听地址和端口
ENV WECOMMSG_HOST=0.0.0.0
ENV WECOMMSG_PORT=7149
# 维护者信息
LABEL "website.name"="okxaas"
# 安装基础依赖并清理缓存，减少镜像大小
RUN apk --no-cache add \
    tzdata \
    ca-certificates \
    libc6-compat \
    libgcc \
    libstdc++ && \
    # 设置时区
    cp /usr/share/zoneinfo/$TZ /etc/localtime && \
    # 清理不必要的文件
    apk del tzdata && \
    # 更新证书
    update-ca-certificates
# 复制构建阶段的文件
COPY ./weworkmsg /usr/bin/
COPY ./libWeWorkFinanceSdk_C.so /lib/
# 设置开放的端口
EXPOSE 7149
# 启动应用
CMD [ "/usr/bin/weworkmsg" ]
