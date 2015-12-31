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