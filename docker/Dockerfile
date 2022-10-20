FROM bmltenabled/bmlt-root-server-base:latest

COPY build/bmlt-root-server.zip /tmp/bmlt-root-server.zip
RUN \
  echo '<html><head><meta http-equiv="refresh" content="0; URL=main_server/" /></head><body>OK</body></html>' \
    >> /var/www/html/index.html \
  && unzip /tmp/bmlt-root-server.zip -d /var/www/html \
  && rm -f /tmp/bmlt-root-server.zip \
  && chown -R www-data: /var/www/html

# Handle mod_dir redirect using X-Forwarded headers
COPY docker/.htaccess /var/www/html/main_server/.htaccess

COPY docker/start-bmlt.sh /tmp/start-bmlt.sh

CMD ["/bin/bash", "/tmp/start-bmlt.sh"]
