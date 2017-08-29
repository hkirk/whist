FROM mysql/mysql-server:latest

ENV MYSQL_ROOT_PASSWORD=12345
ENV MYSQL_DATABASE=whist
ENV MYSQL_ROOT_HOST=172.17.0.1

COPY ./init.sql /docker-entrypoint-initdb.d/

EXPOSE 3306

CMD ["mysqld"]
