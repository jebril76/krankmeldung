Hi, 
Docker Container der auf Basis von Laravel eine App bereitstellt.
Die App wurde für das Sekretariat einer Schule in NRW entwickelt.
Mit ihr können Krankmeldungen von Schülern schnell erfasst und verwaltet werden.
Der Zugang ist nicht geschützt.

Voraussetzungen:
1. Ein Server mit root-shell.

Installation
1. Installiere Docker-Compose und git. (z.B. unter Debian: "apt-get update && apt-get install -y docker-compose git")
1. mkdir /opt && cd /opt
1. git clone https://github.com/jebril76/Krankmeldung.git && rm -rf Krankmeldung/.git
2. .env anpassen.
3. docker-compose up -d

Zugang über http://***APP_URL***/m

