FROM public.ecr.aws/bmlt/bmlt-root-server-base:1.1.0

RUN rm /var/www/html/index.html
ADD /src /var/www/html
RUN chown -R www-data: /var/www/html

COPY start-bmlt.sh /tmp/start-bmlt.sh

CMD ["/bin/bash", "/tmp/start-bmlt.sh"]
