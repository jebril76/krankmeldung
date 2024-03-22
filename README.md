Hi, 
Docker Container der auf Basis von Laravel eine App bereitstellt.
Die App wurde für das Sekretariat einer Schule in NRW entwickelt.
Mit ihr können Krankmeldungen von Schülern schnell erfasst und verwaltet werden.
Der Zugang ist nicht geschützt. Daher sollte die App nur im lokalen Verwaltungsnetz abgelegt werden.

Voraussetzungen:
1. Ein Server mit Root-Shell.

Installation
1. Installiere Docker-Compose und git. (z.B. unter Debian: "apt-get update && apt-get install -y docker-compose git")
2. mkdir /opt (Fehler ignorieren)
3. cd /opt && git clone https://github.com/jebril76/krankmeldung.git && rm -rf krankmeldung/.git && cd krankmeldung && chmod 700 ./setup.sh && ./setup.sh

Zugang über http://***APP_URL***/m

