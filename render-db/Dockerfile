FROM mariadb:10.11
COPY init.sql /docker-entrypoint-initdb.d/init.sql
CMD ["--datadir=/var/lib/mysql/data", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci"]
