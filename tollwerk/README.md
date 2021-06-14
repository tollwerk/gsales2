# Sendmail-Konfiguration

Zur pauschalen Anforderung von Empfangsbestätigungen muss folgendes in der *sendmail*-Konfiguration `/etc/mail/sendmail.mc` ergänzt werden:

```
LOCAL_CONFIG
HDisposition-Notification-To: <$g>
```

Sodann muss die Sendmail-Konfiguration aktualisiert werden:

```bash
cd /etc/mail
m4 sendmail.mc > sendmail.cf
/etc/init.d/sendmail restart
```

# Fälligkeitsdaten von Serienvorlagen

Unsere Serienvorlagen sollten immer mit dem Monatsersten als Fälligkeitstag angelegt sein. Da gsales das Fälligkeitsdatum allerdings ausdrücklich mit dem Anlagedatum gleichsetzt und nicht eigens bearbeiten lässt, sollte das Fälligkeitsdatum automatisch angepasst werden. Das folgende SQL-Statement erledigt diese Aufgabe:

```sql
UPDATE `contracts` SET duedate = DATE_FORMAT(duedate, "%Y-%m-01")
```

Auf dem Server ist ein Cronjob eingerichtet, der genau diese Aufgabe einmal täglich übernimmt.