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

im Anhang erhalten Sie {$mail.anredepronomen_plus_titel} {$invoice.base.invoiceno} vom {$invoice.base.created|date_format} als PDF-Dokument. Zum Lesen und Ausdrucken benötigen Sie den Adobe Reader [https://get.adobe.com/de/reader/] oder ein vergleichbares Programm zur Verarbeitung von PDF-Dokumenten.{if $customer.dtaus == 1 && $invoice.type == 'invoices'}

Der Betrag wird wie vereinbart in den nächsten Tagen von Ihrem Konto abgebucht.{/if}


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
im Anhang erhalten Sie {$mail.anredepronomen_plus_titel} <b>{$invoice.base.invoiceno}</b> vom {$invoice.base.created|date_format} als PDF-Dokument. Zum Lesen und Ausdrucken benötigen Sie den Adobe Reader [<a href="https://get.adobe.com/de/reader/">https://get.adobe.com/de/reader/</a>] oder ein vergleichbares Programm zur Verarbeitung von PDF-Dokumenten.
</p>

{if $customer.dtaus == 1 && $invoice.type == 'invoices'}<p>Der Betrag wird wie vereinbart in den nächsten Tagen von Ihrem Konto abgebucht.</p>{/if}

{if $customer.frontend_active == 1 && $frontend_active}<p>Unter der Adresse {$data.url} finden Sie unseren Kundenbereich. Dort können Sie sich vergangene Rechnungen und Gutschriften ansehen und herunterladen. Außerdem erhalten Sie eine Übersicht Ihrer wiederkehrenden Vertragspositionen, sowie Ihrer Kundendaten.</p>

{/if}<p>Mit besten Grüßen aus dem Tollwerk!</p>

[signature_html]

</body>
</html>