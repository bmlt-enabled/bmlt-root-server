FROM public.ecr.aws/bmlt/bmlt-root-server-base:latest

RUN rm /var/www/html/index.html
ADD /src /var/www/html
RUN chown -R www-data: /var/www/html
