FROM debian:$DOCKER_DEBIAN_TAG
RUN apt-get update && apt-get install -y apache2 libapache2-mod-php5
RUN apt-get install -y wget
COPY nspages.tgz installTestEnvironment.sh testEnvironment dw_dl_cache source.sh /home/
RUN cd /home \
      && mkdir nspages \
      && cd nspages \
      && mv ../nspages.tgz . \
      && tar xf nspages.tgz \
      && rm nspages.tgz \
      && mkdir _tests \
      && cd _tests \
      && mv /home/installTestEnvironment.sh .\
      && mv /home/source.sh .\
      && mkdir testEnvironment \
      && mv /home/data testEnvironment \
      && mkdir dw_dl_cache \
      && mv /home/dokuwiki-*.tgz dw_dl_cache \
      && chmod u+x installTestEnvironment.sh \
      && /etc/init.d/apache2 start \
      && SERVER_FS_ROOT=$SERVER_FS_ROOT ./installTestEnvironment.sh

CMD /etc/init.d/apache2 start && tail -F /var/log/apache2/access.log
