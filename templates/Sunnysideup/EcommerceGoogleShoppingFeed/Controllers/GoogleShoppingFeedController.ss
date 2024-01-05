<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:pj="https://schema.prisjakt.nu/ns/1.0" xmlns:g="http://base.google.com/ns/1.0" version="3.0">
    <channel>
        <title>$SiteConfig.Title</title>
        <link>$BaseHref</link>
        <description>$SiteConfig.Tagline</description>

        <% loop $Items %>
        <item>
            <title>$title</title>
            <link>$link</link>
            <g:id>$id</g:id>
            <g:image_link>$image_link</g:image_link>
            <g:price>$price</g:price>
            <g:condition>$condition</g:condition>
            <g:availability>$availability</g:availability>
        </item>
        <% end_loop %>
    </channel>
</rss>




<%-- NOTE this may not be in use - see GoogleShoppingFeedController.php --%>
