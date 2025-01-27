Hi, <br>
Docker Container der auf Basis von Laravel eine App bereitstellt.<br>
Die App wurde für das Sekretariat einer Schule in NRW entwickelt.<br>
Mit ihr können Krankmeldungen von Schülern schnell erfasst und verwaltet werden.<br>
Der Zugang ist nicht geschützt. Daher sollte die App nur im lokalen Verwaltungsnetz abgelegt werden.<br>

Voraussetzungen:
1. Ein debian basierter amd64 Server mit Root-Shell.
2. Installiere Docker-Compose und git. (z.B. unter Debian: "apt-get update && apt-get install -y docker-compose git")

Installation
1. mkdir /opt (Fehler ignorieren)
2. cd /opt && git clone https://github.com/jebril76/krankmeldung.git && rm -rf krankmeldung/.git && cd krankmeldung && chmod 700 ./setup.sh && ./setup.sh
3. Hostnamen des Servers eingeben.
4. Für das EMail-Backup die Emailadresse und das Passwort eingeben.
5. Abwarten und Tee trinken.

Zugang Verwaltung über http://**HOSTNAME**/m<br>
Zugang Lehrer über http://**HOSTNAME**/l<br>
Zugang InfoScreen über http://**HOSTNAME**/i<br>

