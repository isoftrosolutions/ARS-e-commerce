<?php
/**
 * Global JSON-LD schema — included in both header files.
 * Outputs Organization, LocalBusiness, and WebSite schemas.
 */
$_schema_base = rtrim(SITE_URL, '/') . '/ARS';
?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": ["Organization", "LocalBusiness"],
            "@id": "<?= $_schema_base ?>/#organization",
            "name": "Easy Shopping A.R.S",
            "alternateName": "ARS Shop",
            "url": "<?= $_schema_base ?>",
            "logo": {
                "@type": "ImageObject",
                "url": "<?= $_schema_base ?>/assets/logo.jpeg",
                "width": 200,
                "height": 200
            },
            "image": "<?= $_schema_base ?>/assets/logo.jpeg",
            "description": "Easy Shopping A.R.S is Nepal's trusted online shopping destination based in Birgunj, Parsa. We offer quality electronics, fashion, home goods and more with fast delivery across Nepal.",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "Birgunj-13 Radhemai",
                "addressLocality": "Birgunj",
                "addressRegion": "Parsa",
                "addressCountry": "NP"
            },
            "geo": {
                "@type": "GeoCoordinates",
                "latitude": "27.0104",
                "longitude": "84.8777"
            },
            "telephone": "+977-9820210361",
            "email": "easyshoppinga.r.s1@gmail.com",
            "openingHours": "Mo-Su 09:00-20:00",
            "currenciesAccepted": "NPR",
            "paymentAccepted": "Cash, eSewa, FonePay, Bank Transfer",
            "priceRange": "Rs. 100 - Rs. 50000",
            "sameAs": [
                "https://www.facebook.com/easyshoppinga.r.s1",
                "https://www.instagram.com/easyshoppinga.r.s1"
            ]
        },
        {
            "@type": "WebSite",
            "@id": "<?= $_schema_base ?>/#website",
            "url": "<?= $_schema_base ?>",
            "name": "Easy Shopping A.R.S",
            "description": "Nepal's trusted online shopping destination",
            "publisher": {
                "@id": "<?= $_schema_base ?>/#organization"
            },
            "potentialAction": {
                "@type": "SearchAction",
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": "<?= $_schema_base ?>/shop.php?q={search_term_string}"
                },
                "query-input": "required name=search_term_string"
            },
            "inLanguage": "en-NP"
        }
    ]
}
</script>
