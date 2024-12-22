# 使用更小的镜像（如果只需要运行，不需要构建工具）
FROM alpine:latest
# 设置时区
ENV TZ=Asia/Shanghai
ENV WECOMMSG_HOST=0.0.0.0
ENV WECOMMSG_PORT=7149
# 维护者信息
LABEL "website.name"="okxaas"
# 安装依赖
RUN apk --no-cache add tzdata ca-certificates libc6-compat libgcc libstdc++
# 从构建阶段复制二进制文件
COPY ./weworkmsg /usr/bin/
COPY ./libWeWorkFinanceSdk_C.so /lib/
# 开放端口
EXPOSE 7149
# 启动
CMD [ "/usr/bin/weworkmsg" ]