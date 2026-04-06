# Comment line
FROM ubuntu:22.04 AS builder

# ARG before FROM
ARG VERSION=1.0

# Labels
LABEL maintainer="user@example.com"
LABEL version="1.0" \
      description="Multi-line label"

# Environment variables
ENV NODE_ENV=production
ENV PATH=/usr/local/bin:$PATH \
    HOME=/root

# Working directory
WORKDIR /app

# Copy files
COPY package.json .
COPY --chown=user:group src/ /app/src/

# Add files (with URL)
ADD https://example.com/file.tar.gz /tmp/
ADD archive.tar.gz /extracted/

# Run commands
RUN apt-get update
RUN apt-get install -y \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# User
USER node
USER 1000:1000

# Expose ports
EXPOSE 80
EXPOSE 443/tcp
EXPOSE 8080/udp

# Volume
VOLUME ["/data"]
VOLUME /var/log /var/db

# Entrypoint
ENTRYPOINT ["node"]
ENTRYPOINT node app.js

# Command
CMD ["start"]
CMD npm start

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s \
  CMD curl -f http://localhost/ || exit 1

# Shell
SHELL ["/bin/bash", "-c"]

# Stopsignal
STOPSIGNAL SIGTERM

# Onbuild
ONBUILD RUN echo "Building child image"
ONBUILD COPY . /app

# Arg with default
ARG BUILD_DATE
ARG VCS_REF=master

# Multi-stage
FROM alpine:latest
COPY --from=builder /app/dist /app/

# Maintainer (deprecated but still valid)
MAINTAINER Old Style <old@example.com>
