<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:pj="https://schema.prisjakt.nu/ns/1.0" xmlns:g="http://base.google.com/ns/1.0" version="3.0">
    <channel>
        <title>$SiteConfig.Title</title>
        <link>$BaseHref</link>
        <description>$SiteConfig.Tagline</description>

        <% loop $Items %>
        <item>
            <title>$Title</title>
            <description>$Description</description>
            <g:description>$Description</g:description>
            <g:id><% if $InternalItemID %>$InternalItemID<% else %>$ID<% end_if %></g:id>
            <link>$AbsoluteLink</link>
            <% if $ImageLink %><g:image_link>{$ImageLink}</g:image_link><% end_if %>
            <g:price>$Price(2) $Top.Currency</g:price>
            <g:condition>$Condition</g:condition>
            <g:availability>$Availability</g:availability>
            <g:brand>$Brand</g:brand>
            <% if $MPN %><g:mpn>$MPN</g:mpn><% end_if %>
            <% if $GoogleProductCategory %><g:google_product_category>$GoogleProductCategory</g:google_product_category><% end_if %>
            <g:custom_label_1>$ParentTitle</g:custom_label_1>
        </item>
        <% end_loop %>
    </channel>
</rss>
