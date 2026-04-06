# Edge cases for Dockerfile coverage

# Shell operators in RUN commands
RUN apt-get update && apt-get install -y curl
RUN command1 || command2
RUN cmd1; cmd2 | cmd3

# Variables - both $VAR and ${VAR} forms
ENV VAR1=value
RUN echo $VAR1
RUN echo ${VAR1}
RUN echo $VAR_WITH_UNDERSCORE
RUN path=${HOME}/bin

# Edge case: $ without variable name
RUN echo "Price: $$"

# Quoted strings with escaping
RUN echo "Line 1\nLine 2"
RUN echo 'Single \'quoted\' string'
RUN mixed "double" and 'single' quotes

# Bare numbers
EXPOSE 8080
USER 1000
ARG PORT=3000

# AS and NONE modifiers
FROM ubuntu AS builder
HEALTHCHECK NONE

# Line continuations
RUN apt-get install -y \
    package1 \
    package2 \
    package3

LABEL description="Multi \
line \
label"

# Unrecognized instruction (should be plain text)
INVALID_INSTRUCTION some arguments

# Edge: empty instruction
