FROM public.ecr.aws/bmlt/bmlt-root-server-base:1.1.2

COPY build/bmlt-root-server.zip /tmp/bmlt-root-server.zip
RUN \
  unzip /tmp/bmlt-root-server.zip -d /var/www/html \
  && rm -f /tmp/bmlt-root-server.zip \
  && chown -R www-data: /var/www/html

COPY docker/start-bmlt.sh /tmp/start-bmlt.sh

CMD ["/bin/bash", "/tmp/start-bmlt.sh"]
