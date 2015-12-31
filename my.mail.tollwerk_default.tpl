{$mail.titel} {$invoice.base.invoiceno} vom {$invoice.base.created|date_format}
[MAIL-NEXT-PART]

{if $customer.customerno}Ihre Kundennummer: {$customer.customerno}{/if}

{if $customer.title == 'Herr' && $customer.lastname}
Sehr geehrter Herr {$customer.lastname},
{elseif $customer.title == 'Frau' && $customer.lastname}
Sehr geehrte Frau {$customer.lastname},
{else}
Sehr geehrte Damen und Herren,
{/if}

im Anhang erhalten Sie {$mail.anredepronomen_plus_titel} {$invoice.base.invoiceno} vom {$invoice.base.created|date_format} als PDF-Dokument. Zum Lesen und Ausdrucken benötigen Sie den Adobe Reader [http://www.adobe.de/products/acrobat/readstep2.html] oder ein vergleichbares Programm zur Verarbeitung von PDF-Dokumenten.{if $customer.dtaus == 1 && $invoice.type == 'invoices'}

Der Betrag wird wie vereinbart in den nächsten Tagen von Ihrem Konto abgebucht.{/if}

Bitte beachten Sie, dass wir Belege ab dem 1.1.2016 standardmäßig nur noch per E-Mail versenden. Sollten Sie in Einzelfällen einen urschriftlichen, papierhaften Beleg benötigen, So bitten wir Sie, uns durch Antwort auf diese Nachricht davon zu unterrichten.

Mit besten Grüßen aus dem Tollwerk!

{if $customer.frontend_active == 1 && $frontend_active}Unter der Adresse {$data.url} finden Sie unseren Kundenbereich. Dort können Sie sich vergangene Rechnungen und Gutschriften ansehen und herunterladen. Außerdem erhalten Sie eine Übersicht Ihrer wiederkehrenden Vertragspositionen, sowie Ihrer Kundendaten.

{/if}[signature_plain]

[MAIL-NEXT-PART]

<html>
<body>

{if $customer.customerno}<p>Ihre Kundennummer: {$customer.customerno}</p>{/if}

<p>
{if $customer.title == 'Herr' && $customer.lastname}
Sehr geehrter Herr {$customer.lastname},
{elseif $customer.title == 'Frau' && $customer.lastname}
Sehr geehrte Frau {$customer.lastname},
{else}
Sehr geehrte Damen und Herren,
{/if}
</p>

<p>
im Anhang erhalten Sie {$mail.anredepronomen_plus_titel} <b>{$invoice.base.invoiceno}</b> vom {$invoice.base.created|date_format} als PDF-Dokument. Zum Lesen und Ausdrucken benötigen Sie den Adobe Reader [<a href="http://www.adobe.de/products/acrobat/readstep2.html">http://www.adobe.de/products/acrobat/readstep2.html</a>] oder ein vergleichbares Programm zur Verarbeitung von PDF-Dokumenten.
</p>

{if $customer.dtaus == 1 && $invoice.type == 'invoices'}<p>Der Betrag wird wie vereinbart in den nächsten Tagen von Ihrem Konto abgebucht.</p>{/if}

<p>Bitte beachten Sie, dass wir Belege ab dem 1.1.2016 standardmäßig nur noch per E-Mail versenden. Sollten Sie in Einzelfällen einen urschriftlichen, papierhaften Beleg benötigen, So bitten wir Sie, uns durch Antwort auf diese Nachricht davon zu unterrichten.</p>

{if $customer.frontend_active == 1 && $frontend_active}<p>Unter der Adresse {$data.url} finden Sie unseren Kundenbereich. Dort können Sie sich vergangene Rechnungen und Gutschriften ansehen und herunterladen. Außerdem erhalten Sie eine Übersicht Ihrer wiederkehrenden Vertragspositionen, sowie Ihrer Kundendaten.</p>

{/if}<p>Mit besten Grüßen aus dem Tollwerk!</p>

[signature_html]

</body>
</html>